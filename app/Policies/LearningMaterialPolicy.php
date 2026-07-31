<?php
namespace App\Policies;
use App\Enums\Role; use App\Models\LearningMaterial; use App\Models\Subject; use App\Models\User;
class LearningMaterialPolicy {
 public function viewAny(User $u,Subject $s):bool{return !$u->isRole(Role::Mateen) && (!$u->isRole(Role::Student) || $u->enrollments()->where('subject_id',$s->id)->exists());}
 public function create(User $u,Subject $s):bool{return $u->isRole(Role::Admin,Role::Supervisor)||($u->isRole(Role::Teacher)&&$u->subject_id===$s->id);}
 public function update(User $u,LearningMaterial $m):bool{return $u->isRole(Role::Admin,Role::Supervisor)||($u->isRole(Role::Teacher)&&$u->subject_id===$m->subject_id);}
 public function delete(User $u,LearningMaterial $m):bool{return $this->update($u,$m);}
}
