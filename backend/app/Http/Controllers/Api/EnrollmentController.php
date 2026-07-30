<?php
namespace App\Http\Controllers\Api;
use App\Enums\Role; use App\Models\Subject; use Illuminate\Http\Request;
class EnrollmentController extends Controller { public function store(Request $r,Subject $subject){
 abort_unless($r->user()->isRole(Role::Student),403,'التسجيل متاح للطلاب فقط.');
 $e=$r->user()->enrollments()->firstOrCreate(['subject_id'=>$subject->id],['enrolled_at'=>now()]);return response()->json(['data'=>$e],$e->wasRecentlyCreated?201:200);
}}
