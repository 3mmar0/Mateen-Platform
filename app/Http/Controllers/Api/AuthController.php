<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Requests\Auth\{ForgotPasswordRequest, LoginRequest, RegisterRequest, ResetPasswordRequest};
use App\Http\Resources\UserResource;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $r): JsonResponse
    {
        $data = $r->validated();
        $role = $data['role'] instanceof Role ? $data['role']->value : (string) ($data['role'] ?? '');
        if ($role === Role::Teacher->value) {
            $slug = $data['subject'] ?? null;
            $data['subject_id'] = $data['subject_id'] ?? Subject::query()
                ->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)->orWhere('title', $slug);
                })
                ->value('id');
            // Map legacy client slugs (tafseer/quran1) to seeded slugs when needed
            if (! $data['subject_id'] && $slug) {
                $aliases = [
                    'tafseer' => 'tafsir',
                    'quran1' => 'maqraah',
                    'quran2' => 'maqraah',
                    'ithraiyat' => 'maqraah',
                ];
                if (isset($aliases[$slug])) {
                    $data['subject_id'] = Subject::query()->where('slug', $aliases[$slug])->value('id');
                }
            }
            if (! $data['subject_id']) {
                throw ValidationException::withMessages(['subject' => 'المادة المحددة غير موجودة.']);
            }
        }
        unset($data['subject']);
        $u = User::create($data);
        $u->load('enrollments.subject');

        return response()->json([
            'token' => $u->createToken('api')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($u),
        ], 201);
    }

    public function login(LoginRequest $r): JsonResponse
    {
        $u = User::where('email', $r->string('email'))->first();
        if (! $u || ! $u->is_active || ! Hash::check($r->string('password'), $u->password)) {
            throw ValidationException::withMessages(['email' => 'بيانات الدخول غير صحيحة.']);
        }
        if ($r->filled('expected_role') && $u->role->value !== $r->string('expected_role')->toString()) {
            abort(403, 'الدور المحدد غير مطابق.');
        }
        $u->load('enrollments.subject');

        return response()->json([
            'token' => $u->createToken('api')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($u),
        ]);
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }

    public function me(Request $r): UserResource
    {
        return new UserResource($r->user()->load('enrollments.subject'));
    }

    public function forgot(ForgotPasswordRequest $r): JsonResponse
    {
        Password::sendResetLink($r->only('email'));

        return response()->json(['message' => 'إذا كان البريد مسجلاً فسيصل رابط إعادة التعيين.'], 202);
    }

    public function reset(ResetPasswordRequest $r): JsonResponse
    {
        $status = Password::reset($r->validated(), function (User $u, string $p) {
            $u->forceFill(['password' => $p, 'must_reset_password' => false])->save();
            $u->tokens()->delete();
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'تم تحديث كلمة المرور.']);
    }
}
