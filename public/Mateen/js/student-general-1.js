import { USE_LARAVEL_API } from "./config.js";
import { isLaravelApi } from "./api.js";
import { logoutApp, resolveLaravelSession } from "./session.js";
import { effectiveRole, mountTestModeSwitcher } from "./test-mode.js";
import { applyCustomTheme } from "./custom-theme.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

async function showStudentHome(session) {
  const role = effectiveRole(session.raw || session, session.email);
  if (role !== 'student') {
    window.location.href = '../html/login.html';
    return;
  }
  mountTestModeSwitcher(session.raw || session, session.email);
  applyCustomTheme(session.raw || session);
  const name = session.name || session.email?.split('@')[0] || 'مستخدم';
  document.getElementById('navUserName').textContent = name;
  document.getElementById('heroName').textContent = `أهلاً بكِ، ${name}`;
  document.getElementById('authGate').style.display = 'none';
  document.getElementById('mainContent').style.display = 'flex';
}

async function bootLaravel() {
  const session = await resolveLaravelSession();
  if (!session) {
    window.location.href = '../html/login.html';
    return;
  }
  await showStudentHome(session);
}

async function bootFirebase() {
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const { getAuth, onAuthStateChanged, signOut } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  const { getFirestore, doc, getDoc } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const { FIREBASE_CONFIG } = await import("./config.js");

  const app = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  const auth = getAuth(app);
  const db = getFirestore(app);

  onAuthStateChanged(auth, async user => {
    if (!user) { window.location.href = '../html/login.html'; return; }
    const snap = await getDoc(doc(db, 'users', user.uid));
    const data = snap.exists() ? snap.data() : {};
    const role = effectiveRole(data, user.email);
    if (role !== 'student') { window.location.href = '../html/login.html'; return; }
    mountTestModeSwitcher(data, user.email);
    applyCustomTheme(data);
    const name = data.name || user.email.split('@')[0];
    document.getElementById('navUserName').textContent = name;
    document.getElementById('heroName').textContent = `أهلاً بكِ، ${name}`;
    document.getElementById('authGate').style.display = 'none';
    document.getElementById('mainContent').style.display = 'flex';
  });

  window.doLogout = () => signOut(auth).then(() => { window.location.href = '../html/login.html'; });
}

if (useApi()) {
  bootLaravel();
  window.doLogout = () => logoutApp('../html/login.html');
} else {
  bootFirebase();
}
