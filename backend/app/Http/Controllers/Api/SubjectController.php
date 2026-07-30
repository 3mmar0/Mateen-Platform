<?php
namespace App\Http\Controllers\Api;
use App\Http\Resources\SubjectResource; use App\Models\Subject; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class SubjectController extends Controller {
 public function index(){return SubjectResource::collection(Subject::orderBy('sort_order')->get());}
 public function show(Subject $subject){return new SubjectResource($subject);}
 public function store(Request $r){$this->authorize('create',Subject::class);$s=Subject::create($this->data($r));return (new SubjectResource($s))->response()->setStatusCode(201);}
 public function update(Request $r,Subject $subject){$this->authorize('update',$subject);$subject->update($this->data($r,$subject));return new SubjectResource($subject);}
 private function data(Request $r,?Subject $s=null):array{return $r->validate(['slug'=>['sometimes','required','string',Rule::unique('subjects')->ignore($s)],'title'=>['sometimes','required','string'],'subtitle'=>'nullable|string','description'=>'nullable|string','axes'=>'nullable|array','sort_order'=>'nullable|integer']);}
}
