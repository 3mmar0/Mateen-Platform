<?php

use App\Http\Controllers\Web\AuthPageController;
use App\Http\Controllers\Web\PublicPageController;
use App\Http\Controllers\Web\StaffPageController;
use App\Http\Controllers\Web\StudentPageController;
use App\Http\Controllers\Web\TeacherPageController;
use Illuminate\Support\Facades\Route;

$alias = function (string $html, string $name, array|string $action) {
    Route::get('/Mateen/html/'.$html, $action)->name($name.'.html');
};

Route::get('/', [PublicPageController::class, 'home'])->name('mateen.home');
Route::redirect('/Mateen/html/home.html', '/', 301)->name('mateen.home.html');

Route::get('/about', [PublicPageController::class, 'about'])->name('mateen.about');
$alias('about.html', 'mateen.about', [PublicPageController::class, 'about']);

Route::get('/courses', [PublicPageController::class, 'courses'])->name('mateen.courses');
$alias('courses.html', 'mateen.courses', [PublicPageController::class, 'courses']);

Route::get('/library', [PublicPageController::class, 'library'])->name('mateen.library');
$alias('library.html', 'mateen.library', [PublicPageController::class, 'library']);

Route::get('/news', [PublicPageController::class, 'news'])->name('mateen.news');
$alias('news.html', 'mateen.news', [PublicPageController::class, 'news']);

Route::get('/schedule', [PublicPageController::class, 'schedule'])->name('mateen.schedule');
$alias('schedule.html', 'mateen.schedule', [PublicPageController::class, 'schedule']);

Route::get('/login', [AuthPageController::class, 'login'])->name('mateen.login');
$alias('login.html', 'mateen.login', [AuthPageController::class, 'login']);

Route::get('/onboarding', [AuthPageController::class, 'onboarding'])->name('mateen.onboarding');
$alias('onboarding.html', 'mateen.onboarding', [AuthPageController::class, 'onboarding']);

Route::get('/messages', [StaffPageController::class, 'messages'])->name('mateen.messages');
$alias('messages.html', 'mateen.messages', [StaffPageController::class, 'messages']);

Route::get('/stats', [StaffPageController::class, 'stats'])->name('mateen.stats');
$alias('stats.html', 'mateen.stats', [StaffPageController::class, 'stats']);

Route::get('/student', [StudentPageController::class, 'dashboard'])->name('mateen.student');
$alias('student.html', 'mateen.student', [StudentPageController::class, 'dashboard']);

Route::get('/student/general', [StudentPageController::class, 'general'])->name('mateen.student.general');
$alias('student-general.html', 'mateen.student.general', [StudentPageController::class, 'general']);

Route::get('/student/view', [StudentPageController::class, 'view'])->name('mateen.student.view');
$alias('student-view.html', 'mateen.student.view', [StudentPageController::class, 'view']);

Route::get('/teacher/tafseer', [TeacherPageController::class, 'tafseer'])->name('mateen.teacher.tafseer');
$alias('teacher-tafseer.html', 'mateen.teacher.tafseer', [TeacherPageController::class, 'tafseer']);

Route::get('/teacher/fiqh', [TeacherPageController::class, 'fiqh'])->name('mateen.teacher.fiqh');
$alias('teacher-fiqh.html', 'mateen.teacher.fiqh', [TeacherPageController::class, 'fiqh']);

Route::get('/teacher/aqeedah', [TeacherPageController::class, 'aqeedah'])->name('mateen.teacher.aqeedah');
$alias('teacher-aqeedah.html', 'mateen.teacher.aqeedah', [TeacherPageController::class, 'aqeedah']);

Route::get('/teacher/hadeeth', [TeacherPageController::class, 'hadeeth'])->name('mateen.teacher.hadeeth');
$alias('teacher-hadeeth.html', 'mateen.teacher.hadeeth', [TeacherPageController::class, 'hadeeth']);

Route::get('/teacher/quran1', [TeacherPageController::class, 'quran1'])->name('mateen.teacher.quran1');
$alias('teacher-quran1.html', 'mateen.teacher.quran1', [TeacherPageController::class, 'quran1']);

Route::get('/teacher/quran2', [TeacherPageController::class, 'quran2'])->name('mateen.teacher.quran2');
$alias('teacher-quran2.html', 'mateen.teacher.quran2', [TeacherPageController::class, 'quran2']);

Route::get('/teacher/ithraiyat', [TeacherPageController::class, 'ithraiyat'])->name('mateen.teacher.ithraiyat');
$alias('teacher-ithraiyat.html', 'mateen.teacher.ithraiyat', [TeacherPageController::class, 'ithraiyat']);

Route::get('/teacher/library', [TeacherPageController::class, 'library'])->name('mateen.teacher.library');
$alias('teacher-library.html', 'mateen.teacher.library', [TeacherPageController::class, 'library']);

Route::get('/teacher/profile', [TeacherPageController::class, 'profile'])->name('mateen.teacher.profile');
$alias('teacher-profile.html', 'mateen.teacher.profile', [TeacherPageController::class, 'profile']);

Route::get('/teacher/schedule', [TeacherPageController::class, 'schedule'])->name('mateen.teacher.schedule');
$alias('teacher-schedule.html', 'mateen.teacher.schedule', [TeacherPageController::class, 'schedule']);

Route::get('/teacher/students', [TeacherPageController::class, 'students'])->name('mateen.teacher.students');
$alias('teacher-students.html', 'mateen.teacher.students', [TeacherPageController::class, 'students']);

Route::get('/admin', [StaffPageController::class, 'admin'])->name('mateen.admin');
$alias('admin.html', 'mateen.admin', [StaffPageController::class, 'admin']);

Route::get('/supervisor', [StaffPageController::class, 'supervisor'])->name('mateen.supervisor');
$alias('supervisor.html', 'mateen.supervisor', [StaffPageController::class, 'supervisor']);

Route::get('/support', [StaffPageController::class, 'support'])->name('mateen.support');
$alias('support.html', 'mateen.support', [StaffPageController::class, 'support']);

Route::get('/my-students', [StaffPageController::class, 'myStudents'])->name('mateen.my-students');
$alias('my-students.html', 'mateen.my-students', [StaffPageController::class, 'myStudents']);
