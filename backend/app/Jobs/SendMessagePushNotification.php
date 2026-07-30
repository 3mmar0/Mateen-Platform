<?php
namespace App\Jobs;
use App\Models\Message; use App\Services\FcmService; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Foundation\Queue\Queueable;
class SendMessagePushNotification implements ShouldQueue { use Queueable; public function __construct(public Message $message){}
 public function handle(FcmService $fcm):void {$m=$this->message->load('conversation.participants.devices');
  $tokens=$m->conversation->participants->where('id','!=',$m->sender_id)->flatMap->devices->pluck('fcm_token')->all();
  $fcm->send($tokens,$m->sender_display??'متين',$m->body??'رسالة جديدة',['conversation_id'=>(string)$m->conversation_id]);
 }}
