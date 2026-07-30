<?php
namespace App\Policies;
use App\Enums\Role; use App\Models\StudentProfile; use App\Models\User;
class StudentProfilePolicy {
 public function viewAny(User $u):bool{return $u->isRole(Role::Admin,Role::Supervisor,Role::Support,Role::Teacher);}
 public function view(User $u,StudentProfile $p):bool{return $u->id===$p->user_id||$this->viewAny($u);}
 public function create(User $u):bool{return $u->isRole(Role::Admin,Role::Supervisor);}
 public function update(User $u,StudentProfile $p):bool{return $u->isRole(Role::Admin,Role::Supervisor);}
}
