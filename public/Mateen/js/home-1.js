
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { initNotifications, initAdminNotifications } from "./notifications.js";
import { effectiveRole, mountTestModeSwitcher } from "./test-mode.js";
import {
  isLoggedInLocal,
  logoutApp,
  onAppSession,
  useLaravelBackend,
} from "./session.js";

function applySidebarGuest() {
  const guest   = document.getElementById('sidebar-guest');
  const userDiv = document.getElementById('sidebar-user');
  const layout  = document.querySelector('.page-layout');
  const loading = document.getElementById('sidebar-loading');
  if (loading) loading.style.display = 'none';
  if (guest) guest.style.display = 'block';
  if (userDiv) userDiv.classList.add('sidebar-user-hidden');
  if (layout) layout.classList.add('guest-layout');
  const heroBtnsGuest = document.getElementById('heroBtns');
  if (heroBtnsGuest) { heroBtnsGuest.classList.remove('d-none'); heroBtnsGuest.classList.add('d-flex'); }
}

function applySidebarUser(session) {
  const guest   = document.getElementById('sidebar-guest');
  const userDiv = document.getElementById('sidebar-user');
  const layout  = document.querySelector('.page-layout');
  const loading = document.getElementById('sidebar-loading');
  if (loading) loading.style.display = 'none';
  if (layout) layout.classList.remove('guest-layout');

  const heroBtns = document.getElementById('heroBtns');
  if (heroBtns) { heroBtns.classList.remove('d-flex','d-lg-flex'); heroBtns.classList.add('d-none'); }
  const navBtns = document.getElementById('navBtns');
  if (navBtns) { navBtns.classList.remove('d-flex','d-lg-flex'); navBtns.classList.add('d-none'); }
  const mobNavBtns = document.getElementById('mobNavBtns');
  if (mobNavBtns) { mobNavBtns.classList.remove('d-flex','d-lg-flex'); mobNavBtns.classList.add('d-none'); }
  const navUserActions = document.getElementById('navUserActions');
  if (navUserActions) { navUserActions.classList.remove('d-none'); navUserActions.classList.add('d-flex'); }
  const navMsgBtn = document.getElementById('navMsgBtn');
  if (navMsgBtn) navMsgBtn.classList.remove('d-none');

  if (guest) guest.classList.add('d-none');
  if (userDiv) { userDiv.classList.remove('sidebar-user-hidden'); userDiv.classList.add('show-user'); }

  const role = session.role || 'student';
  const status = session.status || 'active';
  const subject = session.subject || '';
  const name = session.name || session.email?.split('@')[0] || 'مستخدم';

  initNotifications(String(session.uid || session.id));

  const sidebarName = document.getElementById('sidebarName');
  const sidebarRole = document.getElementById('sidebarRole');
  if (sidebarName) sidebarName.textContent = 'مرحباً، ' + name;
  if (sidebarRole) sidebarRole.textContent =
    role === 'admin'      ? 'إدارية' :
    role === 'supervisor' ? 'مشرفة' :
    role === 'teacher'    ? 'معلمة' :
    role === 'mateen'     ? 'بنات متين' :
    role === 'support'    ? 'الدعم الفني' : 'الطالبة';

  function show(id) { const el = document.getElementById(id); if (el) el.classList.remove('d-none'); }
  function hide(id)  { const el = document.getElementById(id); if (el) el.classList.add('d-none'); }

  if (role !== 'mateen') {
    hide('profileLink');
    hide('linkCerts');
    hide('linkAwards');
    hide('linkGrades');
    hide('linkSchedule');
  }
  if (role === 'admin' || role === 'supervisor') hide('linkTeacher');

  if (role === 'admin') {
    show('linkAdmin');
    show('linkNews');
  } else if (role === 'supervisor') {
    const linkAdminEl = document.getElementById('linkAdmin');
    if (linkAdminEl) {
      linkAdminEl.href = 'supervisor.html';
      linkAdminEl.innerHTML = '<i class="ti ti-shield"></i> لوحة المشرفة';
    }
    show('linkAdmin');
    show('linkNews');
  } else if (role === 'support') {
    const linkAdminEl = document.getElementById('linkAdmin');
    if (linkAdminEl) {
      linkAdminEl.href = 'support.html';
      linkAdminEl.innerHTML = '<i class="ti ti-headset"></i> لوحة الدعم';
    }
    show('linkAdmin');
    show('linkNews');
  } else if (role === 'teacher') {
    show('linkNews');
    show('linkTeacher');
  } else if (role === 'mateen') {
    show('linkCerts');
    show('linkAwards');
    show('linkGrades');
    show('linkSchedule');
    show('linkNews');
  }

  const profileLink   = document.getElementById('profileLink');
  const navProfileBtn = document.getElementById('navProfileBtn');

  if (status !== 'active') {
    if (profileLink)   profileLink.classList.add('d-none');
    if (navProfileBtn) navProfileBtn.classList.add('d-none');
  } else {
    const navAvatar = document.getElementById('navProfileAvatar');
    const avatarEmoji =
      role === 'admin'      ? '👑' :
      role === 'supervisor' ? '🎓' :
      role === 'teacher'    ? '📚' :
      role === 'mateen'     ? '🧕' :
      role === 'support'    ? '🛠️' : '🌸';
    if (navAvatar) navAvatar.textContent = avatarEmoji;

    if (role === 'mateen') {
      const linkedId = session.linkedStudentId;
      if (linkedId) {
        if (profileLink)   { profileLink.href = `student.html?id=${linkedId}`; profileLink.classList.remove('d-none'); }
        if (navProfileBtn) { navProfileBtn.href = `student.html?id=${linkedId}`; navProfileBtn.classList.remove('d-none'); }
      } else if (navProfileBtn) {
        navProfileBtn.classList.remove('d-none');
      }
    } else if (role === 'admin') {
      if (navProfileBtn) { navProfileBtn.href = 'admin.html'; navProfileBtn.classList.remove('d-none'); }
    } else if (role === 'supervisor') {
      if (navProfileBtn) { navProfileBtn.href = 'supervisor.html'; navProfileBtn.classList.remove('d-none'); }
    } else if (role === 'teacher') {
      const teacherPageMap = {
        'tafseer':'teacher-tafseer.html','fiqh':'teacher-fiqh.html',
        'aqeedah':'teacher-aqeedah.html','hadith':'teacher-hadeeth.html','hadeeth':'teacher-hadeeth.html',
        'quran':'teacher-quran1.html','quran1':'teacher-quran1.html','quran2':'teacher-quran2.html',
        'tafsir':'teacher-tafseer.html','maqraah':'teacher-quran1.html',
      };
      const teacherPage = teacherPageMap[subject] || 'teacher-profile.html';
      if (navProfileBtn) { navProfileBtn.href = teacherPage; navProfileBtn.classList.remove('d-none'); }
    } else if (navProfileBtn) {
      navProfileBtn.classList.remove('d-none');
    }
  }

  try {
    mountTestModeSwitcher(session.raw || session, session.email);
  } catch (_) {}
  if (role === 'admin' || role === 'supervisor') {
    try { initAdminNotifications(String(session.uid || session.id), role); } catch (_) {}
  }
}

async function bootLaravel() {
  await onAppSession((session) => {
    if (!session) {
      applySidebarGuest();
      return;
    }
    applySidebarUser(session);
  });
}

async function bootFirebaseLegacy() {
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const { getAuth, onAuthStateChanged, signOut } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  const { getFirestore, doc, getDoc } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const app  = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  const auth = getAuth(app);
  const db   = getFirestore(app);

  onAuthStateChanged(auth, async user => {
    if (!user) {
      applySidebarGuest();
      return;
    }
    const snap = await getDoc(doc(db, 'users', user.uid));
    const userData = snap.exists() ? snap.data() : {};
    const role = snap.exists() ? effectiveRole(userData, user.email) : 'student';
    applySidebarUser({
      id: user.uid,
      uid: user.uid,
      email: user.email,
      name: user.displayName || user.email?.split('@')[0],
      role,
      status: userData.status || 'active',
      subject: userData.subject || '',
      linkedStudentId: userData.linkedStudentId || null,
      raw: userData,
    });
  });

  window.doLogout = () =>
    signOut(auth).then(() => { window.location.href = '../html/login.html'; });
}

if (useLaravelBackend()) {
  bootLaravel();
  window.doLogout = () => logoutApp('../html/login.html');
} else {
  bootFirebaseLegacy();
}

// silence unused when tree-shaken
void USE_LARAVEL_API;
void isLoggedInLocal;
