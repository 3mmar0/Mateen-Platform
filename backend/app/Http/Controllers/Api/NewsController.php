<?php
namespace App\Http\Controllers\Api;
use App\Enums\{NewsStatus,Role}; use App\Models\NewsItem; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class NewsController extends Controller {
 public function index(Request $r){$q=NewsItem::latest('published_at');if(!$r->user()->isRole(Role::Admin,Role::Supervisor))$q->where('status',NewsStatus::Published)->where(fn($x)=>$x->whereNull('published_at')->orWhere('published_at','<=',now()));return ['data'=>$q->get()];}
 public function store(Request $r){$this->authorize('create',NewsItem::class);$i=NewsItem::create([...$this->data($r),'created_by'=>$r->user()->id]);return response()->json(['data'=>$i],201);}
 public function update(Request $r,NewsItem $news){$this->authorize('update',$news);$news->update($this->data($r));return ['data'=>$news];}
 public function destroy(NewsItem $news){$this->authorize('delete',$news);$news->delete();return response()->noContent();}
 private function data(Request $r):array{return $r->validate(['title'=>'sometimes|required|string','body'=>'sometimes|required|string','status'=>['nullable',Rule::enum(NewsStatus::class)],'published_at'=>'nullable|date']);}
}
