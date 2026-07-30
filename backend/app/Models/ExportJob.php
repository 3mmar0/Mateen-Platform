<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExportJob extends Model { protected $guarded=[]; protected function casts():array{return ['params'=>'array','ready_at'=>'datetime'];}
 public function user(){return $this->belongsTo(User::class);}}
