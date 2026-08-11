<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        $enrollments = $this->relationLoaded('enrollments')
            ? $this->enrollments
            : $this->enrollments()->with('subject')->get();

        $status = $this->account_status
            ?? (($this->is_active ?? true) ? 'active' : 'suspended');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'year' => $this->year,
            'role' => $this->role,
            'subject_id' => $this->subject_id,
            'subject' => $this->subject?->slug,
            'subject_title' => $this->subject?->title,
            'theme_id' => $this->theme_id,
            'ornament_id' => $this->ornament_id,
            'is_active' => $this->is_active,
            'status' => $status,
            'account_status' => $status,
            'must_reset_password' => $this->must_reset_password,
            'created_at' => $this->created_at?->toIso8601String(),
            'enrolled_subject_ids' => $enrollments->pluck('subject_id')->values(),
            'enrolled_subjects' => $enrollments->map(fn ($e) => $e->subject?->title)->filter()->values(),
        ];
    }
}
