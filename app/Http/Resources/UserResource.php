<?php
namespace App\Http\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class UserResource extends JsonResource { public function toArray(Request $r):array{
 $enrollments = $this->relationLoaded('enrollments')
  ? $this->enrollments
  : $this->enrollments()->with('subject')->get();
 return [
 'id'=>$this->id,'name'=>$this->name,'email'=>$this->email,'phone'=>$this->phone,'role'=>$this->role,
 'subject_id'=>$this->subject_id,'theme_id'=>$this->theme_id,'ornament_id'=>$this->ornament_id,
 'is_active'=>$this->is_active,'must_reset_password'=>$this->must_reset_password,
 'enrolled_subject_ids'=>$enrollments->pluck('subject_id')->values(),
 'enrolled_subjects'=>$enrollments->map(fn ($e) => $e->subject?->title)->filter()->values(),
 ];
}}
