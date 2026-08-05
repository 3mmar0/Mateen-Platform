import { USE_LARAVEL_API } from "./config.js";
import { api, isLaravelApi } from "./api.js";
import { logoutApp, resolveLaravelSession } from "./session.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

const SUBJECT_LABELS = {
  aqeedah: 'العقيدة', fiqh: 'الفقه', hadeeth: 'الحديث',
  tafseer: 'التفسير', quran1: 'القرآن (١)', quran2: 'القرآن (٢)',
};
const ROLE_AVATARS = { student: '👧', mateen: '🌸', teacher: '🧕‍🏫', supervisor: '🧕‍💼', admin: '🛡️' };

let allMateenStudents = [];
let db = null;
let auth = null;
let doc, getDoc, collection, query, where, getDocs;

function unwrapList(res) {
  if (Array.isArray(res)) return res;
  if (Array.isArray(res?.data)) return res.data;
  if (Array.isArray(res?.data?.data)) return res.data.data;
  return [];
}

async function showProfile(session) {
  const data = session.raw || session;
  const role = data.role || session.role || '';
  const status = session.status || data.status || '';

  if (role !== 'teacher' && role !== 'admin' && role !== 'supervisor') {
    window.location.href = '/';
    return;
  }
  if (status === 'pending' || status === 'suspended') {
    window.location.href = '/';
    return;
  }

  const subjectAr = SUBJECT_LABELS[data.subject || session.subject] || data.subject || session.subject || '—';
  document.getElementById('teacherName').textContent = session.name || session.email;
  document.getElementById('teacherSubj').textContent = subjectAr;
  document.getElementById('infoName').textContent = session.name || '—';
  document.getElementById('infoEmail').textContent = session.email || '—';
  document.getElementById('infoPhone').textContent = data.phone || '—';
  document.getElementById('infoSubject').textContent = subjectAr;

  const subj = data.subject || session.subject;
  if (subj) {
    document.getElementById('myPageLink').href = `teacher-${subj}.html`;
  }

  document.getElementById('authGate').style.display = 'none';
  document.getElementById('mainContent').style.display = 'block';
  loadMateenStudents();
}

async function bootLaravel() {
  const session = await resolveLaravelSession();
  if (!session) { window.location.href = '../html/login.html'; return; }
  await showProfile(session);
  window.doLogout = () => logoutApp('../html/login.html');
}

async function bootFirebase() {
  const { initializeApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const firestore = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const { getAuth, onAuthStateChanged, signOut } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  ({ doc, getDoc, collection, query, where, getDocs } = firestore);
  const { FIREBASE_CONFIG } = await import("./config.js");
  const app = initializeApp(FIREBASE_CONFIG);
  db = firestore.getFirestore(app);
  auth = getAuth(app);

  onAuthStateChanged(auth, async user => {
    if (!user) { window.location.href = '../html/login.html'; return; }
    const snap = await getDoc(doc(db, 'users', user.uid));
    if (!snap.exists()) { window.location.href = '../html/login.html'; return; }
    const data = snap.data();
    const role = data.role || '';
    const status = data.status || '';
    if (role !== 'teacher' && role !== 'admin' && role !== 'supervisor') {
      window.location.href = '/';
      return;
    }
    if (status === 'pending' || status === 'suspended') {
      window.location.href = '/';
      return;
    }
    const subjectAr = SUBJECT_LABELS[data.subject] || data.subject || '—';
    document.getElementById('teacherName').textContent = data.name || user.email;
    document.getElementById('teacherSubj').textContent = subjectAr;
    document.getElementById('infoName').textContent = data.name || '—';
    document.getElementById('infoEmail').textContent = user.email || '—';
    document.getElementById('infoPhone').textContent = data.phone || '—';
    document.getElementById('infoSubject').textContent = subjectAr;
    if (data.subject) {
      document.getElementById('myPageLink').href = `teacher-${data.subject}.html`;
    }
    document.getElementById('authGate').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    loadMateenStudents();
  });
  window.doLogout = () => signOut(auth).then(() => { window.location.href = '../html/login.html'; });
}

if (useApi()) bootLaravel();
else bootFirebase();

async function loadMateenStudents() {
  try {
    if (useApi()) {
      const res = await api.students.list('?per_page=500');
      allMateenStudents = unwrapList(res)
        .filter(u => u.role === 'mateen' || u.role === 'student')
        .map(u => ({ id: String(u.id), name: u.name, email: u.email, role: u.role || 'mateen' }));
    } else {
      const q = query(collection(db, 'users'), where('role', '==', 'mateen'));
      const snap = await getDocs(q);
      allMateenStudents = [];
      snap.forEach(d => allMateenStudents.push({ id: d.id, ...d.data() }));
    }
    allMateenStudents.sort((a, b) => (a.name || '').localeCompare(b.name || '', 'ar'));
    document.getElementById('mateenCount').textContent = allMateenStudents.length;
    renderMateenList(allMateenStudents);
  } catch (e) {
    document.getElementById('mateenList').innerHTML = '<div class="empty-state">حدث خطأ أثناء التحميل</div>';
  }
}

function renderMateenList(list) {
  const el = document.getElementById('mateenList');
  if (!list.length) {
    el.innerHTML = '<div class="empty-state"><i class="ti ti-users-off"></i><span>لا توجد طالبات مسجلات بعد</span></div>';
    return;
  }
  el.innerHTML = list.map(u => `
    <div class="mateen-row">
      <div class="mateen-avatar">${ROLE_AVATARS[u.role] || '🌸'}</div>
      <div class="mateen-info">
        <div class="mateen-name">${u.name || '—'}</div>
        <div class="mateen-email">${u.email || ''}</div>
      </div>
    </div>`).join('');
}

window.filterMateen = () => {
  const q = (document.getElementById('mateenSearch')?.value || '').toLowerCase();
  const filtered = q ? allMateenStudents.filter(u => (u.name || '').toLowerCase().includes(q)) : allMateenStudents;
  renderMateenList(filtered);
};
