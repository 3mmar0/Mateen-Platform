<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceRecord extends Model { protected $guarded=[]; protected function casts():array{return ['session_date'=>'date','present'=>'boolean'];}
 public function user(){return $this->belongsTo(User::class);} public function subject(){return $this->belongsTo(Subject::class);}}
