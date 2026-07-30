// ===========================
//  نظام الواجبات — Assignments System
// ===========================
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { api, getStoredUser, isLaravelApi } from "./api.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

let db = null, auth = null;
let collection, doc, addDoc, getDoc, getDocs, updateDoc, deleteDoc,
    query, where, orderBy, serverTimestamp, Timestamp;

async function ensureFirebase() {
  if (useApi()) throw new Error('Firebase data disabled in Laravel mode');
  if (db) return { db, auth };
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const firestore = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const firebaseAuth = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  ({ collection, doc, addDoc, getDoc, getDocs, updateDoc, deleteDoc, query, where, orderBy, serverTimestamp, Timestamp } = firestore);
  const app = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  auth = firebaseAuth.getAuth(app);
  db = firestore.getFirestore(app);
  return { db, auth };
}

function apiDeadline(dueAt) {
  if (!dueAt) return null;
  const d = new Date(dueAt);
  return { toDate: () => d };
}

function mapApiAssignment(a) {
  return {
    id: String(a.id),
    materialId: a.learning_material_id != null ? String(a.learning_material_id) : '',
    course: a.subject?.name || '',
    kind: 'homework',
    title: a.title,
    description: a.description || '',
    allowFile: true,
    allowText: true,
    deadline: apiDeadline(a.due_at),
    examLink: null,
    createdAt: a.created_at,
  };
}

function mapApiSubmission(s) {
  const u = s.user || {};
  return {
    id: String(s.id),
    studentUid: String(s.user_id),
    studentName: u.name || u.email || 'طالبة',
    fileUrl: s.attachment_url || null,
    textAnswer: s.content || null,
    grade: s.grade ?? null,
    feedback: s.feedback || null,
    gradedAt: s.updated_at || null,
  };
}

async function apiListAssignments(filter = {}) {
  let q = '';
  if (filter.subject_id) q = `?subject_id=${encodeURIComponent(filter.subject_id)}`;
  const res = await api.assignments.list(q);
  return (res?.data || []).map(mapApiAssignment);
}

// ── إضافة واجب أو اختبار جديد (معلمة/أدمن فقط) ────────────────
export async function addAssignment({ materialId, course, title, description, allowFile, allowText, deadline, kind, examLink, subjectId }) {
  if (useApi()) {
    const isExam = kind === 'exam';
    if (isExam) throw new Error('اختبارات الروابط غير مدعومة عبر الواجهة حالياً');
    if (!allowFile && !allowText) throw new Error('اختاري وسيلة تسليم واحدة على الأقل');
    const body = {
      title,
      description: description || '',
      due_at: deadline || null,
      learning_material_id: materialId ? Number(materialId) || materialId : null,
    };
    if (subjectId) body.subject_id = Number(subjectId);
    const res = await api.assignments.create(body);
    return { id: String(res?.data?.id || res?.id) };
  }

  await ensureFirebase();
  const user = auth.currentUser;
  if (!user) throw new Error('يجب تسجيل الدخول');
  const isExam = kind === 'exam';
  if (isExam) {
    if (!examLink) throw new Error('اكتبي رابط الاختبار');
  } else if (!allowFile && !allowText) {
    throw new Error('اختاري وسيلة تسليم واحدة على الأقل');
  }

  return await addDoc(collection(db, 'assignments'), {
    materialId, course, title, description: description || '',
    kind: isExam ? 'exam' : 'homework',
    examLink: isExam ? examLink : null,
    allowFile: !!allowFile, allowText: !!allowText,
    deadline: deadline ? Timestamp.fromDate(new Date(deadline)) : null,
    createdBy: user.uid,
    createdAt: serverTimestamp()
  });
}

// ── جلب واجبات محاضرة معينة ──────────────────────────────────
export async function getAssignmentsForMaterial(materialId) {
  if (useApi()) {
    const all = await apiListAssignments();
    return all.filter(a => a.materialId === String(materialId));
  }
  await ensureFirebase();
  const q = query(collection(db, 'assignments'), where('materialId', '==', materialId));
  const snap = await getDocs(q);
  return snap.docs.map(d => ({ id: d.id, ...d.data() }));
}

// ── جلب كل واجبات مادة معينة (لصفحة المعلمة) ─────────────────
export async function getAssignmentsForCourse(course) {
  if (useApi()) {
    const all = await apiListAssignments();
    return all.filter(a => a.course === course);
  }
  await ensureFirebase();
  const q = query(collection(db, 'assignments'), where('course', '==', course), orderBy('createdAt', 'desc'));
  const snap = await getDocs(q);
  return snap.docs.map(d => ({ id: d.id, ...d.data() }));
}

export async function getAssignmentById(assignmentId) {
  if (useApi()) {
    const all = await apiListAssignments();
    return all.find(a => a.id === String(assignmentId)) || null;
  }
  await ensureFirebase();
  const snap = await getDoc(doc(db, 'assignments', assignmentId));
  return snap.exists() ? { id: snap.id, ...snap.data() } : null;
}

// ── حذف كل تسليمات واجب معين (مساعدة داخلية) ──────────────────
async function deleteSubmissionsFor(assignmentId) {
  await ensureFirebase();
  const snap = await getDocs(collection(db, 'assignments', assignmentId, 'submissions'));
  await Promise.all(snap.docs.map(d => deleteDoc(d.ref)));
}

// ── حذف واجب (بيمسح تسليماته الأول عشان محدش يفضل يتيم) ───────
export async function deleteAssignment(assignmentId) {
  if (useApi()) throw new Error('حذف الواجبات غير متاح عبر الواجهة حالياً');
  await deleteSubmissionsFor(assignmentId);
  await deleteDoc(doc(db, 'assignments', assignmentId));
}

// ── حذف كل الواجبات (+تسليماتها) المرتبطة بمادة معينة ─────────
export async function deleteAssignmentsForMaterial(materialId) {
  if (useApi()) return;
  await ensureFirebase();
  const q = query(collection(db, 'assignments'), where('materialId', '==', materialId));
  const snap = await getDocs(q);
  await Promise.all(snap.docs.map(async d => {
    await deleteSubmissionsFor(d.id);
    await deleteDoc(d.ref);
  }));
}

// ── تسليم رد الطالبة على الواجب ──────────────────────────────
export async function submitAssignment(assignmentId, { fileUrl, textAnswer }) {
  if (useApi()) {
    const me = getStoredUser();
    if (!me?.id) throw new Error('يجب تسجيل الدخول');
    await api.assignments.submit(assignmentId, {
      content: textAnswer || null,
      attachment_url: fileUrl || null,
    });
    return;
  }

  await ensureFirebase();
  const user = auth.currentUser;
  if (!user) throw new Error('يجب تسجيل الدخول');

  const studentSnap = await getDoc(doc(db, 'users', user.uid));
  const studentName = studentSnap.exists() ? (studentSnap.data().name || user.email) : user.email;

  await updateDoc(doc(db, 'assignments', assignmentId, 'submissions', user.uid), {
    studentUid: user.uid,
    studentName,
    fileUrl: fileUrl || null,
    textAnswer: textAnswer || null,
    submittedAt: serverTimestamp()
  }).catch(async () => {
    const { setDoc } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
    await setDoc(doc(db, 'assignments', assignmentId, 'submissions', user.uid), {
      studentUid: user.uid,
      studentName,
      fileUrl: fileUrl || null,
      textAnswer: textAnswer || null,
      submittedAt: serverTimestamp(),
      grade: null,
      feedback: null,
      gradedAt: null,
      gradedBy: null
    });
  });
}

// ── جلب رد طالبة معينة على واجب (الطالبة بتشوف ردها هي بس) ───
export async function getMySubmission(assignmentId) {
  if (useApi()) {
    const me = getStoredUser();
    if (!me?.id) return null;
    const res = await api.assignments.submissions(assignmentId);
    const mine = (res?.data || []).find(s => String(s.user_id) === String(me.id));
    return mine ? mapApiSubmission(mine) : null;
  }
  await ensureFirebase();
  const user = auth.currentUser;
  if (!user) return null;
  const snap = await getDoc(doc(db, 'assignments', assignmentId, 'submissions', user.uid));
  return snap.exists() ? { id: snap.id, ...snap.data() } : null;
}

// ── جلب كل الردود على واجب (للمعلمة/الأدمن فقط) ──────────────
export async function getAllSubmissions(assignmentId) {
  if (useApi()) {
    const res = await api.assignments.submissions(assignmentId);
    return (res?.data || []).map(mapApiSubmission);
  }
  await ensureFirebase();
  const snap = await getDocs(collection(db, 'assignments', assignmentId, 'submissions'));
  return snap.docs.map(d => ({ id: d.id, ...d.data() }));
}

// ── تقييم رد طالبة (معلمة/أدمن) ───────────────────────────────
export async function gradeSubmission(assignmentId, studentUid, { grade, feedback }) {
  if (useApi()) {
    const subs = await getAllSubmissions(assignmentId);
    const sub = subs.find(s => s.studentUid === String(studentUid));
    if (!sub) throw new Error('لم يُعثر على التسليم');
    await api.assignments.grade(sub.id, { grade: grade ?? null, feedback: feedback || '' });
    return;
  }
  await ensureFirebase();
  const user = auth.currentUser;
  if (!user) throw new Error('يجب تسجيل الدخول');
  await updateDoc(doc(db, 'assignments', assignmentId, 'submissions', studentUid), {
    grade: grade ?? null,
    feedback: feedback || '',
    gradedAt: serverTimestamp(),
    gradedBy: user.uid
  });
}

// ── حساب الوقت المتبقي للموعد النهائي ────────────────────────
export function getDeadlineStatus(deadline) {
  if (!deadline) return { status: 'none', text: 'بدون موعد نهائي', color: 'var(--text-mid)' };
  const now = new Date();
  const dl = deadline.toDate ? deadline.toDate() : new Date(deadline);
  const diffMs = dl - now;
  const diffHours = diffMs / (1000 * 60 * 60);
  const diffDays = diffHours / 24;

  if (diffMs < 0) {
    return { status: 'expired', text: 'انتهى الموعد النهائي', color: '#e74c3c' };
  } else if (diffHours <= 24) {
    return { status: 'urgent', text: `باقي ${Math.round(diffHours)} ساعة فقط!`, color: '#e74c3c' };
  } else if (diffDays <= 3) {
    return { status: 'soon', text: `باقي ${Math.round(diffDays)} يوم`, color: '#e67e22' };
  } else {
    return { status: 'ok', text: dl.toLocaleDateString('ar-EG', { day:'numeric', month:'long', year:'numeric' }), color: 'var(--green-dark)' };
  }
}
