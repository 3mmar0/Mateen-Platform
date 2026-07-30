<?php
namespace App\Http\Controllers\Api;
use App\Enums\MaterialType; use App\Models\{LearningMaterial,Subject}; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class MaterialController extends Controller {
 public function index(Request $r,Subject $subject){$this->authorize('viewAny',[LearningMaterial::class,$subject]);return response()->json(['data'=>$subject->materials()->orderBy('sort_order')->get()]);}
 public function store(Request $r,Subject $subject){$this->authorize('create',[LearningMaterial::class,$subject]);$m=$subject->materials()->create([...$this->data($r),'created_by'=>$r->user()->id]);return response()->json(['data'=>$m],201);}
 public function update(Request $r,LearningMaterial $material){$this->authorize('update',$material);$material->update($this->data($r));return response()->json(['data'=>$material]);}
 public function destroy(LearningMaterial $material){$this->authorize('delete',$material);$material->delete();return response()->noContent();}
 private function data(Request $r):array{return $r->validate(['title'=>['sometimes','required','string'],'type'=>['sometimes','required',Rule::enum(MaterialType::class)],'body'=>'nullable|string','url'=>'nullable|url','sort_order'=>'nullable|integer']);}
}
