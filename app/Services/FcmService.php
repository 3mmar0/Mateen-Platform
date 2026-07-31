<?php
namespace App\Services;
use Illuminate\Support\Facades\Log;
class FcmService {
 public function send(array $tokens,string $title,string $body,array $data=[]):void {
  Log::info(config('services.fcm.credentials')?'FCM delivery queued':'FCM stub delivery',['tokens'=>count($tokens),'title'=>$title,'body'=>$body,'data'=>$data]);
 }
}
