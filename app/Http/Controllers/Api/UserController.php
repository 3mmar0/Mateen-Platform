<?php
namespace App\Http\Controllers\Api;
use App\Enums\Role; use App\Models\User; use App\Services\AccountDeletionService; use Illuminate\Http\Request;
class UserController extends Controller { public function destroy(Request $r,User $user,AccountDeletionService $service){
 abort_unless($r->user()->id===$user->id||$r->user()->isRole(Role::Admin),403);$service->delete($user);return response()->noContent();}}
