<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $r)
    {
        abort_unless($r->user()->isRole(Role::Admin, Role::Support, Role::Supervisor), 403);

        $q = User::query()->with('subject')->orderBy('name');
        if ($role = $r->string('role')->toString()) {
            $q->where('role', $role);
        }
        if ($status = $r->string('status')->toString()) {
            $q->where('account_status', $status);
        }

        $perPage = min(500, max(1, $r->integer('per_page', 50)));

        return UserResource::collection($q->paginate($perPage));
    }

    public function update(Request $r, User $user)
    {
        abort_unless($r->user()->isRole(Role::Admin, Role::Supervisor), 403);

        $v = $r->validate([
            'name' => 'sometimes|string',
            'phone' => 'nullable|string',
            'year' => 'nullable|string',
            'role' => ['sometimes', Rule::enum(Role::class)],
            'subject_id' => 'nullable|exists:subjects,id',
            'account_status' => ['sometimes', Rule::in(['active', 'pending', 'suspended'])],
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($v['account_status'])) {
            $v['is_active'] = $v['account_status'] === 'active';
        } elseif (array_key_exists('is_active', $v)) {
            $v['account_status'] = $v['is_active'] ? 'active' : 'suspended';
        }

        $user->update($v);

        return new UserResource($user->fresh()->load('subject', 'enrollments.subject'));
    }

    public function destroy(Request $r, User $user, AccountDeletionService $service)
    {
        abort_unless($r->user()->id === $user->id || $r->user()->isRole(Role::Admin), 403);
        $service->delete($user);

        return response()->noContent();
    }
}
