<?php
namespace App\Http\Controllers\Api;
use App\Enums\LibrarySection; use App\Models\LibraryItem; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class LibraryController extends Controller {
 public function index(Request $r){
  if ($r->user()) $this->authorize('viewAny',LibraryItem::class);
  return ['data'=>LibraryItem::when($r->string('section')->toString(),fn($q,$v)=>$q->where('section',$v))->orderBy('sort_order')->get()];
 }
 public function store(Request $r){$this->authorize('create',LibraryItem::class);$i=LibraryItem::create([...$this->data($r),'created_by'=>$r->user()->id]);return response()->json(['data'=>$i],201);}
 public function update(Request $r,LibraryItem $library){$this->authorize('update',$library);$library->update($this->data($r));return ['data'=>$library];}
 public function destroy(LibraryItem $library){$this->authorize('delete',$library);$library->delete();return response()->noContent();}
 private function data(Request $r):array{return $r->validate(['section'=>['sometimes','required',Rule::enum(LibrarySection::class)],'title'=>'sometimes|required|string','description'=>'nullable|string','media_url'=>'nullable|url','subject_filter'=>'nullable|string','sort_order'=>'nullable|integer']);}
}
