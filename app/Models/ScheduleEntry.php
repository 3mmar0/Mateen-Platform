<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ScheduleEntry extends Model { protected $guarded=[]; protected function casts():array{return ['starts_at'=>'datetime','ends_at'=>'datetime','audience'=>'array'];}
 public function subject(){return $this->belongsTo(Subject::class);} public function creator(){return $this->belongsTo(User::class,'created_by');}}
