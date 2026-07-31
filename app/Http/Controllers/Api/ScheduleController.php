<?php
namespace App\Http\Controllers\Api;
use App\Http\Resources\ScheduleResource; use App\Models\ScheduleEntry; use Illuminate\Http\Request;
class ScheduleController extends Controller {
 public function index(){return ScheduleResource::collection(ScheduleEntry::with('subject')->orderBy('starts_at')->get());}
 public function store(Request $r){$this->authorize('manage-schedules');$e=ScheduleEntry::create([...$this->data($r),'created_by'=>$r->user()->id]);return (new ScheduleResource($e))->response()->setStatusCode(201);}
 public function update(Request $r,ScheduleEntry $schedule){$this->authorize('manage-schedules');$schedule->update($this->data($r));return new ScheduleResource($schedule);}
 public function destroy(ScheduleEntry $schedule){$this->authorize('manage-schedules');$schedule->delete();return response()->noContent();}
 private function data(Request $r):array{return $r->validate(['subject_id'=>'nullable|exists:subjects,id','title'=>'sometimes|required|string','starts_at'=>'sometimes|required|date','ends_at'=>'nullable|date|after:starts_at','weekday'=>'nullable|integer|between:0,6','audience'=>'nullable|array']);}
}
