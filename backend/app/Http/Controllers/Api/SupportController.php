<?php
namespace App\Http\Controllers\Api;
use App\Http\Resources\UserResource; use App\Models\User; use Illuminate\Http\Request;
class SupportController extends Controller {
 public function users(Request $r){$this->authorize('support-users');return UserResource::collection(User::paginate());}
 public function theme(Request $r,User $user){$this->authorize('support-users');$v=$r->validate(['theme_id'=>'nullable|string|max:100','ornament_id'=>'nullable|string|max:100']);$user->update($v);return new UserResource($user);}
}
