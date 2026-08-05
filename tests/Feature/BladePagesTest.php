<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BladePagesTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<string> */
    public static function pageProvider(): array
    {
        return [
            ['/'],
            ['/about'],
            ['/Mateen/html/about.html'],
            ['/courses'],
            ['/Mateen/html/courses.html'],
            ['/library'],
            ['/Mateen/html/library.html'],
            ['/news'],
            ['/Mateen/html/news.html'],
            ['/schedule'],
            ['/Mateen/html/schedule.html'],
            ['/login'],
            ['/Mateen/html/login.html'],
            ['/onboarding'],
            ['/Mateen/html/onboarding.html'],
            ['/messages'],
            ['/Mateen/html/messages.html'],
            ['/stats'],
            ['/Mateen/html/stats.html'],
            ['/student'],
            ['/Mateen/html/student.html'],
            ['/student/general'],
            ['/Mateen/html/student-general.html'],
            ['/student/view'],
            ['/Mateen/html/student-view.html'],
            ['/teacher/tafseer'],
            ['/Mateen/html/teacher-tafseer.html'],
            ['/teacher/fiqh'],
            ['/Mateen/html/teacher-fiqh.html'],
            ['/teacher/aqeedah'],
            ['/Mateen/html/teacher-aqeedah.html'],
            ['/teacher/hadeeth'],
            ['/Mateen/html/teacher-hadeeth.html'],
            ['/teacher/quran1'],
            ['/Mateen/html/teacher-quran1.html'],
            ['/teacher/quran2'],
            ['/Mateen/html/teacher-quran2.html'],
            ['/teacher/ithraiyat'],
            ['/Mateen/html/teacher-ithraiyat.html'],
            ['/teacher/library'],
            ['/Mateen/html/teacher-library.html'],
            ['/teacher/profile'],
            ['/Mateen/html/teacher-profile.html'],
            ['/teacher/schedule'],
            ['/Mateen/html/teacher-schedule.html'],
            ['/teacher/students'],
            ['/Mateen/html/teacher-students.html'],
            ['/admin'],
            ['/Mateen/html/admin.html'],
            ['/supervisor'],
            ['/Mateen/html/supervisor.html'],
            ['/support'],
            ['/Mateen/html/support.html'],
            ['/my-students'],
            ['/Mateen/html/my-students.html'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pageProvider')]
    public function test_blade_pages_return_ok(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public function test_home_is_blade_and_legacy_path_redirects(): void
    {
        $this->get('/')->assertOk()->assertSee('برنامج متين العلمي', false);
        $this->get('/Mateen/html/home.html')->assertRedirect('/');
    }

    public function test_contact_and_registration_endpoints(): void
    {
        $this->postJson('/api/v1/contact', [
            'name' => 'فاطمة',
            'topic' => 'سؤال علمي',
            'body' => 'السلام عليكم',
        ])->assertCreated();

        $this->postJson('/api/v1/registration-requests', [
            'name' => 'فاطمة',
            'phone' => '0500000000',
            'email' => 'fatima@example.com',
            'level' => 'المستوى الأول (مبتدئ)',
        ])->assertCreated();
    }
}
