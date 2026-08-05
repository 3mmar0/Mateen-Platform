<?php

/**
 * One-shot converter: copy live HTML → resources/html-source + Blade views.
 * Asset paths become /Mateen/… ; in-app *.html links become /Mateen/html/*.html
 */

$root = dirname(__DIR__);
$htmlDir = $root.'/public/Mateen/html';
$sourceDir = $root.'/resources/html-source';
$viewsDir = $root.'/resources/views';

$map = [
    'home' => 'pages/home.full.blade.php',
    'about' => 'pages/about.blade.php',
    'courses' => 'pages/courses.blade.php',
    'library' => 'pages/library.blade.php',
    'news' => 'pages/news.blade.php',
    'schedule' => 'pages/schedule.blade.php',
    'login' => 'pages/login.blade.php',
    'onboarding' => 'pages/onboarding.blade.php',
    'messages' => 'pages/messages.blade.php',
    'stats' => 'pages/stats.blade.php',
    'student' => 'pages/student/dashboard.blade.php',
    'student-general' => 'pages/student/general.blade.php',
    'student-view' => 'pages/student/view.blade.php',
    'teacher-tafseer' => 'pages/teacher/tafseer.blade.php',
    'teacher-fiqh' => 'pages/teacher/fiqh.blade.php',
    'teacher-aqeedah' => 'pages/teacher/aqeedah.blade.php',
    'teacher-hadeeth' => 'pages/teacher/hadeeth.blade.php',
    'teacher-quran1' => 'pages/teacher/quran1.blade.php',
    'teacher-quran2' => 'pages/teacher/quran2.blade.php',
    'teacher-ithraiyat' => 'pages/teacher/ithraiyat.blade.php',
    'teacher-library' => 'pages/teacher/library.blade.php',
    'teacher-profile' => 'pages/teacher/profile.blade.php',
    'teacher-schedule' => 'pages/teacher/schedule.blade.php',
    'teacher-students' => 'pages/teacher/students.blade.php',
    'admin' => 'pages/admin/dashboard.blade.php',
    'supervisor' => 'pages/supervisor/dashboard.blade.php',
    'support' => 'pages/support/dashboard.blade.php',
    'my-students' => 'pages/staff/my-students.blade.php',
];

function convert_html(string $html): string
{
    $replacements = [
        '../css/' => '/Mateen/css/',
        '../js/' => '/Mateen/js/',
        '../libs/' => '/Mateen/libs/',
        '../logo' => '/Mateen/logo',
        '../hero-bg' => '/Mateen/hero-bg',
        '../favicon.ico' => '/favicon.ico',
        '../star-ornament' => '/Mateen/star-ornament',
        '../schedule.jpg' => '/Mateen/schedule.jpg',
        '../schedule-wird.jpg' => '/Mateen/schedule-wird.jpg',
        '../shuraka-logo.png' => '/Mateen/shuraka-logo.png',
        '../manifest.json' => '/Mateen/manifest.json',
        '/Mateen/html/' => '/Mateen/html/',
    ];
    $html = strtr($html, $replacements);

    $html = preg_replace(
        '/((?:href|action)\s*=\s*["\'])(?!https?:|\/|#|mailto:|tel:|javascript:)([a-z0-9._-]+\.html)/i',
        '$1/Mateen/html/$2',
        $html
    ) ?? $html;

    $html = preg_replace(
        '/((?:window\.location(?:\.href)?|location\.href)\s*=\s*[\'"])([a-z0-9._-]+\.html)/i',
        '$1/Mateen/html/$2',
        $html
    ) ?? $html;

    $html = preg_replace(
        '/(history\.back\(\)\s*:\s*window\.location\.href\s*=\s*[\'"])([a-z0-9._-]+\.html)/i',
        '$1/Mateen/html/$2',
        $html
    ) ?? $html;

    return str_replace('@', '@@', $html);
}

if (! is_dir($htmlDir)) {
    fwrite(STDERR, "Missing $htmlDir\n");
    exit(1);
}

if (! is_dir($sourceDir)) {
    mkdir($sourceDir, 0777, true);
}

foreach (glob($htmlDir.'/*.html') ?: [] as $file) {
    $name = basename($file);
    if (str_contains($name, '_backup')) {
        continue;
    }
    copy($file, $sourceDir.'/'.$name);
    $stem = basename($name, '.html');
    if (! isset($map[$stem])) {
        echo "skip unmapped $name\n";
        continue;
    }
    $dest = $viewsDir.'/'.$map[$stem];
    $dir = dirname($dest);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $blade = convert_html((string) file_get_contents($file));
    file_put_contents($dest, $blade);
    echo "wrote {$map[$stem]}\n";
}

echo "done\n";
