<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<script>
(function(){
  try{
    var t=JSON.parse(localStorage.getItem('mateenCustomTheme')||'null');
    if(!t)return;
    var r=document.documentElement.style;
    if(t.greenDark)r.setProperty('--green-dark',t.greenDark);
    if(t.gold)r.setProperty('--gold',t.gold);
    if(t.beige)r.setProperty('--beige',t.beige);
    var patterns={
      stars:"url(\"data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23000' fill-opacity='0.045'%3E%3Cpath d='M20 15l1.5 4.5H26l-3.6 2.8 1.4 4.5-3.8-2.8-3.8 2.8 1.4-4.5L14 19.5h4.5z'/%3E%3C/g%3E%3C/svg%3E\")",
      geometric:"url(\"data:image/svg+xml,%3Csvg width='44' height='44' viewBox='0 0 44 44' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23000' stroke-opacity='0.05'%3E%3Cpath d='M22 2l20 20-20 20L2 22z'/%3E%3C/g%3E%3C/svg%3E\")",
      circles:"url(\"data:image/svg+xml,%3Csvg width='36' height='36' viewBox='0 0 36 36' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='18' cy='18' r='6' fill='none' stroke='%23000' stroke-opacity='0.05'/%3E%3C/svg%3E\")"
    };
    var bg=patterns[t.pattern]||'';
    if(bg){
      document.addEventListener('DOMContentLoaded',function(){
        document.body.style.backgroundImage=bg;
        document.body.style.backgroundRepeat='repeat';
      });
    }
  }catch(e){}
})();
</script>

<meta charset="utf-8"/>
<link href="/favicon.ico" rel="icon" type="image/x-icon"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>طالباتي — برنامج متين العلمي</title>
<link href="/Mateen/libs/fonts/arabic-fonts.css" rel="stylesheet"/>
<link href="/Mateen/libs/tabler-icons/tabler-icons.min.css" rel="stylesheet"/>
<link href="/Mateen/css/shared.css" rel="stylesheet"/>
<link href="/Mateen/css/home.css" rel="stylesheet"/>
<link href="/Mateen/css/islamic.css" rel="stylesheet"/>
<link href="/Mateen/css/mobile.css" rel="stylesheet"/>
<link href="/Mateen/css/responsive-fix.css" rel="stylesheet"/>
<style>
  .students-wrap {
    max-width: 900px;
    margin: 32px auto;
    padding: 0 20px;
  }
  .page-title {
    font-family: Amiri, serif;
    font-size: 26px;
    color: var(--green-dark);
    margin-bottom: 6px;
  }
  .page-sub {
    font-size: 13px;
    color: var(--text-mid);
    margin-bottom: 28px;
  }
  .subject-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--beige);
    border: 1px solid var(--gold);
    color: var(--green-dark);
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 24px;
  }
  .search-box {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-family: inherit;
    font-size: 14px;
    margin-bottom: 20px;
    box-sizing: border-box;
    background: var(--white);
  }
  .search-box:focus { outline: none; border-color: var(--gold); }
  .students-grid {
    display: none;
    flex-direction: column;
    gap: 10px;
  }
  .students-grid.visible {
    display: flex;
  }
  .student-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
  }
  .student-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: var(--beige);
    border: 2px solid var(--gold);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
  }
  .student-info { flex: 1; min-width: 160px; }
  .student-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
  }
  .student-year {
    font-size: 12px;
    color: var(--text-mid);
    margin-top: 3px;
  }
  .student-status {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 10px;
    margin-top: 5px;
    display: inline-block;
  }
  .status-active  { background: #e8f5e9; color: #2e7d32; }
  .status-pending { background: #fff8e1; color: #f57f17; }
  .student-grades {
    display: flex;
    gap: 12px;
    flex-shrink: 0;
    flex-wrap: wrap;
    align-items: center;
  }
  .exam-grades-wrap {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }
  .exam-grade-pill {
    font-size: 11px;
    color: var(--green-dark);
    background: var(--beige);
    border: 1px solid var(--gold);
    border-radius: 20px;
    padding: 3px 10px;
    white-space: nowrap;
  }
  .student-grades label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-mid);
  }
  .student-grades input {
    width: 56px;
    padding: 5px 6px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: inherit;
    font-size: 12px;
    text-align: center;
  }
  .student-no-link {
    font-size: 11px;
    color: #c9852b;
    flex-shrink: 0;
    max-width: 220px;
  }
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-mid);
  }
  .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; }
  .loading-state {
    text-align: center;
    padding: 60px;
    color: var(--text-mid);
  }
  .spinner {
    width: 36px; height: 36px;
    border: 3px solid var(--border);
    border-top-color: var(--gold);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 12px;
  }
  @@keyframes spin { to { transform: rotate(360deg); } }
  .count-badge {
    background: var(--gold);
    color: white;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 10px;
    margin-right: 8px;
    font-weight: 600;
  }
</style>
<script>
  function revealPage() { document.documentElement.classList.add('ready'); }
  var t = setTimeout(revealPage, 100);
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(function() { clearTimeout(t); revealPage(); });
  } else {
    window.addEventListener('load', revealPage);
  }
</script>
</head>
<body>

<div class="basmala-bar">
  <span class="bsm-ornament">❦</span>بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ<span class="bsm-ornament">❦</span>
</div>

<nav>

  <a class="nav-logo" href="/Mateen/html/home.html">
    <div class="logo-circle"><img alt="متين" src="/Mateen/logo.png" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"/></div>
    <div>
      <div class="nav-brand">برنامج متين العلمي</div>
      <div class="nav-tagline">نحو بناء علميٍّ متين</div>
    </div>
  </a>
  <ul class="nav-links">
    <li><a href="/Mateen/html/home.html">الرئيسية</a></li>
    <li><a href="/Mateen/html/messages.html">رسائلي</a></li>
    <li><a href="/Mateen/html/news.html">الأخبار</a></li>
    <li><a class="active" href="/Mateen/html/my-students.html">طالباتي</a></li>
  </ul>
  <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">
    <i class="ti ti-menu-2"></i>
  </button>
    <button onclick="history.length > 1 ? history.back() : window.location.href='/Mateen/html/home.html'" class="nav-back-btn" aria-label="رجوع">
      <i class="ti ti-arrow-right"></i>
    </button>
</nav>

<div class="students-wrap">
  <div class="page-title"><i class="ti ti-users" style="margin-left:8px;"></i>طالباتي</div>
  <div class="page-sub" id="pageSub">الطالبات المسجلات في مادتك</div>

  <div id="subjectBadge" class="subject-badge" style="display:none;">
    <i class="ti ti-book"></i>
    <span id="subjectName">...</span>
  </div>

  <div id="publishWrap" style="display:none;align-items:center;gap:8px;background:#fff8e1;border:1px solid #f0d78c;border-radius:10px;padding:8px 14px;margin-bottom:14px;font-size:13px">
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin:0">
      <input type="checkbox" id="publishToggle" onchange="togglePublishParticipation()" style="width:16px;height:16px;cursor:pointer">
      <span id="publishLabel" style="color:#5c3d2e;font-weight:600">نشر للطالبات</span>
    </label>
    <span style="color:#8a6a3c;font-size:11.5px">— لما تنشري، درجات المشاركة تظهر للطالبات وتتحسب في التوتال لكل طالبات المادة دي مرة واحدة</span>
  </div>

  <select id="subjectPicker" class="search-box" style="display:none;margin-bottom:20px" onchange="onSubjectPickerChange()">
    <option value="">— اختاري المادة لعرض/تسجيل درجاتها —</option>
  </select>

  <input class="search-box" id="searchInput" placeholder="🔍 ابحثي باسم الطالبة..." type="text" oninput="filterStudents()" style="display:none;">

  <div id="loadingState" class="loading-state">
    <div class="spinner"></div>
    <div>جاري التحميل...</div>
  </div>

  <div class="students-grid" id="studentsGrid"></div>
</div>

<script type="module">
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "/Mateen/js/config.js";
import { api, getToken, getStoredUser, isLaravelApi } from "/Mateen/js/api.js";
import { effectiveRole, mountTestModeSwitcher } from "/Mateen/js/test-mode.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

let db = null, auth = null;
let getDoc, setDoc, doc, serverTimestamp, collection, query, where, getDocs, onAuthStateChanged;

async function ensureFirebase() {
  if (useApi()) throw new Error('Firebase data disabled in Laravel mode');
  if (db) return;
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const firebaseAuth = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  const firestore = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  ({ getDoc, setDoc, doc, serverTimestamp, collection, query, where, getDocs } = firestore);
  ({ onAuthStateChanged } = firebaseAuth);
  const app = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  auth = firebaseAuth.getAuth(app);
  db = firestore.getFirestore(app);
}

let allStudents = [];
const PARTICIPATION_TOTAL = 10;

const SUBJECT_MAP = {
  'tafseer': 'التفسير', tafsir: 'التفسير',
  'fiqh': 'الفقه',
  'aqeedah': 'العقيدة',
  'hadith': 'الحديث', 'hadeeth': 'الحديث',
  'quran': 'مقرأة متين', 'quran1': 'مقرأة متين', 'quran2': 'مقرأة متين', maqraah: 'مقرأة متين',
};

function show(id)  { document.getElementById(id).style.display = ''; }
function hide(id)  { document.getElementById(id).style.display = 'none'; }

function normalizeStuName(name) {
  return (name || '')
    .replace(/[أإآا]/g, 'ا').replace(/[ةه]/g, 'ه').replace(/[يى]/g, 'ي')
    .replace(/[\u064B-\u065F]/g, '')
    .replace(/\s+/g, '')
    .trim();
}

async function getExistingScore(sid, gradeId) {
  if (useApi()) return '';
  try {
    const gSnap = await getDoc(doc(db, 'students', sid, 'grades', gradeId));
    return gSnap.exists() ? gSnap.data().score : '';
  } catch (e) {
    return '';
  }
}
async function getExamGrades(sid, subjectAr) {
  if (useApi()) return [];
  try {
    const snap = await getDocs(query(
      collection(db, 'students', sid, 'grades'),
      where('subject', '==', subjectAr)
    ));
    return snap.docs
      .filter(d => d.id !== 'participation_' + subjectAr && d.data().label !== 'المشاركة')
      .map(d => d.data());
  } catch (e) {
    console.error('getExamGrades:', e);
    return [];
  }
}
window.savePartGrade = async (sid, subject) => {
  if (useApi()) {
    alert('تسجيل درجات المشاركة غير متاح بعد في وضع Laravel');
    return;
  }
  const scoreInput = document.getElementById(`partScore-${sid}-${subject}`);
  if (!scoreInput) return;

  const raw = scoreInput.value.trim();
  if (raw === '') return;

  const score = Math.max(0, Math.min(PARTICIPATION_TOTAL, Number(raw)));
  scoreInput.value = score;

  scoreInput.disabled = true;
  try {
    await setDoc(doc(db, 'students', sid, 'grades', 'participation_' + subject), {
      label: 'المشاركة', subject, score, total: PARTICIPATION_TOTAL,
      updatedAt: serverTimestamp(),
    }, { merge: true });
    scoreInput.style.borderColor = '#2e8b57';
    setTimeout(() => { scoreInput.style.borderColor = ''; }, 1200);
  } catch (e) {
    console.error('savePartGrade:', e);
    alert('حصل خطأ أثناء حفظ الدرجة، حاولي تاني');
  } finally {
    scoreInput.disabled = false;
  }
};

async function getPublishState(subjectAr) {
  if (useApi()) return false;
  try {
    const snap = await getDoc(doc(db, 'subjectSettings', subjectAr));
    return snap.exists() ? !!snap.data().participationPublished : false;
  } catch (e) { return false; }
}

async function loadPublishState(subjectAr) {
  const wrap = document.getElementById('publishWrap');
  if (!subjectAr) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'flex';
  const published = await getPublishState(subjectAr);
  document.getElementById('publishToggle').checked = published;
  document.getElementById('publishLabel').textContent = published ? 'منشورة للطالبات' : 'نشر للطالبات';
}

window.togglePublishParticipation = async () => {
  if (useApi()) {
    alert('نشر درجات المشاركة غير متاح بعد في وضع Laravel');
    document.getElementById('publishToggle').checked = false;
    return;
  }
  const subjectAr = window._subjectAr;
  if (!subjectAr) return;
  const checkbox = document.getElementById('publishToggle');
  const label    = document.getElementById('publishLabel');
  const newState = checkbox.checked;
  checkbox.disabled = true;
  try {
    await setDoc(doc(db, 'subjectSettings', subjectAr), {
      participationPublished: newState,
      updatedAt: serverTimestamp(),
    }, { merge: true });
    label.textContent = newState ? 'منشورة للطالبات' : 'نشر للطالبات';
  } catch (e) {
    console.error('togglePublishParticipation:', e);
    checkbox.checked = !newState;
    alert('حصل خطأ أثناء تحديث حالة النشر، حاولي تاني');
  } finally {
    checkbox.disabled = false;
  }
};

async function studentRowHTML(s, subjectAr) {
  const statusClass = s.status === 'active' ? 'status-active' : 'status-pending';
  const statusText  = s.status === 'active' ? 'مفعّلة' : 'بانتظار التفعيل';
  const sid = s.sid || s.id;

  let gradesHtml = '';
  if (useApi()) {
    gradesHtml = subjectAr
      ? '<div class="student-no-link" style="font-size:12px;color:var(--text-mid)">درجات المشاركة قريبًا عبر النظام الجديد</div>'
      : '';
  } else if (sid && subjectAr) {
    const gradeIdParticipation = 'participation_' + subjectAr;
    const [partVal, examGrades] = await Promise.all([
      getExistingScore(sid, gradeIdParticipation),
      getExamGrades(sid, subjectAr),
    ]);

    const examGradesHtml = examGrades.length
      ? examGrades.map(g => `
        <span class="exam-grade-pill" title="${g.label || 'اختبار'}">
          ${g.label || 'اختبار'}: ${g.score ?? '—'}${g.total ? ' / ' + g.total : ''}
        </span>`).join('')
      : '';

    gradesHtml = `
      <div class="student-grades">
        <label>مشاركة
          <input type="number" min="0" max="${PARTICIPATION_TOTAL}" id="partScore-${sid}-${subjectAr}" value="${partVal}" placeholder="0"
            onchange="savePartGrade('${sid}','${subjectAr}')" style="width:56px">
          <span style="font-size:11px;color:var(--text-mid,#8a6a52)">/ ${PARTICIPATION_TOTAL}</span>
        </label>
        ${examGradesHtml ? `<div class="exam-grades-wrap">${examGradesHtml}</div>` : ''}
      </div>`;
  } else if (sid) {
    gradesHtml = `<div class="student-no-link">⚠️ مقدرناش نلاقي سجل طالبة بنفس الاسم — كلّمي الإدارة</div>`;
  }

  return `
    <div class="student-card">
      <div class="student-avatar">🧕</div>
      <div class="student-info">
        <div class="student-name">${s.name || s.email || '—'}</div>
        <div class="student-year">${s.year ? 'السنة: ' + s.year : ''}</div>
        <span class="student-status ${statusClass}">${statusText}</span>
      </div>
      ${gradesHtml}
    </div>`;
}

window.onSubjectPickerChange = () => {
  window._subjectAr = document.getElementById('subjectPicker').value || '';
  loadPublishState(window._subjectAr);
  window.filterStudents();
};

window.filterStudents = async () => {
  const q        = document.getElementById('searchInput').value.trim().toLowerCase();
  const filtered = q ? allStudents.filter(s => (s.name || '').toLowerCase().includes(q)) : allStudents;
  const grid     = document.getElementById('studentsGrid');
  grid.classList.add('visible');
  if (filtered.length === 0) {
    grid.innerHTML = `<div class="empty-state"><i class="ti ti-user-off"></i>لا توجد طالبات مطابقة للبحث</div>`;
    return;
  }
  const rows = await Promise.all(filtered.map(s => studentRowHTML(s, window._subjectAr)));
  grid.innerHTML = rows.join('');
};

function showError(msg) {
  hide('loadingState');
  document.getElementById('studentsGrid').innerHTML =
    `<div class="empty-state"><i class="ti ti-alert-circle"></i>${msg}</div>`;
  document.getElementById('studentsGrid').classList.add('visible');
}

async function finishStudentsUi(role, subjectAr) {
  window._subjectAr = subjectAr || '';
  hide('loadingState');
  document.getElementById('searchInput').style.display = 'block';
  document.getElementById('pageSub').innerHTML =
    `الطالبات المسجلات في مادتك <span class="count-badge">${allStudents.length}</span>`;
  await window.filterStudents();
}

async function bootLaravelMyStudents() {
  if (!getToken()) { location.href = '/Mateen/html/login.html'; return; }
  try {
    const me = await api.me();
    const userData = me?.data || me || getStoredUser() || {};
    const role = effectiveRole(userData, userData.email || '');
    if (!['teacher','admin','support','supervisor'].includes(role)) {
      location.href = '/Mateen/html/home.html';
      return;
    }
    mountTestModeSwitcher(userData, userData.email || '');

    let subjectAr = '';
    try {
      const subjRes = await api.subjects.list();
      const subjects = Array.isArray(subjRes?.data) ? subjRes.data : (Array.isArray(subjRes) ? subjRes : []);
      if (userData.subject_id != null) {
        const hit = subjects.find(s => String(s.id) === String(userData.subject_id));
        subjectAr = hit?.title || SUBJECT_MAP[hit?.slug] || '';
      }
    } catch (_) {}

    if (subjectAr) {
      document.getElementById('subjectBadge').style.display = 'inline-flex';
      document.getElementById('subjectName').textContent = subjectAr;
      loadPublishState(subjectAr);
    } else if (role !== 'teacher') {
      const picker = document.getElementById('subjectPicker');
      const uniqueSubjects = [...new Set(Object.values(SUBJECT_MAP))];
      picker.innerHTML = '<option value="">— اختاري المادة لعرض/تسجيل درجاتها —</option>' +
        uniqueSubjects.map(s => `<option value="${s}">${s}</option>`).join('');
      picker.style.display = 'block';
    }

    const stuRes = await api.students.list();
    const raw = Array.isArray(stuRes?.data) ? stuRes.data : (Array.isArray(stuRes) ? stuRes : []);
    allStudents = raw
      .filter(s => !s.archived)
      .map(s => ({
        id: String(s.id),
        sid: String(s.id),
        name: s.name || s.user?.name || '—',
        email: s.email || s.user?.email || '',
        status: s.status || 'active',
        year: s.year || '',
        enrolledSubjects: s.enrolled_subjects || s.enrolledSubjects || [],
      }));

    if (role === 'teacher' && subjectAr) {
      allStudents = allStudents.filter(s =>
        !s.enrolledSubjects?.length || s.enrolledSubjects.includes(subjectAr)
      );
    }

    await finishStudentsUi(role, subjectAr);
  } catch (e) {
    console.error('[طالباتي] API خطأ:', e);
    showError('حدث خطأ في التحميل، حاولي مرة أخرى');
  }
}

async function bootFirebaseMyStudents() {
  await ensureFirebase();
  onAuthStateChanged(auth, async user => {
  if (!user) { location.href = '/Mateen/html/login.html'; return; }

  try {
    const userSnap = await getDoc(doc(db, 'users', user.uid));
    const userData = userSnap.exists() ? userSnap.data() : {};
    const role     = effectiveRole(userData, user.email);
    const subject  = userData.subject || '';

    if (!['teacher','admin','support'].includes(role)) {
      location.href = '/Mateen/html/home.html';
      return;
    }
    mountTestModeSwitcher(userData, user.email);

    const subjectAr = SUBJECT_MAP[subject] || subject;

    if (subjectAr) {
      document.getElementById('subjectBadge').style.display = 'inline-flex';
      document.getElementById('subjectName').textContent    = subjectAr;
      loadPublishState(subjectAr);
    } else if (role !== 'teacher') {
      const picker = document.getElementById('subjectPicker');
      const uniqueSubjects = [...new Set(Object.values(SUBJECT_MAP))];
      picker.innerHTML = '<option value="">— اختاري المادة لعرض/تسجيل درجاتها —</option>' +
        uniqueSubjects.map(s => `<option value="${s}">${s}</option>`).join('');
      picker.style.display = 'block';
    }

    const usersSnap = await getDocs(query(collection(db, 'users'), where('role', '==', 'mateen')));
    const usersByUid = new Map();
    usersSnap.docs.forEach(d => usersByUid.set(d.id, { id: d.id, ...d.data() }));
    const usersByName = new Map();
    usersSnap.docs.forEach(d => {
      const norm = normalizeStuName(d.data().name || '');
      if (norm && !usersByName.has(norm)) usersByName.set(norm, d.id);
    });

    const studentsSnap = await getDocs(collection(db, 'students'));
    let allDocsRaw = studentsSnap.docs.map(sd => {
      const sData = sd.data();
      const linkedUid = sData.uid || usersByName.get(normalizeStuName(sData.name || '')) || null;
      const uData  = linkedUid ? usersByUid.get(linkedUid) : null;
      return {
        id: linkedUid || sd.id,
        sid: sd.id,
        name: sData.name || uData?.name || '—',
        email: uData?.email || '',
        status: uData?.status || 'pending',
        enrolledSubjects: uData?.enrolledSubjects || [],
        archived: !!sData.archived,
      };
    });

    const byId = new Map();
    allDocsRaw.forEach(s => {
      if (!byId.has(s.id)) byId.set(s.id, []);
      byId.get(s.id).push(s);
    });
    let allDocs = [];
    byId.forEach(group => {
      const anyArchived  = group.some(s => s.archived);
      const anySuspended = group.some(s => s.status === 'suspended');
      if (anyArchived || anySuspended) return;
      allDocs.push(group[0]);
    });

    if (role === 'teacher' && subjectAr) {
      allDocs = allDocs.filter(s => s.enrolledSubjects.includes(subjectAr));
    }

    allStudents = allDocs;
    await finishStudentsUi(role, subjectAr);

  } catch(e) {
    console.error('[طالباتي] خطأ:', e);
    showError('حدث خطأ في التحميل، حاولي مرة أخرى');
  }
  });
}

if (useApi()) bootLaravelMyStudents();
else bootFirebaseMyStudents();
</script>
<script type="module" src="/Mateen/js/notifications.js?v=20260731c"></script>
</body>
</html>
