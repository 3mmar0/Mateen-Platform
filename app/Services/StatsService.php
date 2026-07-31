<?php
namespace App\Services;
use App\Models\AttendanceRecord; use App\Models\GradeRecord;
class StatsService {
 public function summary(?int $subjectId=null):array {
  $a=AttendanceRecord::query()->when($subjectId,fn($q)=>$q->where('subject_id',$subjectId));
  $g=GradeRecord::query()->when($subjectId,fn($q)=>$q->where('subject_id',$subjectId));
  $total=(clone $a)->count(); return ['attendance_total'=>$total,'attendance_present'=>(clone $a)->where('present',true)->count(),
   'attendance_rate'=>$total?round((clone $a)->where('present',true)->count()*100/$total,2):0,
   'grade_count'=>(clone $g)->count(),'grade_average'=>(float)((clone $g)->avg('score')??0)];
 }
}
