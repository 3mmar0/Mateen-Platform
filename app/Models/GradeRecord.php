<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GradeRecord extends Model { protected $guarded=[]; protected function casts():array{return ['recorded_at'=>'datetime','score'=>'decimal:2'];}
 public function user(){return $this->belongsTo(User::class);} public function subject(){return $this->belongsTo(Subject::class);}}
