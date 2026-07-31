import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { api, isLaravelApi } from "./api.js";

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

let db = null, auth = null;
let collection, addDoc, getDocs, deleteDoc, doc, Timestamp, onAuthStateChanged;

async function ensureFirebase() {
  if (useApi()) throw new Error('Firebase data disabled in Laravel mode');
  if (db) return { db, auth };
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const firestore = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const firebaseAuth = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  ({ collection, addDoc, getDocs, deleteDoc, doc, Timestamp } = firestore);
  ({ onAuthStateChanged } = firebaseAuth);
  const app = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  auth = firebaseAuth.getAuth(app);
  db = firestore.getFirestore(app);
  return { db, auth };
}

const scriptEl   = document.currentScript ||
  [...document.querySelectorAll('script')].find(s => s.src.includes('teacher-schedule-shared'));
const TEACHER_ID = scriptEl ? scriptEl.dataset.teacherId : '';

const DAYS_ORDER = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];

let scheduleSlots = [];
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

async function loadSchedule() {
  if (useApi()) {
    const subjectId = await resolveSubjectId(TEACHER_ID);
    scheduleSlots = unwrapList(await api.schedules.list())
      .filter(s => !subjectId || s.subject_id === subjectId)
      .map(mapApiSchedule);
  } else {
    await ensureFirebase();
    const snap = await getDocs(collection(db, 'teachers', TEACHER_ID, 'schedule'));
    scheduleSlots = snap.docs.map(d => ({ id: d.id, ...d.data() }));
  }
  scheduleSlots.sort((a,b) => DAYS_ORDER.indexOf(a.day) - DAYS_ORDER.indexOf(b.day) || (a.time||'').localeCompare(b.time||''));
  renderSchedule();
  updateQuickCards();
}

function renderSchedule() {
  const c = document.getElementById('scheduleContainer');
  if (!c) return;
  if (!scheduleSlots.length) {
    c.innerHTML = '<div class="empty-state"><i class="ti ti-calendar-off"></i>لا توجد مواعيد بعد</div>';
    return;
  }

  const byDay = {};
  scheduleSlots.forEach(s => {
    if (!byDay[s.day]) byDay[s.day] = [];
    byDay[s.day].push(s);
  });

  let html = '';
  DAYS_ORDER.filter(d => byDay[d]).forEach(day => {
    html += `<div class="sched-day-block">
      <div class="sched-day-head"><i class="ti ti-calendar"></i> ${day}</div>
      ${byDay[day].map(s => `
        <div class="sched-slot">
          <div class="sched-slot-time"><i class="ti ti-clock"></i> ${formatTime(s.time)}</div>
          ${s.note ? `<div class="sched-slot-note">${esc(s.note)}</div>` : ''}
          <button class="sched-del-btn" onclick="window.deleteSlot('${s.id}')"><i class="ti ti-trash"></i></button>
        </div>
      `).join('')}
    </div>`;
  });
  c.innerHTML = html;
}

function updateQuickCards() {
  const totalEl = document.getElementById('qTotalSlots');
  const daysEl  = document.getElementById('qTotalDays');
  const nextEl  = document.getElementById('qNextDay');
  if (totalEl) totalEl.textContent = scheduleSlots.length;
  if (daysEl)  daysEl.textContent  = [...new Set(scheduleSlots.map(s => s.day))].length;

  if (nextEl) {
    const todayIdx = new Date().getDay();
    let nearest = null;
    let minDiff  = 8;
    scheduleSlots.forEach(s => {
      const idx = DAYS_ORDER.indexOf(s.day);
      let diff = (idx - todayIdx + 7) % 7;
      if (diff === 0) diff = 0;
      if (diff < minDiff) { minDiff = diff; nearest = s; }
    });
    nextEl.textContent = nearest ? `${nearest.day} ${formatTime(nearest.time)}` : '—';
  }
}

window.showAddSlot = () => {
  const f = document.getElementById('addSlotForm');
  if (f) f.style.display = f.style.display === 'none' ? 'block' : 'none';
};

window.saveSlot = async () => {
  const day  = document.getElementById('slotDay')?.value;
  const time = document.getElementById('slotTime')?.value;
  const note = document.getElementById('slotNote')?.value?.trim() || '';
  if (!day || !time) { alert('اختاري اليوم والوقت'); return; }

  const btn = document.querySelector('.btn-save-slot');
  btn.disabled = true;
  try {
    if (useApi()) {
      const weekday = DAYS_ORDER.indexOf(day);
      const [h, m] = time.split(':');
      const starts = new Date();
      starts.setHours(parseInt(h, 10), parseInt(m, 10), 0, 0);
      await api.schedules.create({
        subject_id: await resolveSubjectId(TEACHER_ID),
        title: note || 'موعد',
        weekday: weekday >= 0 ? weekday : null,
        starts_at: starts.toISOString(),
      });
    } else {
      await ensureFirebase();
      await addDoc(collection(db, 'teachers', TEACHER_ID, 'schedule'), {
        day, time, note, createdAt: Timestamp.now()
      });
    }
    document.getElementById('addSlotForm').style.display = 'none';
    document.getElementById('slotTime').value = '';
    document.getElementById('slotNote').value = '';
    await loadSchedule();
  } catch(e) { alert('خطأ: ' + e.message); }
  finally { btn.disabled = false; }
};

window.deleteSlot = async (id) => {
  if (!confirm('حذف هذا الموعد؟')) return;
  if (useApi()) {
    await api.schedules.remove(id);
  } else {
    await ensureFirebase();
    await deleteDoc(doc(db, 'teachers', TEACHER_ID, 'schedule', id));
  }
  await loadSchedule();
};

function formatTime(t) {
  if (!t) return '—';
  const [h, m] = t.split(':');
  const hr = parseInt(h);
  const ampm = hr >= 12 ? 'م' : 'ص';
  const hr12 = hr % 12 || 12;
  return `${hr12}:${m} ${ampm}`;
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

if (useApi()) {
  if (TEACHER_ID) loadSchedule();
} else {
  (async () => {
    await ensureFirebase();
    onAuthStateChanged(auth, user => {
      if (user && TEACHER_ID) loadSchedule();
    });
  })();
}
