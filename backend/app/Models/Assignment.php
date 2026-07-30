<?php
namespace App\Models;
use App\Enums\AssignmentStatus; use Illuminate\Database\Eloquent\Model;
class Assignment extends Model { protected $guarded=[]; protected function casts():array{return ['status'=>AssignmentStatus::class,'due_at'=>'datetime'];}
 public function subject(){return $this->belongsTo(Subject::class);} public function material(){return $this->belongsTo(LearningMaterial::class,'learning_material_id');}
 public function submissions(){return $this->hasMany(AssignmentSubmission::class);} public function creator(){return $this->belongsTo(User::class,'created_by');}}
