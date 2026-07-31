<?php
namespace App\Services;
use App\Enums\Role; use App\Models\User; use Barryvdh\DomPDF\Facade\Pdf; use PhpOffice\PhpSpreadsheet\Spreadsheet; use PhpOffice\PhpSpreadsheet\Writer\Xlsx; use PhpOffice\PhpWord\PhpWord;
class StudentExportService {
 public function export(string $format='xlsx'):string {
  $rows=User::whereIn('role',[Role::Student,Role::Mateen])->get(['name','email','phone','role']);
  $path=storage_path('app/private/students-'.now()->format('YmdHis').'.'.$format);
  if($format==='pdf'){Pdf::loadView('exports.students',['students'=>$rows])->save($path);}
  elseif($format==='docx'){$w=new PhpWord;$s=$w->addSection();foreach($rows as $r)$s->addText("$r->name | $r->email | {$r->role->value}");$w->save($path);}
  else {$x=new Spreadsheet;$sheet=$x->getActiveSheet();$sheet->fromArray([['Name','Email','Phone','Role'],...$rows->map(fn($r)=>[$r->name,$r->email,$r->phone,$r->role->value])->all()]);(new Xlsx($x))->save($path);}
  return $path;
 }
}
