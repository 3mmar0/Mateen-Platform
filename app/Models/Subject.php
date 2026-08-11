<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model {
 protected $guarded=[]; protected function casts():array{return ['axes'=>'array'];}
 /** Subjects shown as courses (have emoji/image); stubs like الإثرائيات stay out of the catalog. */
 public function scopeCatalog(Builder $q): Builder
 {
  return $q->where(function (Builder $inner) {
   $inner->where(function (Builder $a) {
    $a->whereNotNull('icon_data')->where('icon_data', '!=', '');
   })->orWhere(function (Builder $b) {
    $b->whereNotNull('icon_url')->where('icon_url', '!=', '');
   })->orWhere(function (Builder $c) {
    $c->whereNotNull('icon')->where('icon', '!=', '');
   });
  });
 }
 public function imageUrl(): ?string
 {
  foreach ([$this->icon_data, $this->icon_url] as $v) {
   if (is_string($v) && $v !== '') {
    return $v;
   }
  }
  return null;
 }
 public function materials(){return $this->hasMany(LearningMaterial::class);}
 public function enrollments(){return $this->hasMany(Enrollment::class);}
 public function teachers(){return $this->hasMany(User::class);}
 public function assignments(){return $this->hasMany(Assignment::class);}
 public function schedules(){return $this->hasMany(ScheduleEntry::class);}
}
