<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Enrollment extends Model { protected $guarded=[]; protected function casts():array{return ['enrolled_at'=>'datetime'];}
 public function user(){return $this->belongsTo(User::class);} public function subject(){return $this->belongsTo(Subject::class);}}
