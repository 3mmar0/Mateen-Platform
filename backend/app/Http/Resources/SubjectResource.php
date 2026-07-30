<?php
namespace App\Http\Resources;
use App\Enums\Role; use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class SubjectResource extends JsonResource { public function toArray(Request $r):array{$u=$r->user();return [
 'id'=>$this->id,'slug'=>$this->slug,'title'=>$this->title,'subtitle'=>$this->subtitle,'description'=>$this->description,
 'axes'=>$this->axes??[],'sort_order'=>$this->sort_order,'can_enroll'=>$u?->isRole(Role::Student)??false,
 'materials_visible'=>$u&&!$u->isRole(Role::Mateen),
];}}
