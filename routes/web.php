<?php

use App\Http\Controllers\Web\AuthPageController;
use App\Http\Controllers\Web\PublicPageController;
use App\Http\Controllers\Web\StaffPageController;
use App\Http\Controllers\Web\StudentPageController;
use App\Http\Controllers\Web\TeacherPageController;
use Illuminate\Support\Facades\Route;

$alias = function (string $html, string $name, string $to) {
    Route::redirect('/Mateen/html/'.$html, $to, 301)->name($name.'.html');
};

Route::get('/', [PublicPageController::class, 'home'])->name('mateen.home');
$alias('home.html', 'mateen.home', '/');

Route::get('/about', [PublicPageController::class, 'about'])->name('mateen.about');
$alias('about.html', 'mateen.about', '/about');

Route::get('/courses', [PublicPageController::class, 'courses'])->name('mateen.courses');
$alias('courses.html', 'mateen.courses', '/courses');

Route::get('/library', [PublicPageController::class, 'library'])->name('mateen.library');
$alias('library.html', 'mateen.library', '/library');

Route::get('/news', [PublicPageController::class, 'news'])->name('mateen.news');
$alias('news.html', 'mateen.news', '/news');

Route::get('/schedule', [PublicPageController::class, 'schedule'])->name('mateen.schedule');
$alias('schedule.html', 'mateen.schedule', '/schedule');

Route::get('/login', [AuthPageController::class, 'login'])->name('mateen.login');
$alias('login.html', 'mateen.login', '/login');

Route::get('/onboarding', [AuthPageController::class, 'onboarding'])->name('mateen.onboarding');
$alias('onboarding.html', 'mateen.onboarding', '/onboarding');

Route::get('/messages', [StaffPageController::class, 'messages'])->name('mateen.messages');
$alias('messages.html', 'mateen.messages', '/messages');

Route::get('/stats', [StaffPageController::class, 'stats'])->name('mateen.stats');
$alias('stats.html', 'mateen.stats', '/stats');

Route::get('/student', [StudentPageController::class, 'dashboard'])->name('mateen.student');
$alias('student.html', 'mateen.student', '/student');

Route::get('/student/general', [StudentPageController::class, 'general'])->name('mateen.student.general');
$alias('student-general.html', 'mateen.student.general', '/student/general');

Route::get('/student/view', [StudentPageController::class, 'view'])->name('mateen.student.view');
$alias('student-view.html', 'mateen.student.view', '/student/view');

Route::get('/teacher/tafseer', [TeacherPageController::class, 'tafseer'])->name('mateen.teacher.tafseer');
$alias('teacher-tafseer.html', 'mateen.teacher.tafseer', '/teacher/tafseer');

Route::get('/teacher/fiqh', [TeacherPageController::class, 'fiqh'])->name('mateen.teacher.fiqh');
$alias('teacher-fiqh.html', 'mateen.teacher.fiqh', '/teacher/fiqh');

Route::get('/teacher/aqeedah', [TeacherPageController::class, 'aqeedah'])->name('mateen.teacher.aqeedah');
$alias('teacher-aqeedah.html', 'mateen.teacher.aqeedah', '/teacher/aqeedah');

Route::get('/teacher/hadeeth', [TeacherPageController::class, 'hadeeth'])->name('mateen.teacher.hadeeth');
$alias('teacher-hadeeth.html', 'mateen.teacher.hadeeth', '/teacher/hadeeth');

Route::get('/teacher/quran1', [TeacherPageController::class, 'quran1'])->name('mateen.teacher.quran1');
$alias('teacher-quran1.html', 'mateen.teacher.quran1', '/teacher/quran1');

Route::get('/teacher/quran2', [TeacherPageController::class, 'quran2'])->name('mateen.teacher.quran2');
$alias('teacher-quran2.html', 'mateen.teacher.quran2', '/teacher/quran2');

Route::get('/teacher/ithraiyat', [TeacherPageController::class, 'ithraiyat'])->name('mateen.teacher.ithraiyat');
$alias('teacher-ithraiyat.html', 'mateen.teacher.ithraiyat', '/teacher/ithraiyat');

Route::get('/teacher/library', [TeacherPageController::class, 'library'])->name('mateen.teacher.library');
$alias('teacher-library.html', 'mateen.teacher.library', '/teacher/library');

Route::get('/teacher/profile', [TeacherPageController::class, 'profile'])->name('mateen.teacher.profile');
$alias('teacher-profile.html', 'mateen.teacher.profile', '/teacher/profile');

Route::get('/teacher/schedule', [TeacherPageController::class, 'schedule'])->name('mateen.teacher.schedule');
$alias('teacher-schedule.html', 'mateen.teacher.schedule', '/teacher/schedule');

Route::get('/teacher/students', [TeacherPageController::class, 'students'])->name('mateen.teacher.students');
$alias('teacher-students.html', 'mateen.teacher.students', '/teacher/students');

Route::get('/admin', [StaffPageController::class, 'admin'])->name('mateen.admin');
$alias('admin.html', 'mateen.admin', '/admin');

Route::get('/supervisor', [StaffPageController::class, 'supervisor'])->name('mateen.supervisor');
$alias('supervisor.html', 'mateen.supervisor', '/supervisor');

Route::get('/support', [StaffPageController::class, 'support'])->name('mateen.support');
$alias('support.html', 'mateen.support', '/support');

Route::get('/my-students', [StaffPageController::class, 'myStudents'])->name('mateen.my-students');
$alias('my-students.html', 'mateen.my-students', '/my-students');
