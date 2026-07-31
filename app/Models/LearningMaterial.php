<?php
namespace App\Models;
use App\Enums\MaterialType; use Illuminate\Database\Eloquent\Model;
class LearningMaterial extends Model { protected $guarded=[]; protected function casts():array{return ['type'=>MaterialType::class];}
 public function subject(){return $this->belongsTo(Subject::class);} public function creator(){return $this->belongsTo(User::class,'created_by');}}
