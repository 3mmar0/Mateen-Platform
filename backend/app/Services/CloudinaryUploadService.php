<?php
namespace App\Services;
class CloudinaryUploadService {
 public function signature(array $params=[]):array {
  $timestamp=time(); $secret=(string)config('services.cloudinary.secret'); ksort($params);
  $base=http_build_query([...$params,'timestamp'=>$timestamp]);
  return ['cloud_name'=>config('services.cloudinary.cloud_name'),'api_key'=>config('services.cloudinary.key'),
   'timestamp'=>$timestamp,'signature'=>$secret?sha1($base.$secret):hash_hmac('sha256',$base,(string)env('APP_KEY')),
   'stub'=>!$secret];
 }
}
