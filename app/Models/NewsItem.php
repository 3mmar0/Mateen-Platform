<?php
namespace App\Models;
use App\Enums\NewsStatus; use Illuminate\Database\Eloquent\Model;
class NewsItem extends Model { protected $guarded=[]; protected function casts():array{return ['status'=>NewsStatus::class,'published_at'=>'datetime'];}
 public function creator(){return $this->belongsTo(User::class,'created_by');}}
