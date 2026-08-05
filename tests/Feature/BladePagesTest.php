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
            ['/courses'],
            ['/library'],
            ['/news'],
            ['/schedule'],
            ['/login'],
            ['/onboarding'],
            ['/messages'],
            ['/stats'],
            ['/student'],
            ['/student/general'],
            ['/student/view'],
            ['/teacher/tafseer'],
            ['/teacher/fiqh'],
            ['/teacher/aqeedah'],
            ['/teacher/hadeeth'],
            ['/teacher/quran1'],
            ['/teacher/quran2'],
            ['/teacher/ithraiyat'],
            ['/teacher/library'],
            ['/teacher/profile'],
            ['/teacher/schedule'],
            ['/teacher/students'],
            ['/admin'],
            ['/supervisor'],
            ['/support'],
            ['/my-students'],
        ];
    }

    /** @return list<array{0:string,1:string}> */
    public static function legacyRedirectProvider(): array
    {
        return [
            ['/Mateen/html/home.html', '/'],
            ['/Mateen/html/about.html', '/about'],
            ['/Mateen/html/courses.html', '/courses'],
            ['/Mateen/html/library.html', '/library'],
            ['/Mateen/html/news.html', '/news'],
            ['/Mateen/html/schedule.html', '/schedule'],
            ['/Mateen/html/login.html', '/login'],
            ['/Mateen/html/onboarding.html', '/onboarding'],
            ['/Mateen/html/messages.html', '/messages'],
            ['/Mateen/html/stats.html', '/stats'],
            ['/Mateen/html/student.html', '/student'],
            ['/Mateen/html/student-general.html', '/student/general'],
            ['/Mateen/html/student-view.html', '/student/view'],
            ['/Mateen/html/teacher-tafseer.html', '/teacher/tafseer'],
            ['/Mateen/html/teacher-fiqh.html', '/teacher/fiqh'],
            ['/Mateen/html/teacher-aqeedah.html', '/teacher/aqeedah'],
            ['/Mateen/html/teacher-hadeeth.html', '/teacher/hadeeth'],
            ['/Mateen/html/teacher-quran1.html', '/teacher/quran1'],
            ['/Mateen/html/teacher-quran2.html', '/teacher/quran2'],
            ['/Mateen/html/teacher-ithraiyat.html', '/teacher/ithraiyat'],
            ['/Mateen/html/teacher-library.html', '/teacher/library'],
            ['/Mateen/html/teacher-profile.html', '/teacher/profile'],
            ['/Mateen/html/teacher-schedule.html', '/teacher/schedule'],
            ['/Mateen/html/teacher-students.html', '/teacher/students'],
            ['/Mateen/html/admin.html', '/admin'],
            ['/Mateen/html/supervisor.html', '/supervisor'],
            ['/Mateen/html/support.html', '/support'],
            ['/Mateen/html/my-students.html', '/my-students'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pageProvider')]
    public function test_blade_pages_return_ok(string $path): void
    {
        $this->get($path)->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('legacyRedirectProvider')]
    public function test_legacy_html_paths_redirect_to_clean_urls(string $from, string $to): void
    {
        $this->get($from)->assertRedirect($to);
    }

    public function test_home_is_blade(): void
    {
        $this->get('/')->assertOk()->assertSee('برنامج متين العلمي', false)->assertSee('data-blade-home', false);
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
