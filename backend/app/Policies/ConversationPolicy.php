<?php
namespace App\Policies;
use App\Enums\Role; use App\Models\Conversation; use App\Models\User;
class ConversationPolicy {
 public function viewAny(User $u):bool{return true;}
 public function view(User $u,Conversation $c):bool{return $c->participants()->whereKey($u->id)->exists();}
 public function create(User $u):bool{return true;}
 public function attachMedia(User $u,Conversation $c):bool{return $this->view($u,$c)&&!$u->isRole(Role::Student,Role::Mateen);}
}
