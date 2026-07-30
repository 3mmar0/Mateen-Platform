<?php
namespace App\Http\Controllers\Api;
use App\Enums\{AssignmentStatus,SubmissionStatus}; use App\Models\{Assignment,AssignmentSubmission}; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class AssignmentController extends Controller {
 public function index(Request $r){$this->authorize('viewAny',Assignment::class);$q=Assignment::with('subject')->when($r->integer('subject_id'),fn($x)=>$x->where('subject_id',$r->integer('subject_id')));return ['data'=>$q->latest()->get()];}
 public function store(Request $r){$this->authorize('create',Assignment::class);$a=Assignment::create([...$this->data($r),'created_by'=>$r->user()->id]);abort_unless($r->user()->can('update',$a),403);return response()->json(['data'=>$a],201);}
 public function submissions(Assignment $assignment){$this->authorize('viewSubmissions',$assignment);return ['data'=>$assignment->submissions()->with('user')->get()];}
 public function submit(Request $r,Assignment $assignment){$this->authorize('submit',$assignment);$v=$r->validate(['content'=>'nullable|string','attachment_url'=>'nullable|url']);$s=$assignment->submissions()->updateOrCreate(['user_id'=>$r->user()->id],[...$v,'status'=>SubmissionStatus::Submitted]);return response()->json(['data'=>$s],201);}
 public function updateSubmission(Request $r,AssignmentSubmission $submission){$this->authorize('update',$submission->assignment);$v=$r->validate(['status'=>['sometimes',Rule::enum(SubmissionStatus::class)],'grade'=>'nullable|numeric','feedback'=>'nullable|string']);$submission->update($v);return ['data'=>$submission];}
 private function data(Request $r):array{return $r->validate(['subject_id'=>'required|exists:subjects,id','learning_material_id'=>'nullable|exists:learning_materials,id','title'=>'required|string','description'=>'nullable|string','due_at'=>'nullable|date','status'=>['nullable',Rule::enum(AssignmentStatus::class)]]);}
}
