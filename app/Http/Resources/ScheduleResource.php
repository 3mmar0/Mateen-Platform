<?php
namespace App\Http\Resources;
use App\Services\HijriDateService; use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class ScheduleResource extends JsonResource { public function toArray(Request $r):array{return [
 'id'=>$this->id,'subject_id'=>$this->subject_id,'title'=>$this->title,'starts_at'=>$this->starts_at?->toIso8601String(),
 'ends_at'=>$this->ends_at?->toIso8601String(),'weekday'=>$this->weekday,'audience'=>$this->audience,
 'hijri_display'=>app(HijriDateService::class)->format($this->starts_at),
];}}
