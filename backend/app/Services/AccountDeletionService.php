<?php
namespace App\Services;
use App\Models\Message; use App\Models\User; use Illuminate\Support\Facades\DB;
class AccountDeletionService {
 public function delete(User $user):void { DB::transaction(function()use($user){
  Message::where('sender_id',$user->id)->update(['sender_id'=>null,'sender_display'=>'محذوف']);
  DB::table('attendance_records')->where('user_id',$user->id)->update(['user_id'=>null]);
  DB::table('grade_records')->where('user_id',$user->id)->update(['user_id'=>null]);
  $user->tokens()->delete(); $user->delete();
 });}
}
