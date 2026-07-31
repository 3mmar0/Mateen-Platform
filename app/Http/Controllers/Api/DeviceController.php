<?php
namespace App\Http\Controllers\Api;
use App\Models\UserDevice; use Illuminate\Http\Request;
class DeviceController extends Controller { public function store(Request $r){$v=$r->validate(['fcm_token'=>'required|string','platform'=>'nullable|in:web,android,ios']);
 $d=UserDevice::updateOrCreate(['fcm_token'=>$v['fcm_token']],['user_id'=>$r->user()->id,'platform'=>$v['platform']??null,'last_seen_at'=>now()]);return response()->json(['data'=>$d],201);}}
