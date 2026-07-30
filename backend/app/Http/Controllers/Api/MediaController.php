<?php
namespace App\Http\Controllers\Api;
use App\Enums\Role; use App\Services\CloudinaryUploadService; use Illuminate\Http\Request;
class MediaController extends Controller { public function sign(Request $r,CloudinaryUploadService $service){
 abort_if($r->user()->isRole(Role::Student,Role::Mateen),403);$v=$r->validate(['folder'=>'nullable|string|max:100']);return $service->signature(array_filter(['folder'=>$v['folder']??'mateen']));
}}
