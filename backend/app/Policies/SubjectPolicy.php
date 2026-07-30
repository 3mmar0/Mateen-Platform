<?php
namespace App\Policies;
use App\Enums\Role; use App\Models\Subject; use App\Models\User;
class SubjectPolicy {
 public function viewAny(User $u):bool{return true;} public function view(User $u,Subject $s):bool{return true;}
 public function create(User $u):bool{return $u->isRole(Role::Admin);}
 public function update(User $u,Subject $s):bool{return $u->isRole(Role::Admin);}
 public function delete(User $u,Subject $s):bool{return $u->isRole(Role::Admin);}
}
