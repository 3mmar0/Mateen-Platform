<?php

$root = dirname(__DIR__);
$home = file_get_contents($root.'/resources/views/pages/home.full.blade.php');
if ($home === false) {
    fwrite(STDERR, "run build-blade-ui.php first\n");
    exit(1);
}

if (! preg_match('/<!-- NAVBAR -->(.*)<!-- PAGE LAYOUT -->/s', $home, $nav)) {
    fwrite(STDERR, "nav block not found\n");
    exit(1);
}
if (! preg_match('/<!-- PAGE LAYOUT -->(.*)<!-- CONTACT SECTION -->/s', $home, $main)) {
    fwrite(STDERR, "main block not found\n");
    exit(1);
}
if (! preg_match('/<!-- CONTACT SECTION -->(.*)<!-- AYA BANNER -->/s', $home, $contact)) {
    fwrite(STDERR, "contact block not found\n");
    exit(1);
}
if (! preg_match('/<!-- AYA BANNER -->(.*)<!-- FOOTER -->/s', $home, $aya)) {
    fwrite(STDERR, "aya block not found\n");
    exit(1);
}

file_put_contents($root.'/resources/views/partials/nav.blade.php', "<!-- NAVBAR -->\n".trim($nav[1])."\n");

$subjectsLoop = <<<'BLADE'
    <section class="page-section bg-white">
      <div class="section-title">المواد العلمية</div>
      <div class="row g-3">
        @forelse ($subjects as $subject)
        <div class="col-6 col-sm-4 col-md-3 col-xl-2dot4">
          <div class="path-card" onclick="window.location.href='{{ url('/Mateen/html/courses.html') }}'">
            <div class="path-icon">{{ $subject->icon ?? '📚' }}</div>
            <div class="path-name">{{ $subject->title }}</div>
            <button class="path-btn">عرض التفاصيل</button>
          </div>
        </div>
        @empty
        <div class="col-12" style="text-align:center;color:#888;">لا توجد مواد بعد</div>
        @endforelse
      </div>
    </section>
BLADE;

$mainHtml = $main[1];
$mainHtml = preg_replace('/<!-- SUBJECTS -->.*<!-- IMPORTANT DATES -->/s', $subjectsLoop."\n\n    <!-- IMPORTANT DATES -->", $mainHtml);

$newsServer = <<<'BLADE'
    <section class="page-section ann-section" id="publicNewsSection" @if($newsItems->isEmpty()) style="display:none;" @endif>
      <div class="section-title">آخر الإعلانات</div>
      <div class="row g-3" id="publicNewsList">
        @foreach ($newsItems as $item)
        <div class="col-12 col-md-6 col-lg-4">
          <div class="news-card" style="background:white;border-radius:14px;border:1px solid var(--border);padding:18px 20px;height:100%;">
            <h3 style="font-family:Amiri,serif;font-size:16px;color:var(--green-dark);margin:10px 0 6px;">{{ $item->title }}</h3>
            <p style="font-size:13px;color:var(--text-mid);line-height:1.6;margin-bottom:10px;">{{ \Illuminate\Support\Str::limit(strip_tags($item->body), 100) }}</p>
            <div style="font-size:11px;color:#aaa;">{{ optional($item->published_at)->translatedFormat('d F Y') }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </section>
BLADE;

$mainHtml = preg_replace('/<!-- ANNOUNCEMENTS -->.*<!-- STATS BAR/s', "<!-- ANNOUNCEMENTS -->\n    <div class=\"islamic-divider\"><span>✦ ❧ ✦</span></div>\n".$newsServer."\n\n    <!-- STATS BAR", $mainHtml);

$homeBlade = <<<BLADE
@extends('layouts.guest')

@section('title', 'برنامج متين العلمي — الصفحة الرئيسية')

@push('styles')
<link href="{{ mateen_asset('css/home.css') }}" rel="stylesheet"/>
@endpush

@section('content')
<!-- PAGE LAYOUT -->
{$mainHtml}
<!-- CONTACT SECTION -->
{$contact[1]}
<!-- AYA BANNER -->
{$aya[1]}
@endsection

@push('scripts')
<script src="{{ mateen_asset('libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ mateen_asset('js/home.js') }}?v=20260805" type="module"></script>
<script src="{{ mateen_asset('js/home-msg.js') }}?v=20260805" type="module"></script>
<script src="{{ mateen_asset('js/sw-register.js') }}?v=20260805"></script>
<script src="{{ mateen_asset('js/tour.js') }}?v=20260805"></script>
@endpush
BLADE;

file_put_contents($root.'/resources/views/pages/home.blade.php', $homeBlade);
echo "extracted nav + home.blade.php\n";
