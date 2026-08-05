<!-- NAVBAR -->
<nav class="main-nav">
  <div class="nav-inner">
    <div class="nav-logo" onclick="triggerInstall()" style="cursor:pointer;" title="اضغطي لتثبيت التطبيق">
      <div class="logo-circle" id="logoCircle">
        <img alt="متين" src="/Mateen/logo.png"/>
      </div>
      <div>
        <div class="nav-brand">برنامج متين العلمي</div>
        <div class="nav-tagline d-none d-md-block">نحو بناء علميٍّ متين</div>
      </div>
    </div>



    <ul class="nav-links d-none d-lg-flex">
      <li><a class="active" href="/">الرئيسية</a></li>
      <li><a href="/about">عن البرنامج</a></li>
      <li><a href="/courses">المواد العلمية</a></li>
      <li><a href="/library">المكتبة</a></li>
      <li class="d-none d-lg-none"><a href="/news">الأخبار <span class="nav-msg-badge nav-msg-badge-orange d-none" id="navNewsBadge2">0</span></a></li>
      <li><a href="#contact">تواصل معنا</a></li>
    </ul>

    <div class="nav-btns d-none d-lg-flex" id="navBtns">
      <a class="btn-outline" href="/login"><i class="ti ti-user"></i> تسجيل الدخول</a>
      <button class="btn-solid" onclick="document.getElementById('reg-modal').classList.add('open')">التسجيل في البرنامج</button>
    </div>

    <!-- Messagesي + الNews + الProfile -->
    <div class="nav-user-actions d-none" id="navUserActions">
      <a class="nav-msg-btn" id="navMsgBtn" href="/messages" aria-label="رسائلي">
        <i class="ti ti-message-2"></i>
        <span class="d-none d-lg-inline">رسائلي</span>
        <span class="nav-msg-badge d-none" id="navMsgBadge">0</span>
      </a>
      <a class="nav-msg-btn" href="/news" aria-label="الأخبار" id="navNewsBtn">
        <i class="ti ti-speakerphone"></i>
        <span class="d-none d-lg-inline">الأخبار</span>
        <span class="nav-msg-badge nav-msg-badge-orange d-none" id="navNewsBadge">0</span>
      </a>
      <a class="nav-profile-btn" id="navProfileBtn" href="/student" aria-label="ملفي الشخصي">
        <div class="nav-profile-avatar" id="navProfileAvatar">🧕</div>
      </a>
      <button class="nav-logout-btn" id="navLogoutBtn" onclick="doLogout()" title="تسجيل الخروج">
        <i class="ti ti-logout"></i>
      </button>
    </div>

    <!-- Button تثبيت التطبيق -->
    <button id="installAppBtn" onclick="showInstallChoiceModal()"
      style="display:none;align-items:center;gap:6px;background:var(--gold);color:#2c1a0e;
             border:none;padding:6px 12px;border-radius:8px;font-family:inherit;font-size:13px;
             cursor:pointer;font-weight:700;flex-shrink:0;">
      <i class="ti ti-download"></i>
      <span class="d-none d-sm-inline">تثبيت</span>
    </button>

    <button class="sidebar-fab" id="sidebarFab" aria-label="ملفي وحسابي" data-bs-toggle="offcanvas" data-bs-target="#mainSidebar" aria-controls="mainSidebar">
      <i class="ti ti-user-circle"></i>
      <span class="d-none d-lg-inline">حسابي</span>
    </button>

    <button onclick="startPageTour()" title="جولة تعريفية"
      style="background:none;border:none;color:rgba(255,255,255,0.85);font-size:18px;cursor:pointer;
             padding:6px 8px;display:flex;align-items:center;flex-shrink:0;">❓</button>

    <button class="nav-toggle d-lg-none" id="mobileMenuToggle" aria-label="القائمة">
      <i class="ti ti-menu-2"></i>
    </button>
  </div>

  <!-- Mobile Menu -->
  <div class="mobile-menu d-lg-none" id="mobileMenu">
    <ul>
      <li><a href="/"><i class="ti ti-home"></i> الرئيسية</a></li>
      <li><a href="/about"><i class="ti ti-info-circle"></i> عن البرنامج</a></li>
      <li><a href="/courses"><i class="ti ti-books"></i> المواد العلمية</a></li>
      <li><a href="/library"><i class="ti ti-library"></i> المكتبة</a></li>
      <li><a href="/news"><i class="ti ti-speakerphone"></i> الأخبار <span class="nav-msg-badge nav-msg-badge-orange d-none" id="sidebarNewsBadge">0</span></a></li>      <li><a href="#contact"><i class="ti ti-headset"></i> تواصل معنا</a></li>
    </ul>
    <div class="mobile-menu-btns" id="mobNavBtns">
      <a class="mob-login-btn" href="/login"><i class="ti ti-login"></i> تسجيل الدخول</a>
      <button class="mob-reg-btn" onclick="document.getElementById('mobileMenu').classList.remove('open');document.getElementById('reg-modal').classList.add('open')">
        <i class="ti ti-user-plus"></i> التسجيل في البرنامج
      </button>
    </div>
  </div>
</nav>
