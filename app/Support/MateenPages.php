<?php

namespace App\Support;

class MateenPages
{
    /** @return array<string, array{view:string, name:string, clean:string}> */
    public static function catalog(): array
    {
        return [
            'home' => ['view' => 'pages.home', 'name' => 'mateen.home', 'clean' => '/'],
            'about' => ['view' => 'pages.about', 'name' => 'mateen.about', 'clean' => '/about'],
            'courses' => ['view' => 'pages.courses', 'name' => 'mateen.courses', 'clean' => '/courses'],
            'library' => ['view' => 'pages.library', 'name' => 'mateen.library', 'clean' => '/library'],
            'news' => ['view' => 'pages.news', 'name' => 'mateen.news', 'clean' => '/news'],
            'schedule' => ['view' => 'pages.schedule', 'name' => 'mateen.schedule', 'clean' => '/schedule'],
            'login' => ['view' => 'pages.login', 'name' => 'mateen.login', 'clean' => '/login'],
            'onboarding' => ['view' => 'pages.onboarding', 'name' => 'mateen.onboarding', 'clean' => '/onboarding'],
            'messages' => ['view' => 'pages.messages', 'name' => 'mateen.messages', 'clean' => '/messages'],
            'stats' => ['view' => 'pages.stats', 'name' => 'mateen.stats', 'clean' => '/stats'],
            'student' => ['view' => 'pages.student.dashboard', 'name' => 'mateen.student', 'clean' => '/student'],
            'student-general' => ['view' => 'pages.student.general', 'name' => 'mateen.student.general', 'clean' => '/student/general'],
            'student-view' => ['view' => 'pages.student.view', 'name' => 'mateen.student.view', 'clean' => '/student/view'],
            'teacher-tafseer' => ['view' => 'pages.teacher.tafseer', 'name' => 'mateen.teacher.tafseer', 'clean' => '/teacher/tafseer'],
            'teacher-fiqh' => ['view' => 'pages.teacher.fiqh', 'name' => 'mateen.teacher.fiqh', 'clean' => '/teacher/fiqh'],
            'teacher-aqeedah' => ['view' => 'pages.teacher.aqeedah', 'name' => 'mateen.teacher.aqeedah', 'clean' => '/teacher/aqeedah'],
            'teacher-hadeeth' => ['view' => 'pages.teacher.hadeeth', 'name' => 'mateen.teacher.hadeeth', 'clean' => '/teacher/hadeeth'],
            'teacher-quran1' => ['view' => 'pages.teacher.quran1', 'name' => 'mateen.teacher.quran1', 'clean' => '/teacher/quran1'],
            'teacher-quran2' => ['view' => 'pages.teacher.quran2', 'name' => 'mateen.teacher.quran2', 'clean' => '/teacher/quran2'],
            'teacher-ithraiyat' => ['view' => 'pages.teacher.ithraiyat', 'name' => 'mateen.teacher.ithraiyat', 'clean' => '/teacher/ithraiyat'],
            'teacher-library' => ['view' => 'pages.teacher.library', 'name' => 'mateen.teacher.library', 'clean' => '/teacher/library'],
            'teacher-profile' => ['view' => 'pages.teacher.profile', 'name' => 'mateen.teacher.profile', 'clean' => '/teacher/profile'],
            'teacher-schedule' => ['view' => 'pages.teacher.schedule', 'name' => 'mateen.teacher.schedule', 'clean' => '/teacher/schedule'],
            'teacher-students' => ['view' => 'pages.teacher.students', 'name' => 'mateen.teacher.students', 'clean' => '/teacher/students'],
            'admin' => ['view' => 'pages.admin.dashboard', 'name' => 'mateen.admin', 'clean' => '/admin'],
            'supervisor' => ['view' => 'pages.supervisor.dashboard', 'name' => 'mateen.supervisor', 'clean' => '/supervisor'],
            'support' => ['view' => 'pages.support.dashboard', 'name' => 'mateen.support', 'clean' => '/support'],
            'my-students' => ['view' => 'pages.staff.my-students', 'name' => 'mateen.my-students', 'clean' => '/my-students'],
        ];
    }
}
