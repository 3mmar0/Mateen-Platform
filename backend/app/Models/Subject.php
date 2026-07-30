<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model {
 protected $guarded=[]; protected function casts():array{return ['axes'=>'array'];}
 public function materials(){return $this->hasMany(LearningMaterial::class);}
 public function enrollments(){return $this->hasMany(Enrollment::class);}
 public function teachers(){return $this->hasMany(User::class);}
 public function assignments(){return $this->hasMany(Assignment::class);}
 public function schedules(){return $this->hasMany(ScheduleEntry::class);}
}
