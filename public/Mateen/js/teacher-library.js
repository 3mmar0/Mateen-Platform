import { USE_LARAVEL_API } from "./config.js";
import { api, isLaravelApi } from "./api.js";
import { logoutApp, resolveLaravelSession } from "./session.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

let teacherSubject = '';
let allResources = [];
let currentFilter = 'all';
let db = null;
let auth = null;
let collection, doc, getDoc, getDocs, addDoc, deleteDoc, query, orderBy, serverTimestamp;

const RES_ICONS = { pdf: '📄', link: '🔗', note: '📝' };
const RES_LABELS = { pdf: 'PDF', link: 'رابط', note: 'ملاحظة' };

async function showLibrary(session) {
  const data = session.raw || session;
  const role = data.role || session.role || '';
  const status = session.status || data.status || '';
  if (role !== 'teacher' && role !== 'admin' && role !== 'supervisor') {
    window.location.href = '../html/home.html';
    return;
  }
  if (status === 'pending' || status === 'suspended') {
    window.location.href = '../html/home.html';
    return;
  }
  teacherSubject = data.subject || session.subject || String(session.id);
  document.getElementById('teacherName').textContent = session.name || session.email;
  document.getElementById('authGate').style.display = 'none';
  document.getElementById('mainContent').style.display = 'block';
  loadResources();
}

async function bootLaravel() {
  const session = await resolveLaravelSession();
  if (!session) { window.location.href = '../html/login.html'; return; }
  await showLibrary(session);
  window.doLogout = () => logoutApp('../html/login.html');
}

async function bootFirebase() {
  const { initializeApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const firestore = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const { getAuth, onAuthStateChanged, signOut } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  ({ collection, doc, getDoc, getDocs, addDoc, deleteDoc, query, orderBy, serverTimestamp } = firestore);
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
      window.location.href = '../html/home.html';
      return;
    }
    if (status === 'pending' || status === 'suspended') {
      window.location.href = '../html/home.html';
      return;
    }
    teacherSubject = data.subject || user.uid;
    document.getElementById('teacherName').textContent = data.name || user.email;
    document.getElementById('authGate').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    loadResources();
  });
  window.doLogout = () => signOut(auth).then(() => { window.location.href = '../html/login.html'; });
}

if (useApi()) bootLaravel();
else bootFirebase();

async function loadResources() {
  const el = document.getElementById('resourcesList');
  try {
    if (useApi()) {
      const res = await api.library.list();
      const items = Array.isArray(res?.data) ? res.data : [];
      allResources = items.map(i => ({
        id: String(i.id),
        title: i.title,
        type: i.type || i.media_type || 'link',
        content: i.url || i.body || i.content || '',
      }));
    } else {
      const q = query(collection(db, 'teachers', teacherSubject, 'library'), orderBy('createdAt', 'desc'));
      const snap = await getDocs(q);
      allResources = [];
      snap.forEach(d => allResources.push({ id: d.id, ...d.data() }));
    }
    renderResources();
  } catch (e) {
    el.innerHTML = '<div class="empty-state">حدث خطأ أثناء التحميل</div>';
  }
}

function renderResources() {
  const el = document.getElementById('resourcesList');
  const list = currentFilter === 'all' ? allResources : allResources.filter(r => r.type === currentFilter);

  if (!list.length) {
    el.innerHTML = '<div class="empty-state"><i class="ti ti-books-off"></i><span>لا توجد مراجع بعد</span></div>';
    return;
  }

  el.innerHTML = list.map(r => `
    <div class="res-row">
      <div class="res-icon">${RES_ICONS[r.type] || '📄'}</div>
      <div style="flex:1">
        <div class="res-title">${r.title || '—'}</div>
        <div class="res-type">${RES_LABELS[r.type] || r.type}</div>
        ${r.content ? `<div class="res-link">${r.type === 'link' ? `<a href="${r.content}" target="_blank">${r.content}</a>` : r.content}</div>` : ''}
      </div>
      <button class="res-del-btn" onclick="deleteResource('${r.id}')"><i class="ti ti-trash"></i></button>
    </div>
  `).join('');
}

window.filterRes = (btn, type) => {
  document.querySelectorAll('.lib-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentFilter = type;
  renderResources();
};

window.openAddRes = () => {
  document.getElementById('resTitle').value = '';
  document.getElementById('resContent').value = '';
  document.getElementById('resType').value = 'link';
  document.getElementById('resModal').classList.add('open');
};

window.saveResource = async () => {
  const title = document.getElementById('resTitle').value.trim();
  const type = document.getElementById('resType').value;
  const content = document.getElementById('resContent').value.trim();
  if (!title) return;
  try {
    if (useApi()) {
      await api.library.create({ title, section: 'teacher', type, url: type === 'link' ? content : undefined, body: type !== 'link' ? content : undefined });
    } else {
      await addDoc(collection(db, 'teachers', teacherSubject, 'library'), {
        title, type, content, createdAt: serverTimestamp(),
      });
    }
    document.getElementById('resModal').classList.remove('open');
    loadResources();
  } catch (e) {
    alert('حدث خطأ أثناء الحفظ');
  }
};

window.deleteResource = async (id) => {
  if (!confirm('حذف هذا المرجع؟')) return;
  try {
    if (useApi()) {
      await api.library.remove(id);
    } else {
      await deleteDoc(doc(db, 'teachers', teacherSubject, 'library', id));
    }
    loadResources();
  } catch (e) {
    alert('حدث خطأ أثناء الحذف');
  }
};

window.closeResModal = () => document.getElementById('resModal').classList.remove('open');
