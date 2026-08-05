// nav.js — loads Navigation bar across all pages
// Laravel mode: session from mateen_api_token; Firebase only as legacy fallback.

function renderNav(activePage) {
  const links = [
    { href: '/',    label: 'الرئيسية' },
    { href: '/about',   label: 'عن البرنامج' },
    { href: '/courses', label: 'المسارات العلمية' },
    { href: '/library', label: 'المكتبة' },
    { href: '/news',    label: 'الأخبار' },
    { href: '/#contact', label: 'تواصل معنا' },
  ];

  const isLoggedIn = _navIsLoggedIn();

  const navHTML = `
<nav>
  <a href="/" class="nav-logo" style="text-decoration:none">
    <img src="/Mateen/logo.png" alt="متين" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:1.5px solid var(--gold);background:#fff;">
    <div>
      <div class="nav-brand">برنامج متين العلمي</div>
      <div class="nav-tagline">نحو بناء علميٍّ متين</div>
    </div>
  </a>
  <div class="nav-shuraka"><img src="/Mateen/shuraka-logo.png" alt="شركاء الخير" class="nav-shuraka-img"/></div>
  <ul class="nav-links">
    ${links.map(l => `<li><a href="${l.href}"${activePage === l.href ? ' class="active"' : ''}>${l.label}</a></li>`).join('\n    ')}
  </ul>
  <div class="nav-btns"${isLoggedIn ? ' style="display:none"' : ''} id="navBtnsRendered">
    <a href="/login" class="btn-admin"><i class="ti ti-dashboard"></i> لوحة الإدارة</a>
    <a href="/login" class="btn-outline"><i class="ti ti-user"></i> تسجيل الدخول</a>
    <button class="btn-solid" onclick="document.getElementById('reg-modal')?.classList.add('open')">التسجيل في البرنامج</button>
  </div>
  <button onclick="typeof startPageTour==='function'&&startPageTour()" title="جولة تعريفية"
    id="navTourBtn"
    style="background:none;border:none;color:rgba(255,255,255,0.85);font-size:18px;cursor:pointer;padding:6px 8px;display:flex;align-items:center;flex-shrink:0;">❓</button>
  <button class="nav-toggle" aria-label="Open sidebar menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
    <i class="ti ti-menu-2"></i>
  </button>
</nav>`;

  const placeholder = document.getElementById('nav-placeholder');
  if (placeholder) {
    placeholder.outerHTML = navHTML;
  }
  setTimeout(() => {
    const tourBtn = document.getElementById('navTourBtn');
    if (tourBtn && typeof startPageTour !== 'function') tourBtn.style.display = 'none';
  }, 300);
}

function _navIsLoggedIn() {
  try {
    if (localStorage.getItem('mateen_api_token') && localStorage.getItem('mateen_api_user')) {
      return true;
    }
  } catch (_) {}
  try {
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && key.startsWith('firebase:authUser:')) {
        const val = localStorage.getItem(key);
        if (val && val !== 'null') return true;
      }
    }
  } catch (_) {}
  return false;
}

async function _addAdminBtn() {
  const page = location.pathname.split('/').pop() || '/';
  if (page !== '/' && page !== '') return;
  try {
    const { USE_LARAVEL_API } = await import('./config.js');
    if (USE_LARAVEL_API === true) {
      const raw = JSON.parse(localStorage.getItem('mateen_api_user') || 'null');
      const role = raw?.role || raw?.data?.role;
      if (role !== 'admin') return;
      const navBtns = document.querySelector('.nav-btns');
      if (!navBtns) return;
      const btn = document.createElement('a');
      btn.href = '/admin';
      btn.className = 'btn-admin';
      btn.innerHTML = '<i class="ti ti-dashboard"></i> لوحة الإدارة';
      navBtns.prepend(btn);
      return;
    }

    const keys = Object.keys(localStorage).filter(k => k.startsWith('firebase:authUser:'));
    if (!keys.length) return;
    const userData = JSON.parse(localStorage.getItem(keys[0]));
    if (!userData?.uid) return;
    const { initializeApp, getApps } = await import('https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js');
    const { getFirestore, doc, getDoc } = await import('https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js');
    const { FIREBASE_CONFIG } = await import('./config.js');
    const app = getApps().length ? getApps()[0] : initializeApp(FIREBASE_CONFIG);
    const db = getFirestore(app);
    const snap = await getDoc(doc(db, 'users', userData.uid));
    if (!snap.exists() || snap.data().role !== 'admin') return;
    const navBtns = document.querySelector('.nav-btns');
    if (!navBtns) return;
    const btn = document.createElement('a');
    btn.href = '/admin';
    btn.className = 'btn-admin';
    btn.innerHTML = '<i class="ti ti-dashboard"></i> لوحة الإدارة';
    navBtns.prepend(btn);
  } catch (_) {}
}

addEventListener('DOMContentLoaded', function() {
  if (document.getElementById('nav-placeholder')) {
    const page = (location.pathname.replace(/\/$/, '') || '/');
    renderNav(page);
    _addAdminBtn();
  }
});
