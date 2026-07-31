<?php

namespace Database\Seeders;

use App\Enums\LibrarySection;
use App\Enums\MaterialType;
use App\Enums\NewsStatus;
use App\Enums\Role;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\LibraryItem;
use App\Models\NewsItem;
use App\Models\ScheduleEntry;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class MateenDemoSeeder extends Seeder
{
    public function run(): void
    {
        $defs = [
            [
                'slug' => 'tafsir',
                'title' => 'التفسير',
                'subtitle' => 'مادة أساسية — المستوى الثاني',
                'description' => 'يهدف مسار التفسير إلى تمكين الطالبة من فهم معاني مقرأة متين وفق منهج السلف الصالح، مع التدرج في الاستنباط والتدبر وربط الآيات بالواقع.',
                'axes' => ['مقدمات في علم التفسير', 'أسباب النزول', 'الناسخ والمنسوخ', 'تفسير المفردات', 'الاستنباط الفقهي', 'التدبر والتطبيق'],
                'teacher_email' => 'teacher.tafsir@mateen.test',
                'teacher_name' => 'معلمة التفسير',
            ],
            [
                'slug' => 'fiqh',
                'title' => 'الفقه',
                'subtitle' => 'مادة أساسية — المستوى الثاني',
                'description' => 'يُعنى مسار الفقه بتدريس أحكام العبادات والمعاملات بأدلتها الشرعية، مع التركيز على التطبيق العملي وحل المسائل الفقهية المعاصرة.',
                'axes' => ['الطهارة والصلاة', 'الزكاة والصيام', 'الحج والعمرة', 'فقه الأسرة', 'المعاملات المالية', 'الفقه المعاصر'],
                'teacher_email' => 'teacher.fiqh@mateen.test',
                'teacher_name' => 'معلمة الفقه',
            ],
            [
                'slug' => 'aqeedah',
                'title' => 'العقيدة',
                'subtitle' => 'مادة أساسية — المستوى الثاني',
                'description' => 'يُرسّخ مسار العقيدة أصول الإيمان الصحيح وفق منهج أهل السنة والجماعة، مع الرد على الشبهات وتقرير مسائل التوحيد والأسماء والصفات.',
                'axes' => ['أصول الإيمان الستة', 'التوحيد وأقسامه', 'الأسماء والصفات', 'القضاء والقدر', 'الولاء والبراء', 'الفرق والمذاهب'],
                'teacher_email' => 'teacher.aqeedah@mateen.test',
                'teacher_name' => 'معلمة العقيدة',
            ],
            [
                'slug' => 'hadeeth',
                'title' => 'الحديث',
                'subtitle' => 'مادة أساسية — المستوى الثاني',
                'description' => 'يجمع مسار الحديث بين دراسة المتون الحديثية وشرحها واستنباط الأحكام، مع تعلّم مصطلح الحديث وأسس قبول الروايات ورفضها.',
                'axes' => ['مصطلح الحديث', 'أقسام الحديث', 'شرح الأربعين النووية', 'الجرح والتعديل', 'فقه الحديث', 'التخريج والدراسة'],
                'teacher_email' => 'teacher.hadeeth@mateen.test',
                'teacher_name' => 'معلمة الحديث',
            ],
            [
                'slug' => 'maqraah',
                'title' => 'مقرأة متين',
                'subtitle' => 'مادة أساسية — المستوى الثاني',
                'description' => 'يُعنى مسار مقرأة متين بتلاوة القرآن بأحكام التجويد وحفظ المقرر الأسبوعي مع المراجعة المستمرة والإتقان التام.',
                'axes' => ['أحكام التجويد', 'الحفظ الأسبوعي', 'المراجعة المستمرة', 'إتقان المخارج', 'الوقف والابتداء', 'الإقراء والمتابعة'],
                'teacher_email' => 'teacher.maqraah@mateen.test',
                'teacher_name' => 'معلمة المقرأة',
            ],
        ];

        $subjects = collect($defs)->values()->map(function (array $d, int $i) {
            return Subject::updateOrCreate(
                ['slug' => $d['slug']],
                [
                    'title' => $d['title'],
                    'subtitle' => $d['subtitle'],
                    'description' => $d['description'],
                    'axes' => $d['axes'],
                    'sort_order' => $i,
                ]
            );
        });

        $roleMeta = [
            Role::Admin->value => ['name' => 'مديرة النظام', 'email' => 'admin@mateen.test'],
            Role::Support->value => ['name' => 'الدعم الفني', 'email' => 'support@mateen.test'],
            Role::Supervisor->value => ['name' => 'المشرفة العامة', 'email' => 'supervisor@mateen.test'],
            Role::Teacher->value => ['name' => 'معلمة تجريبية', 'email' => 'teacher@mateen.test'],
            Role::Student->value => ['name' => 'طالبة تجريبية', 'email' => 'student@mateen.test'],
            Role::Mateen->value => ['name' => 'صديقة متين', 'email' => 'mateen@mateen.test'],
        ];

        $usersByRole = [];
        foreach (Role::cases() as $role) {
            $meta = $roleMeta[$role->value];
            $u = User::updateOrCreate(
                ['email' => $meta['email']],
                [
                    'name' => $meta['name'],
                    'password' => 'password',
                    'role' => $role,
                    'subject_id' => $role === Role::Teacher ? $subjects->first()->id : null,
                    'is_active' => true,
                    'must_reset_password' => false,
                ]
            );
            if (in_array($role, [Role::Student, Role::Mateen], true)) {
                StudentProfile::firstOrCreate(['user_id' => $u->id], [
                    'interview_status' => 'done',
                    'status_class' => 'mateen_girls',
                    'notes' => 'حساب تجريبي',
                ]);
            }
            $usersByRole[$role->value] = $u;
        }

        $admin = $usersByRole[Role::Admin->value];
        $student = $usersByRole[Role::Student->value];

        foreach ($defs as $i => $d) {
            $subject = $subjects[$i];
            User::updateOrCreate(
                ['email' => $d['teacher_email']],
                [
                    'name' => $d['teacher_name'],
                    'password' => 'password',
                    'role' => Role::Teacher,
                    'subject_id' => $subject->id,
                    'is_active' => true,
                    'must_reset_password' => false,
                ]
            );
        }

        // Extra demo students
        $student2 = User::updateOrCreate(
            ['email' => 'student2@mateen.test'],
            [
                'name' => 'طالبة ثانية',
                'password' => 'password',
                'role' => Role::Student,
                'is_active' => true,
                'must_reset_password' => false,
            ]
        );
        StudentProfile::firstOrCreate(['user_id' => $student2->id], [
            'interview_status' => 'done',
            'status_class' => 'newcomer',
        ]);

        // Enroll demo student in first three subjects
        foreach ($subjects->take(3) as $subject) {
            Enrollment::updateOrCreate(
                ['user_id' => $student->id, 'subject_id' => $subject->id],
                ['enrolled_at' => now()->subDays(7)]
            );
        }
        Enrollment::updateOrCreate(
            ['user_id' => $student2->id, 'subject_id' => $subjects->first()->id],
            ['enrolled_at' => now()->subDays(3)]
        );

        $demoMaterials = [
            'tafsir' => [
                ['title' => 'مقدمة في علم التفسير', 'type' => MaterialType::Video, 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'body' => 'محاضرة تمهيدية'],
                ['title' => 'ملخص أسباب النزول', 'type' => MaterialType::Pdf, 'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'body' => 'مرجع مختصر'],
            ],
            'fiqh' => [
                ['title' => 'أحكام الطهارة', 'type' => MaterialType::Video, 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'body' => null],
                ['title' => 'مسائل فقهية معاصرة', 'type' => MaterialType::Article, 'url' => 'https://example.com/fiqh-notes', 'body' => 'قراءة مطلوبة'],
            ],
            'aqeedah' => [
                ['title' => 'أصول الإيمان', 'type' => MaterialType::Video, 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'body' => null],
            ],
            'hadeeth' => [
                ['title' => 'مقدمة مصطلح الحديث', 'type' => MaterialType::Pdf, 'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'body' => null],
            ],
            'maqraah' => [
                ['title' => 'أحكام النون الساكنة', 'type' => MaterialType::Link, 'url' => 'https://example.com/tajweed', 'body' => 'تسجيل صوتي تجريبي'],
            ],
        ];

        foreach ($subjects as $subject) {
            $items = $demoMaterials[$subject->slug] ?? [];
            foreach ($items as $order => $m) {
                LearningMaterial::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $m['title']],
                    [
                        'type' => $m['type'],
                        'url' => $m['url'],
                        'body' => $m['body'],
                        'created_by' => $admin->id,
                        'sort_order' => $order + 1,
                    ]
                );
            }
        }

        $tafsir = $subjects->firstWhere('slug', 'tafsir');
        $material = LearningMaterial::where('subject_id', $tafsir->id)->first();
        Assignment::updateOrCreate(
            ['subject_id' => $tafsir->id, 'title' => 'واجب تدبر — الأسبوع الأول'],
            [
                'learning_material_id' => $material?->id,
                'description' => 'اكتبي فقرة تدبر في آيات الدرس مع ذكر الفائدة العملية.',
                'due_at' => now()->addDays(10),
                'status' => 'open',
                'created_by' => $admin->id,
            ]
        );

        ScheduleEntry::updateOrCreate(
            ['title' => 'لقاء التفسير الأسبوعي', 'subject_id' => $tafsir->id],
            [
                'starts_at' => now()->next('Sunday')->setTime(18, 0),
                'ends_at' => now()->next('Sunday')->setTime(19, 30),
                'weekday' => 0,
                'audience' => ['students'],
                'created_by' => $admin->id,
            ]
        );

        LibraryItem::updateOrCreate(
            ['section' => LibrarySection::MateenLibrary->value, 'title' => 'مكتبة متين — أصول التفسير'],
            [
                'description' => 'مرجع تجريبي للمكتبة العلمية',
                'media_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'subject_filter' => 'التفسير',
                'created_by' => $admin->id,
                'sort_order' => 1,
            ]
        );
        LibraryItem::updateOrCreate(
            ['section' => LibrarySection::Enrichment->value, 'title' => 'إثرائيات — قصص الأنبياء'],
            [
                'description' => 'محتوى إثرائي تجريبي',
                'media_url' => 'https://example.com/enrichment',
                'created_by' => $admin->id,
                'sort_order' => 1,
            ]
        );
        LibraryItem::updateOrCreate(
            ['section' => LibrarySection::Podcast->value, 'title' => 'بودكاست متين — حلقة تجريبية'],
            [
                'description' => 'حلقة صوتية تجريبية',
                'media_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
                'created_by' => $admin->id,
                'sort_order' => 1,
            ]
        );

        NewsItem::updateOrCreate(
            ['title' => 'افتتاح المستوى الثاني'],
            [
                'body' => 'مرحباً بكن في المستوى الثاني من برنامج متين العلمي. هذه نشرة تجريبية من واجهة Laravel.',
                'status' => NewsStatus::Published,
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
            ]
        );
        NewsItem::updateOrCreate(
            ['title' => 'تذكير: مواعيد اللقاءات'],
            [
                'body' => 'تُنشر الجداول الأسبوعية عبر صفحة المواد العلمية والمواعيد. حسابات التجربة جاهزة للتجربة.',
                'status' => NewsStatus::Published,
                'published_at' => now(),
                'created_by' => $admin->id,
            ]
        );
    }
}
