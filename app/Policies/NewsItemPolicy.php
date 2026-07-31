<?php
namespace App\Policies;
use App\Enums\Role; use App\Models\NewsItem; use App\Models\User;
class NewsItemPolicy {
 public function viewAny(User $u):bool{return true;} public function create(User $u):bool{return $u->isRole(Role::Admin,Role::Supervisor);}
 public function update(User $u,NewsItem $i):bool{return $this->create($u);} public function delete(User $u,NewsItem $i):bool{return $this->create($u);}
}
