<?php
namespace App\Policies;
use App\Enums\Role; use App\Models\LibraryItem; use App\Models\User;
class LibraryItemPolicy {
 public function viewAny(User $u):bool{return true;} public function create(User $u):bool{return $u->isRole(Role::Admin,Role::Supervisor);}
 public function update(User $u,LibraryItem $i):bool{return $this->create($u);} public function delete(User $u,LibraryItem $i):bool{return $this->create($u);}
}
