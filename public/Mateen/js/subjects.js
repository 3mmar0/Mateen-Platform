// ===========================
//  إدارة المواد الدراسية — Dynamic Subjects
//  Laravel mode: api.subjects.*  |  Legacy: Firestore (lazy)
// ===========================
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { api, isLaravelApi } from "./api.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

let db = null;
async function getDb() {
  if (useApi()) throw new Error('Firestore disabled in Laravel API mode');
  if (db) return db;
  const { initializeApp, getApps } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const { getFirestore } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const app = getApps().length ? getApps()[0] : initializeApp(FIREBASE_CONFIG);
  db = getFirestore(app);
  return db;
}

const DEFAULT_SUBJECTS = [
  { name: 'التفسير',    inExams: true, inAttendance: true, inEnrollment: true },
  { name: 'الفقه',      inExams: true, inAttendance: true, inEnrollment: true },
  { name: 'العقيدة',    inExams: true, inAttendance: true, inEnrollment: true },
  { name: 'الحديث',     inExams: true, inAttendance: true, inEnrollment: true },
  { name: 'مقرأة متين', inExams: true, inAttendance: true, inEnrollment: true },
];

let _subjectsCache = null;

function unwrapList(res) {
  if (Array.isArray(res)) return res;
  if (Array.isArray(res?.data)) return res.data;
  return [];
}

function mapApiSubject(s) {
  return {
    id: String(s.id),
    name: s.title,
    slug: s.slug,
    inExams: true,
    inAttendance: true,
    inEnrollment: true,
  };
}

async function loadAllSubjectsRaw() {
  if (_subjectsCache) return _subjectsCache;
  if (useApi()) {
    try {
      const res = await api.subjects.list();
      _subjectsCache = unwrapList(res).map(mapApiSubject);
      if (!_subjectsCache.length) {
        _subjectsCache = DEFAULT_SUBJECTS.map((s, i) => ({ id: 'default-' + i, ...s }));
      }
    } catch (e) {
      console.error('loadSubjects API error:', e);
      _subjectsCache = DEFAULT_SUBJECTS.map((s, i) => ({ id: 'default-' + i, ...s }));
    }
    return _subjectsCache;
  }
  try {
    const { collection, getDocs } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
    const database = await getDb();
    const snap = await getDocs(collection(database, 'subjects'));
    if (snap.empty) {
      _subjectsCache = DEFAULT_SUBJECTS.map((s, i) => ({ id: 'default-' + i, ...s }));
    } else {
      const toMillis = (v) => v?.toMillis?.() ?? 0;
      _subjectsCache = snap.docs
        .map(d => ({ id: d.id, ...d.data() }))
        .sort((a, b) => (toMillis(a.addedAt) || toMillis(a.createdAt)) - (toMillis(b.addedAt) || toMillis(b.createdAt)));
    }
  } catch (e) {
    console.error('loadSubjects error:', e);
    _subjectsCache = DEFAULT_SUBJECTS.map((s, i) => ({ id: 'default-' + i, ...s }));
  }
  return _subjectsCache;
}

export async function loadSubjects() {
  const all = await loadAllSubjectsRaw();
  return all.map(s => s.name);
}

export async function loadSubjectsFor(key) {
  const all = await loadAllSubjectsRaw();
  return all.filter(s => s[key] !== false).map(s => s.name);
}

export async function addSubject(name, flags = { inExams: true, inAttendance: true, inEnrollment: true }) {
  name = (name || '').trim();
  if (!name) throw new Error('اسم المادة مطلوب');
  if (useApi()) {
    const slug = name.replace(/\s+/g, '-').toLowerCase();
    await api.subjects.create({ title: name, slug });
    _subjectsCache = null;
    return;
  }
  const { collection, addDoc, serverTimestamp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const database = await getDb();
  await addDoc(collection(database, 'subjects'), {
    name,
    inExams: !!flags.inExams,
    inAttendance: !!flags.inAttendance,
    inEnrollment: !!flags.inEnrollment,
    createdAt: serverTimestamp()
  });
  _subjectsCache = null;
}

export async function updateSubjectFlags(id, flags) {
  if (useApi()) {
    _subjectsCache = null;
    return;
  }
  const { doc, updateDoc } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const database = await getDb();
  await updateDoc(doc(database, 'subjects', id), {
    inExams: !!flags.inExams,
    inAttendance: !!flags.inAttendance,
    inEnrollment: !!flags.inEnrollment,
  });
  _subjectsCache = null;
}

export async function deleteSubject(id) {
  if (useApi()) {
    _subjectsCache = null;
    return;
  }
  const { doc, deleteDoc } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const database = await getDb();
  await deleteDoc(doc(database, 'subjects', id));
  _subjectsCache = null;
}

export async function loadSubjectsWithIds() {
  return await loadAllSubjectsRaw();
}

export async function seedDefaultSubjectsIfEmpty() {
  if (useApi()) {
    const res = await api.subjects.list();
    if (unwrapList(res).length) return false;
    for (const s of DEFAULT_SUBJECTS) {
      await api.subjects.create({ title: s.name, slug: s.name.replace(/\s+/g, '-').toLowerCase() });
    }
    _subjectsCache = null;
    return true;
  }
  const { collection, getDocs, addDoc, serverTimestamp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const database = await getDb();
  const snap = await getDocs(collection(database, 'subjects'));
  if (!snap.empty) return false;
  for (const s of DEFAULT_SUBJECTS) {
    await addDoc(collection(database, 'subjects'), { ...s, createdAt: serverTimestamp() });
  }
  _subjectsCache = null;
  return true;
}
