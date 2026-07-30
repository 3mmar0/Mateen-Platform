<?php
namespace App\Http\Controllers\Api;
use App\Services\{StatsService,StudentExportService}; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class StatsController extends Controller {
 public function summary(Request $r,StatsService $s){$this->authorize('view-stats');$subject=$r->user()->role->value==='teacher'?$r->user()->subject_id:$r->integer('subject_id');return ['data'=>$s->summary($subject)];}
 public function export(Request $r,StudentExportService $s){$this->authorize('view-stats');$v=$r->validate(['format'=>['nullable',Rule::in(['xlsx','docx','pdf'])]]);return response()->download($s->export($v['format']??'xlsx'))->deleteFileAfterSend();}
}
