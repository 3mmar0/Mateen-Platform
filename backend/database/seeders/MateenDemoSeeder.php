<?php
namespace Database\Seeders;
use App\Enums\Role; use App\Models\{StudentProfile,Subject,User}; use Illuminate\Database\Seeder;
class MateenDemoSeeder extends Seeder {
 public function run():void {$subjects=collect([
  ['slug'=>'tafsir','title'=>'التفسير'],['slug'=>'fiqh','title'=>'الفقه'],['slug'=>'aqeedah','title'=>'العقيدة'],['slug'=>'hadeeth','title'=>'الحديث'],['slug'=>'maqraah','title'=>'المقرأة'],
 ])->map(fn($s,$i)=>Subject::updateOrCreate(['slug'=>$s['slug']],[...$s,'sort_order'=>$i]));
  foreach(Role::cases() as $role){$u=User::updateOrCreate(['email'=>$role->value.'@mateen.test'],['name'=>'مستخدم '.ucfirst($role->value),'password'=>'password','role'=>$role,'subject_id'=>$role===Role::Teacher?$subjects->first()->id:null,'is_active'=>true]);
   if(in_array($role,[Role::Student,Role::Mateen],true))StudentProfile::firstOrCreate(['user_id'=>$u->id]);}
 }
}
