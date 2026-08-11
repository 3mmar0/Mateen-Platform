<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $r)
    {
        $q = Subject::query()->orderBy('sort_order');
        // Public route: optionally resolve Sanctum bearer so admins still see stub subjects.
        $user = $r->user();
        if (! $user && $r->bearerToken()) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($r->bearerToken())?->tokenable;
        }
        // Hide stub subjects (e.g. الإثرائيات) from the public course catalog.
        if (! $user?->isRole(\App\Enums\Role::Admin)) {
            $q->catalog();
        }

        return SubjectResource::collection($q->get());
    }

    public function show(Subject $subject)
    {
        return new SubjectResource($subject);
    }

    public function store(Request $r)
    {
        $this->authorize('create', Subject::class);
        $s = Subject::create($this->data($r));

        return (new SubjectResource($s))->response()->setStatusCode(201);
    }

    public function update(Request $r, Subject $subject)
    {
        $this->authorize('update', $subject);
        $subject->update($this->data($r, $subject));

        return new SubjectResource($subject);
    }

    private function data(Request $r, ?Subject $s = null): array
    {
        return $r->validate([
            'slug' => ['sometimes', 'required', 'string', Rule::unique('subjects')->ignore($s)],
            'title' => ['sometimes', 'required', 'string'],
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:32',
            'icon_url' => 'nullable|string',
            'icon_data' => 'nullable|string',
            'color' => 'nullable|string|max:191',
            'meetings' => 'nullable|string',
            'weeks' => 'nullable|string',
            'level' => 'nullable|string',
            'axes' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);
    }
}
