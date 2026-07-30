<?php
namespace App\Policies;
use App\Enums\Role; use App\Models\Assignment; use App\Models\User;
class AssignmentPolicy {
 public function viewAny(User $u):bool{return !$u->isRole(Role::Mateen);}
 public function create(User $u):bool{return $u->isRole(Role::Admin,Role::Supervisor,Role::Teacher);}
 public function update(User $u,Assignment $a):bool{return $u->isRole(Role::Admin,Role::Supervisor)||($u->isRole(Role::Teacher)&&$u->subject_id===$a->subject_id);}
 public function viewSubmissions(User $u,Assignment $a):bool{return $this->update($u,$a);}
 public function submit(User $u,Assignment $a):bool{return $u->isRole(Role::Student)&&$a->status->value==='open'&&$u->enrollments()->where('subject_id',$a->subject_id)->exists();}
}
