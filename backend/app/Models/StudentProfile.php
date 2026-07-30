<?php
namespace App\Models;
use App\Enums\InterviewStatus; use App\Enums\StudentStatusClass; use Illuminate\Database\Eloquent\Model;
class StudentProfile extends Model { protected $guarded=[]; protected function casts():array{return ['interview_status'=>InterviewStatus::class,'status_class'=>StudentStatusClass::class,'extra'=>'array'];}
 public function user(){return $this->belongsTo(User::class);}}
