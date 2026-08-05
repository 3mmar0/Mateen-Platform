@extends('layouts.guest')

@section('title', 'برنامج متين العلمي — الصفحة الرئيسية')

@push('styles')
<link href="{{ mateen_asset('css/home.css') }}?v=20260805reveal" rel="stylesheet"/>
@endpush

@section('content')
<div data-blade-home="1" hidden></div>
<!-- PAGE LAYOUT -->

<div class="page-layout guest-layout">

  <!-- SIDEBAR — Bootstrap Offcanvas -->
  <aside class="offcanvas offcanvas-start sidebar-offcanvas" tabindex="-1" id="mainSidebar" aria-labelledby="mainSidebarLabel">

    <!-- Button الإغلاق -->
    <div class="offcanvas-header border-bottom px-3 py-2">
      <h6 class="offcanvas-title text-gold mb-0" id="mainSidebarLabel" style="font-family:Amiri,serif;">متين العلمي</h6>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">

      <!-- Loading state -->
      <div id="sidebar-loading" style="display:flex;flex-direction:column;align-items:center;justify-content:center;flex:1;padding:40px 20px;gap:12px;">
        <div style="width:36px;height:36px;border:3px solid var(--border);border-top-color:var(--gold);border-radius:50%;animation:spin 0.8s linear infinite;"></div>
        <div style="font-size:12px;color:var(--text-mid);">جاري التحقق...</div>
      </div>

      <!-- ══ حالة الزائر ══ -->
      <div id="sidebar-guest" class="d-none">

        <!-- الIfجو -->
        <div class="text-center py-3 px-3">
          <img alt="متين" src="/Mateen/logo.png" class="sidebar-logo-img mb-2"/>
          <div class="sidebar-brand">برنامج متين العلمي</div>
          <div class="sidebar-sub">نحو بناء علميٍّ متين</div>
        </div>

        <hr class="sidebar-divider mx-3 my-1"/>

        <!-- Buttons الدخول  and the تسجيل -->
        <div class="d-flex flex-column gap-2 px-3 pb-2">
          <a class="sidebar-login-btn" href="/Mateen/html/login.html">
            <i class="ti ti-login"></i> تسجيل الدخول
          </a>
          <button class="sidebar-reg-btn" onclick="document.getElementById('reg-modal').classList.add('open')">
            <i class="ti ti-user-plus"></i> التسجيل في البرنامج
          </button>
        </div>

        <hr class="sidebar-divider mx-3 my-1"/>

        <!-- روابط الNavigation -->
        <nav class="sidebar-nav flex-grow-1">
          <a class="active" href="/"><i class="ti ti-home"></i> الرئيسية</a>
          <a href="/Mateen/html/about.html"><i class="ti ti-info-circle"></i> عن البرنامج</a>
          <a href="/Mateen/html/courses.html"><i class="ti ti-books"></i> المواد العلمية</a>
          <a href="/Mateen/html/library.html"><i class="ti ti-library"></i> المكتبة</a>
          <a href="/Mateen/html/news.html"><i class="ti ti-speakerphone"></i> الأخبار</a>
          <a href="#contact"><i class="ti ti-headset"></i> تواصل معنا</a>
        </nav>
      </div>

      <!-- ══ حالة المسجّل ══ -->
      <div id="sidebar-user" class="sidebar-user-hidden flex-column h-100">

        <!-- هيدر User -->
        <div class="sidebar-header">
          <div class="sidebar-avatar" id="sidebarAvatar">🧕</div>
          <div class="user-info">
            <div class="user-name" id="sidebarName">...</div>
            <div class="user-role" id="sidebarRole">الطالبة</div>
          </div>
        </div>

        <hr class="sidebar-divider mx-3 my-1"/>

        <!-- روابط الNavigation -->
        <nav class="sidebar-nav" id="sidebarNav">
          <a class="active" href="/"><i class="ti ti-home"></i> الرئيسية</a>
          <a href="/Mateen/html/messages.html" id="sidebarMsgLink">
            <i class="ti ti-message-2"></i> رسائلي
            <span class="nav-msg-badge d-none ms-auto" id="sidebarMsgBadge">0</span>
          </a>
          <a href="/Mateen/html/student.html" id="profileLink" class="d-none"><i class="ti ti-user"></i> ملفي الشخصي</a>
          <a href="#" id="linkCerts" class="d-none"><i class="ti ti-certificate"></i> شهاداتي</a>
          <a href="#" id="linkAwards" class="d-none"><i class="ti ti-award"></i> إجازاتي</a>
          <a href="#" id="linkGrades" class="d-none"><i class="ti ti-chart-bar"></i> درجاتي</a>
          <a href="/Mateen/html/schedule.html" id="linkSchedule" class="d-none"><i class="ti ti-calendar"></i> جدولي الدراسي</a>
          <a href="/Mateen/html/admin.html" id="linkAdmin" class="d-none"><i class="ti ti-shield"></i> لوحة الإدارة</a>
          <a href="/Mateen/html/news.html" id="linkNews" class="d-none"><i class="ti ti-speakerphone"></i> الأخبار</a>
          <a href="/Mateen/html/my-students.html" id="linkTeacher" class="d-none"><i class="ti ti-users"></i> طالباتي</a>
          <a href="/Mateen/html/onboarding.html" style="margin-top:8px;border:1px solid var(--gold);border-radius:10px;justify-content:center;color:var(--green-dark) !important;"><i class="ti ti-sparkles"></i> دليل البرنامج</a>

          <div id="notifBtnWrap" class="d-none" style="padding:8px 8px 0"></div>

          <button onclick="doLogout()" style="width:100%;margin-top:10px;padding:10px;border:1.5px solid #c0392b;background:rgba(192,57,43,0.07);color:#c0392b;border-radius:10px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i class="ti ti-logout"></i> تسجيل الخروج
          </button>

          <button id="sidebarDeleteAccBtn" onclick="requestAccountDeletion()" style="width:100%;margin-top:8px;padding:10px;border:1.5px solid var(--border);background:transparent;color:var(--text-mid);border-radius:10px;font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i class="ti ti-trash"></i> طلب حذف الحساب
          </button>
        </nav>
        <hr class="sidebar-divider mx-3 my-1"/>
      </div>

      <div class="sidebar-footer mt-auto">
        <img alt="متين" src="/Mateen/logo.png" class="sidebar-foot-img"/>
        <span>متين العلمي</span>
      </div>



    </div><!-- /offcanvas-body -->
  </aside>

  <!-- MAIN CONTENT -->
  <main class="content-area">

    <!-- HERO -->
    <section class="hero position-relative overflow-hidden">

      <!-- Hero background image -->
      <img
        src="/Mateen/hero-bg.png"
        alt="خلفية متين العلمي"
        class="hero-bg-img"
        loading="eager"
      />

      <!-- Overlay layer -->
      <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100"></div>

      <!-- Content -->
      <div class="hero-text">
        <div class="hero-logos">
          <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
            <img alt="متين" class="hero-logo-desktop d-none d-lg-block hero-anim-logo" src="/Mateen/logo.png"/>
            <img alt="متين" class="hero-logo-mobile d-lg-none hero-anim-logo" src="/Mateen/logo.png"/>
            <div class="hero-anim-name" style="font-family:Amiri,serif;font-size:clamp(18px,2.2vw,28px);color:var(--text-dark);font-weight:700;white-space:nowrap;text-shadow:0 1px 6px rgba(247,239,227,0.95);">برنامج متين العلمي</div>
          </div>
        </div>
        <div class="hero-ornament hero-anim-orn">❧ ✦ ❧</div>
        <h1 class="hero-anim-h1">علمٌ يُبنى عليه<br>إيمانٌ يُثمر حياة</h1>
        <p class="hero-anim-p"> منصة متين تقدم لك تعلماً أصيلاً من القرآن والسنة </p>
        <div class="hero-btns d-none hero-anim-btns" id="heroBtns">
          <a class="hero-btn-primary" href="/Mateen/html/courses.html"><i class="ti ti-compass"></i> استكشفي الدورات</a>
          <a class="hero-btn-secondary" href="/Mateen/html/about.html"><i class="ti ti-info-circle"></i> تعرفي على متين</a>
          <a class="hero-btn-secondary" href="/Mateen/html/login.html"><i class="ti ti-login"></i> تسجيل الدخول</a>
          <button class="hero-btn-secondary" onclick="document.getElementById('reg-modal').classList.add('open')"><i class="ti ti-user-plus"></i> التسجيل في البرنامج</button>
        </div>
      </div>

    </section>

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

    <!-- IMPORTANT DATES -->
    <div class="islamic-divider"><span>✦ ❧ ✦</span></div>
    <section class="page-section" id="eventsSection">
      <div class="section-title">📅 المواعيد المهمة</div>
      <div class="timeline" id="homeEventsList" style="max-width:600px;margin:0 auto;">
        <div class="tl-item"><div class="tl-dot"></div><div><div class="tl-label" style="color:#aaa;font-size:13px">جارٍ التحميل...</div></div></div>
      </div>
    </section>

    <!-- ANNOUNCEMENTS -->
    <div class="islamic-divider"><span>✦ ❧ ✦</span></div>
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

    <!-- STATS BAR - مخفية مؤقتاً
    <div class="islamic-divider"><span>✦ ❧ ✦</span></div>
    <section class="stats-bar">
      <div class="row g-0 w-100">
        <div class="col-6 col-md-3">
          <div class="stat-item">
            <div class="stat-icon">🧕‍🎓</div>
            <div class="stat-num">4,256</div>
            <div class="stat-label">Studentات</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-item">
            <div class="stat-icon">🧕‍🏫</div>
            <div class="stat-num">128</div>
            <div class="stat-label">Data/Info</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-item">
            <div class="stat-icon">🎓</div>
            <div class="stat-num">1,890</div>
            <div class="stat-label">الخريجات</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-item">
            <div class="stat-icon">📚</div>
            <div class="stat-num">78</div>
            <div class="stat-label">Roleات المنجزة</div>
          </div>
        </div>
      </div>
    </section>
    -->

    <!-- ABOUT SECTION -->
    <div class="islamic-divider"><span>✦ ❧ ✦</span></div>
    <section class="page-section" style="padding:32px 20px;max-width:900px;margin:0 auto;">
      <div class="section-title">عن برنامج متين</div>
      <div style="display:flex;flex-direction:column;gap:14px;margin-top:20px;">
        <div style="background:white;border-radius:14px;padding:18px 22px;border:1px solid var(--border);display:flex;align-items:center;gap:16px;">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--beige);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">📖</div>
          <div><strong style="color:var(--green-dark);">أولاً:</strong> الربط بكتاب الله حفظاً وتدبراً.</div>
        </div>
        <div style="background:white;border-radius:14px;padding:18px 22px;border:1px solid var(--border);display:flex;align-items:center;gap:16px;">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--beige);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🌙</div>
          <div><strong style="color:var(--green-dark);">ثانياً:</strong> الربط بسُنَّة النبي ﷺ فهماً وتدبراً وإسقاطاً على الواقع.</div>
        </div>
        <div style="background:white;border-radius:14px;padding:18px 22px;border:1px solid var(--border);display:flex;align-items:center;gap:16px;">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--beige);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">⭐</div>
          <div><strong style="color:var(--green-dark);">ثالثاً:</strong> معرفة العقيدة الصحيحة وما ينقضها.</div>
        </div>
        <div style="background:white;border-radius:14px;padding:18px 22px;border:1px solid var(--border);display:flex;align-items:center;gap:16px;">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--beige);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">⚖️</div>
          <div><strong style="color:var(--green-dark);">رابعاً:</strong> دراسة الأحكام الفقهية لمختلف العبادات مع إبراز النواحي الإيمانية فيها.</div>
        </div>
        <div style="background:white;border-radius:14px;padding:18px 22px;border:1px solid var(--border);display:flex;align-items:center;gap:16px;">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--beige);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">💎</div>
          <div><strong style="color:var(--green-dark);">خامساً:</strong> تزويد الطالبة بجملة من الإثرائيات التي تَبني لها المكوّن الإيماني والمعرفي والسلوكي.</div>
        </div>
      </div>
    </section>

  </main>
</div>


<!-- CONTACT SECTION -->

<section class="contact-section" id="contact">
  <div class="container-xl px-4">
    <div class="contact-header text-center mb-5">
      <div class="hero-badge">📬 تواصلي معنا</div>
      <h2>نسعد بتواصلك</h2>
      <p>هل لديكِ سؤال أو استفسار؟ تواصلي معنا وسنرد عليكِ في أقرب وقت</p>
    </div>

    <div class="row g-4">
      <div class="col-12 col-lg-4">

        <div class="contact-info d-flex flex-column gap-3">
         <div class="contact-card">
            <div class="contact-icon"><i class="ti ti-brand-twitter"></i></div>
            <div>
              <div class="contact-label">تويتر (X)</div>
              <a class="contact-val" href="https://x.com/programMateen?t=HENBpRB5qS0lFAyW4d10mg&s=35" target="_blank" rel="noopener">@@programMateen</a>
            </div>
          </div>
         <div class="contact-card">
            <div class="contact-icon"><i class="ti ti-brand-instagram"></i></div>
            <div>
              <div class="contact-label">إنستغرام</div>
              <a class="contact-val" href="https://www.instagram.com/programmateen?igsh=cXZtNGd1c2Voa3p3" target="_blank" rel="noopener">@@programmateen</a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-8">
        <div class="contact-form-wrap">
          <div class="contact-form-title">أرسلي رسالتك</div>
          <div class="row g-3">
            <div class="col-12 col-sm-6">
              <label class="form-label-custom">اسمك الكريم</label>
              <input class="form-input" id="ctName" placeholder="اسمك الكريم" type="text"/>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label-custom">إلى من تودّين الإرسال؟</label>
              <select class="form-input" id="ctRecipient">
                <option value="">جارٍ التحميل...</option>
              </select>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label-custom">موضوع الرسالة</label>
              <select class="form-input" id="ctTopic">
                <option value="">اختاري الموضوع</option>
                <option>سؤال علمي</option>
                <option>استفسار عن التسجيل</option>
                <option>استفسار عن الجدول</option>
                <option>غياب أو عذر</option>
                <option>مشكلة تقنية</option>
                <option>اقتراح</option>
                <option>أخرى</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label-custom">الرسالة</label>
              <textarea class="form-input form-textarea" id="ctBody" placeholder="اكتبي رسالتك هنا..."></textarea>
            </div>
          </div>
          <div id="ctSuccess" class="success-msg mt-3 d-none">
            ✅ تم إرسال رسالتك بنجاح!
          </div>
          <button class="contact-submit mt-3" id="ctBtn" onclick="submitContactNew()">
            <i class="ti ti-send"></i> إرسال الرسالة
          </button>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- AYA BANNER -->

<div class="aya-banner">
  <span class="aya-text">﴿ وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ﴾</span>
  <span class="aya-ref">سورة المائدة: ٢</span>
</div>


@endsection

@push('scripts')
<script>
document.documentElement.classList.add('ready');
</script>
<script src="{{ mateen_asset('libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ mateen_asset('js/home.js') }}?v=20260805" type="module"></script>
<script src="{{ mateen_asset('js/home-msg.js') }}?v=20260805" type="module"></script>
<script src="{{ mateen_asset('js/sw-register.js') }}?v=20260805"></script>
<script src="{{ mateen_asset('js/tour.js') }}?v=20260805"></script>
@endpush