<?php
namespace App\Services;
use App\Enums\Role; use App\Jobs\SendMessagePushNotification; use App\Models\Conversation; use App\Models\Message; use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException; use Illuminate\Validation\ValidationException;
class MessagingService {
 public function start(User $actor,User $recipient):Conversation {
  if($actor->id===$recipient->id)throw ValidationException::withMessages(['participant_id'=>'لا يمكن بدء محادثة مع نفسك.']);
  if($actor->isRole(Role::Student,Role::Mateen)&&!$recipient->role->isStaff())throw new AuthorizationException;
  $ids=[$actor->id,$recipient->id]; sort($ids);
  $existing=$actor->conversations()->whereHas('participants',fn($q)=>$q->whereKey($recipient->id))->first();
  if($existing)return $existing; $c=Conversation::create(); $c->participants()->attach($ids); return $c->load('participants');
 }
 public function send(User $sender,Conversation $c,array $data):Message {
  if(!$c->participants()->whereKey($sender->id)->exists())throw new AuthorizationException;
  if(!$data['body']??null && !$data['media_url']??null)throw ValidationException::withMessages(['body'=>'النص أو الوسائط مطلوبة.']);
  if($sender->isRole(Role::Student,Role::Mateen)&&($data['media_url']??null))throw ValidationException::withMessages(['media_url'=>'الرسائل النصية فقط مسموحة لهذا الدور.']);
  $m=$c->messages()->create([...$data,'sender_id'=>$sender->id,'sender_display'=>$sender->name]);
  SendMessagePushNotification::dispatch($m); return $m;
 }
}
