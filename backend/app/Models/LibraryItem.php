<?php
namespace App\Models;
use App\Enums\LibrarySection; use Illuminate\Database\Eloquent\Model;
class LibraryItem extends Model { protected $guarded=[]; protected function casts():array{return ['section'=>LibrarySection::class];}
 public function creator(){return $this->belongsTo(User::class,'created_by');}}
