<?php
namespace App\Http\Controllers\Api;
use App\Models\{Conversation,User}; use App\Services\MessagingService; use Illuminate\Http\Request;
class ConversationController extends Controller {
 public function index(Request $r){return ['data'=>$r->user()->conversations()->with(['participants:id,name,role','messages'=>fn($q)=>$q->latest()->limit(1)])->latest('conversations.updated_at')->get()];}
 public function store(Request $r,MessagingService $service){$v=$r->validate(['participant_id'=>'required|exists:users,id|not_in:'.$r->user()->id]);return response()->json(['data'=>$service->start($r->user(),User::findOrFail($v['participant_id']))],201);}
 public function messages(Request $r,Conversation $conversation){$this->authorize('view',$conversation);return ['data'=>$conversation->messages()->latest()->paginate()];}
 public function send(Request $r,Conversation $conversation,MessagingService $service){$v=$r->validate(['body'=>'nullable|string|max:10000','media_url'=>'nullable|url','media_type'=>'nullable|in:image,audio']);return response()->json(['data'=>$service->send($r->user(),$conversation,$v)],201);}
}
