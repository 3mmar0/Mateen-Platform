<?php
namespace App\Http\Controllers\Api;
use App\Enums\{InterviewStatus,Role,StudentStatusClass}; use App\Models\{StudentProfile,User}; use App\Services\StudentExportService;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB; use Illuminate\Validation\Rule;
class StudentController extends Controller {
 public function index(Request $r){$this->authorize('viewAny',StudentProfile::class);$q=User::with('studentProfile','enrollments')->whereIn('role',[Role::Student,Role::Mateen]);
  if($r->user()->isRole(Role::Teacher))$q->whereHas('enrollments',fn($x)=>$x->where('subject_id',$r->user()->subject_id));
  if($r->integer('subject_id'))$q->whereHas('enrollments',fn($x)=>$x->where('subject_id',$r->integer('subject_id')));return response()->json(['data'=>$q->paginate()]);}
 public function store(Request $r){$this->authorize('create',StudentProfile::class);return response()->json(['data'=>$this->create($r->validate($this->rules()))],201);}
 public function bulk(Request $r){$this->authorize('create',StudentProfile::class);$rows=$r->validate(['students'=>'required|array|max:500'])['students'];$result=[];
  foreach($rows as $i=>$row){try{$v=validator($row,$this->rules())->validate();$result[]=['index'=>$i,'ok'=>true,'id'=>$this->create($v)->id];}catch(\Throwable $e){$result[]=['index'=>$i,'ok'=>false,'error'=>$e->getMessage()];}}return ['data'=>$result];}
 public function update(Request $r,User $student){abort_unless($student->studentProfile,404);$this->authorize('update',$student->studentProfile);$v=$r->validate($this->rules(true));
  $student->update(array_intersect_key($v,array_flip(['name','email','phone'])));$student->studentProfile->update(array_intersect_key($v,array_flip(['interview_status','status_class','notes','extra'])));return ['data'=>$student->load('studentProfile')];}
 public function export(Request $r,StudentExportService $service){$this->authorize('viewAny',StudentProfile::class);$v=$r->validate(['format'=>['nullable',Rule::in(['xlsx','docx','pdf'])]]);return response()->download($service->export($v['format']??'xlsx'))->deleteFileAfterSend();}
 private function create(array $v):User{return DB::transaction(function()use($v){$u=User::create([...array_intersect_key($v,array_flip(['name','email','phone','password'])),'role'=>Role::Student,'password'=>$v['password']??str()->password(16),'must_reset_password'=>!isset($v['password'])]);$u->studentProfile()->create(array_intersect_key($v,array_flip(['interview_status','status_class','notes','extra'])));return $u->load('studentProfile');});}
 private function rules(bool $partial=false):array{$p=$partial?'sometimes':'required';return ['name'=>[$p,'string'],'email'=>[$p,'email',Rule::unique('users')->ignore(request()->route('student'))],'phone'=>'nullable|string','password'=>'nullable|min:8','interview_status'=>['nullable',Rule::enum(InterviewStatus::class)],'status_class'=>['nullable',Rule::enum(StudentStatusClass::class)],'notes'=>'nullable|string','extra'=>'nullable|array'];}
}
