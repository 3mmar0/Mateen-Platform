<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
@include('partials.theme-boot')
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}"/>
<title>@yield('title', 'برنامج متين العلمي')</title>
@include('partials.head-pwa')
<link href="{{ mateen_asset('libs/fonts/arabic-fonts.css') }}" rel="stylesheet"/>
<link href="{{ mateen_asset('libs/tabler-icons/tabler-icons.min.css') }}" rel="stylesheet"/>
<link href="{{ mateen_asset('libs/bootstrap/bootstrap.rtl.min.css') }}" rel="stylesheet"/>
<link href="{{ mateen_asset('css/shared.css') }}" rel="stylesheet"/>
<link href="{{ mateen_asset('css/mobile.css') }}" rel="stylesheet"/>
<link href="{{ mateen_asset('css/responsive-fix.css') }}" rel="stylesheet"/>
<link href="{{ mateen_asset('css/notifications.css') }}" rel="stylesheet"/>
<link href="{{ mateen_asset('css/islamic.css') }}" rel="stylesheet"/>
@stack('styles')
</head>
<body>
@include('partials.basmala')
@include('partials.nav')
@yield('content')
@include('partials.footer')
@include('partials.register-modal')
@stack('scripts')
</body>
</html>
