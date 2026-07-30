<?php
namespace App\Models;
use App\Enums\SubmissionStatus; use Illuminate\Database\Eloquent\Model;
class AssignmentSubmission extends Model { protected $guarded=[]; protected function casts():array{return ['status'=>SubmissionStatus::class,'grade'=>'decimal:2'];}
 public function assignment(){return $this->belongsTo(Assignment::class);} public function user(){return $this->belongsTo(User::class);}}
