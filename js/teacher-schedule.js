import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { api, getStoredUser, getToken, isLaravelApi, clearSession } from "./api.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

function unwrapList(res) {
  if (Array.isArray(res)) return res;
  if (Array.isArray(res?.data)) return res.data;
  return [];
}

const TEACHER_SLUG_MAP = {
  tafseer: 'tafsir', fiqh: 'fiqh', aqeedah: 'aqeedah', hadeeth: 'hadeeth', hadith: 'hadeeth',
  quran1: 'maqraah', quran2: 'maqraah', quran: 'maqraah', ithraiyat: 'ithraiyat',
};

const DAYS_ORDER = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
const DAY_ICONS  = { 'الأحد':'🌅','الاثنين':'📖','الثلاثاء':'✏️','الأربعاء':'📚','الخميس':'🌿','الجمعة':'🕌','السبت':'⭐' };

let db = null, auth = null;
let collection, doc, getDoc, getDocs, addDoc, deleteDoc, query, orderBy, serverTimestamp, onAuthStateChanged, signOut;

async function ensureFirebase() {
  if (useApi()) throw new Error('Firebase data disabled in Laravel mode');
  if (db) return { db, auth };
  const { initializeApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const firestore = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const firebaseAuth = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  ({ collection, doc, getDoc, getDocs, addDoc, deleteDoc, query, orderBy, serverTimestamp } = firestore);
  ({ onAuthStateChanged, signOut } = firebaseAuth);
  const app = initializeApp(FIREBASE_CONFIG);
  auth = firebaseAuth.getAuth(app);
  db = firestore.getFirestore(app);
  return { db, auth };
}

let apiSubjectId = null;

async function resolveSubjectId(teacherKey) {
  if (apiSubjectId) return apiSubjectId;
  const slug = TEACHER_SLUG_MAP[teacherKey] || teacherKey;
  const subjects = unwrapList(await api.subjects.list());
  const hit = subjects.find(s => s.slug === slug || String(s.id) === String(teacherKey));
  apiSubjectId = hit?.id ?? null;
  return apiSubjectId;
}

function mapApiSchedule(entry) {
  const day = DAYS_ORDER[entry.weekday] || '';
  const d = entry.starts_at ? new Date(entry.starts_at) : new Date();
  const time = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
  return { id: String(entry.id), day, time, note: entry.title || '' };
}

let currentUser = null;
let teacherSubject = '';

async function initTeacherScheduleApi() {
  if (!getToken()) { window.location.href = '../html/login.html'; return; }
  let data;
  try {
    const me = await api.me();
    data = (me?.data ?? me ?? getStoredUser()) || {};
  } catch {
    window.location.href = '../html/login.html'; return;
  }
  const role = data.role || '';
  if (role !== 'teacher' && role !== 'admin' && role !== 'supervisor') {
    window.location.href = '../html/home.html'; return;
  }
  if (data.status === 'pending' || data.status === 'suspended') {
    window.location.href = '../html/home.html'; return;
  }
  teacherSubject = data.subject_id != null ? String(data.subject_id) : (data.subject || String(data.id));
  apiSubjectId = null;
  document.getElementById('teacherName').textContent = data.name || data.email || '';
  document.getElementById('authGate').style.display = 'none';
  document.getElementById('mainContent').style.display = 'block';
  loadSchedule();
}

async function bootTeacherScheduleFirebase() {
  await ensureFirebase();
  onAuthStateChanged(auth, async user => {
    if (!user) { window.location.href = '../html/login.html'; return; }

    const snap = await getDoc(doc(db, 'users', user.uid));
    if (!snap.exists()) { window.location.href = '../html/login.html'; return; }

    const data   = snap.data();
    const role   = data.role   || '';
    const status = data.status || '';

    if (role !== 'teacher' && role !== 'admin' && role !== 'supervisor') {
      window.location.href = '../html/home.html'; return;
    }
    if (status === 'pending' || status === 'suspended') {
      window.location.href = '../html/home.html'; return;
    }

    currentUser    = user;
    teacherSubject = data.subject || user.uid;
    apiSubjectId   = null;

    document.getElementById('teacherName').textContent = data.name || user.email;
    document.getElementById('authGate').style.display   = 'none';
    document.getElementById('mainContent').style.display = 'block';

    loadSchedule();
  });
}

if (useApi()) {
  initTeacherScheduleApi();
} else {
  bootTeacherScheduleFirebase();
}

window.doLogout = () => {
  if (useApi()) {
    clearSession();
    window.location.href = '../html/login.html';
  } else {
    signOut(auth).then(() => window.location.href = '../html/login.html');
  }
};

async function loadSchedule() {
  const container = document.getElementById('scheduleContainer');
  try {
    let slots = [];
    if (useApi()) {
      const subjectId = await resolveSubjectId(teacherSubject);
      slots = unwrapList(await api.schedules.list())
        .filter(s => !subjectId || s.subject_id === subjectId)
        .map(mapApiSchedule);
    } else {
      await ensureFirebase();
      const q = query(collection(db, 'teachers', teacherSubject, 'schedule'), orderBy('createdAt'));
      const snap = await getDocs(q);
      snap.forEach(d => slots.push({ id: d.id, ...d.data() }));
    }

    if (!slots.length) {
      container.innerHTML = '<div class="empty-state"><i class="ti ti-calendar-off"></i><span>لا توجد مواعيد مضافة بعد</span></div>';
      return;
    }

    const byDay = {};
    DAYS_ORDER.forEach(d => byDay[d] = []);
    slots.forEach(s => { if (byDay[s.day]) byDay[s.day].push(s); });
    const activeDays = DAYS_ORDER.filter(d => byDay[d].length > 0);

    let html = '<div class="schedule-week">';
    activeDays.forEach(day => {
      const daySlots = byDay[day].sort((a,b) => (a.time||'').localeCompare(b.time||''));
      html += '<div class="sched-day-group">';
      html += '<div class="sched-day-header">';
      html += '<span class="sched-day-icon">' + (DAY_ICONS[day]||'📅') + '</span>';
      html += '<span class="sched-day-name">' + day + '</span>';
      html += '<span class="sched-day-count">' + daySlots.length + ' موعد</span>';
      html += '</div>';
      html += '<div class="sched-slots">';
      daySlots.forEach(s => {
        html += '<div class="sched-slot">';
        html += '<span class="sched-time-pill"><i class="ti ti-clock"></i> ' + (s.time||'--:--') + '</span>';
        if (s.note) {
          html += '<span class="sched-note-text">' + s.note + '</span>';
        } else {
          html += '<span class="sched-note-text" style="color:var(--text-light)">—</span>';
        }
        html += '<button class="sched-del-btn" onclick="deleteSlot(\'' + s.id + '\')"><i class="ti ti-trash"></i></button>';
        html += '</div>';
      });
      html += '</div></div>';
    });
    html += '</div>';
    container.innerHTML = html;

  } catch(e) {
    container.innerHTML = '<div class="empty-state"><i class="ti ti-alert-circle"></i><span>حدث خطأ أثناء التحميل</span></div>';
  }
}

window.showAddSlot = () => {
  document.getElementById('addSlotForm').style.display = 'block';
  document.getElementById('addSlotForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
};

window.saveSlot = async () => {
  const day  = document.getElementById('slotDay').value;
  const time = document.getElementById('slotTime').value;
  const note = document.getElementById('slotNote').value.trim();

  if (!time) { alert('يرجى تحديد الوقت'); return; }

  try {
    if (useApi()) {
      const weekday = DAYS_ORDER.indexOf(day);
      const [h, m] = time.split(':');
      const starts = new Date();
      starts.setHours(parseInt(h, 10), parseInt(m, 10), 0, 0);
      await api.schedules.create({
        subject_id: await resolveSubjectId(teacherSubject),
        title: note || 'موعد',
        weekday: weekday >= 0 ? weekday : null,
        starts_at: starts.toISOString(),
      });
    } else {
      await ensureFirebase();
      await addDoc(collection(db, 'teachers', teacherSubject, 'schedule'), {
        day, time, note, createdAt: serverTimestamp()
      });
    }
    document.getElementById('addSlotForm').style.display = 'none';
    document.getElementById('slotTime').value = '';
    document.getElementById('slotNote').value = '';
    loadSchedule();
  } catch(e) {
    alert('حدث خطأ أثناء الحفظ');
  }
};

window.deleteSlot = async id => {
  if (!confirm('حذف هذا الموعد؟')) return;
  if (useApi()) {
    await api.schedules.remove(id);
  } else {
    await ensureFirebase();
    await deleteDoc(doc(db, 'teachers', teacherSubject, 'schedule', id));
  }
  loadSchedule();
};
