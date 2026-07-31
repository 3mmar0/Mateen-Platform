/**
 * Teacher page contact/auth — Laravel mode uses session + conversations API.
 */
import { USE_LARAVEL_API } from "./config.js";
import { api, isLaravelApi } from "./api.js";
import { resolveLaravelSession } from "./session.js";
import { effectiveRole, mountTestModeSwitcher } from "./test-mode.js";
import { applyCustomTheme } from "./custom-theme.js";

export const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

export async function bootTeacherPageAuth(allowedRoles = ['teacher', 'admin', 'supervisor']) {
  if (useApi()) {
    const session = await resolveLaravelSession({ refresh: false });
    if (!session) return;
    const role = effectiveRole(session.raw || session, session.email);
    if (!allowedRoles.includes(role)) {
      window.location.href = '../html/login.html';
      return;
    }
    if (session.status === 'pending' || session.status === 'suspended') {
      window.location.href = '../html/login.html';
      return;
    }
    mountTestModeSwitcher(session.raw || session, session.email);
    applyCustomTheme(session.raw || session);
    return;
  }

  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const { getFirestore, doc, getDoc } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const { getAuth, onAuthStateChanged } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  const { FIREBASE_CONFIG } = await import("./config.js");

  const app = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  const db = getFirestore(app);
  const auth = getAuth(app);

  onAuthStateChanged(auth, async user => {
    if (!user) { window.location.href = '../html/login.html'; return; }
    const snap = await getDoc(doc(db, 'users', user.uid));
    const userData = snap.exists() ? snap.data() : {};
    const role = effectiveRole(userData, user.email);
    const status = userData.status || '';
    if (!allowedRoles.includes(role)) {
      window.location.href = '../html/login.html';
      return;
    }
    mountTestModeSwitcher(userData, user.email);
    applyCustomTheme(userData);
    if (status === 'pending' || status === 'suspended') {
      window.location.href = '../html/login.html';
    }
  });
}

export async function sendTeacherContactMessage({ subjectAr, topic, name, phone, body, errEl, errTx, btn, successEl, clearIds = ['msgName', 'msgPhone', 'msgBody'] }) {
  errEl.classList.remove('show');
  if (!name) { errTx.textContent = 'يرجى إدخال اسمك'; errEl.classList.add('show'); return; }
  if (!topic) { errTx.textContent = 'يرجى اختيار موضوع الرسالة'; errEl.classList.add('show'); return; }
  if (!body) { errTx.textContent = 'يرجى كتابة الرسالة'; errEl.classList.add('show'); return; }

  btn.disabled = true;
  btn.innerHTML = '<i class="ti ti-loader" style="animation:spin .8s linear infinite;display:inline-block"></i> جارٍ الإرسال...';

  const text = `[${subjectAr} — ${topic}]\nالاسم: ${name}${phone ? `\nالجوال: ${phone}` : ''}\n${body}`;

  try {
    if (useApi()) {
      const session = await resolveLaravelSession({ refresh: false });
      if (!session) {
        errTx.textContent = 'يرجى تسجيل الدخول لإرسال رسالة';
        errEl.classList.add('show');
        return;
      }
      const convRes = await api.conversations.list();
      let recipientId = null;
      for (const c of (convRes?.data || [])) {
        const admin = (c.participants || []).find(p => p.role === 'admin');
        if (admin) { recipientId = admin.id; break; }
      }
      if (!recipientId) {
        try {
          const usersRes = await api.support.users();
          const users = Array.isArray(usersRes?.data) ? usersRes.data : (usersRes?.data?.data || []);
          recipientId = users.find(u => u.role === 'admin')?.id;
        } catch (_) {}
      }
      if (!recipientId) throw new Error('تعذر إيجاد مستلم — استخدمي صفحة الرسائل');
      const created = await api.conversations.create({ participant_id: Number(recipientId) || recipientId });
      const convId = created?.data?.id ?? created?.id;
      await api.conversations.send(convId, { body: text });
    } else {
      await window._sendTeacherContactFirebase({ subjectAr, topic, name, phone, body, text });
    }

    successEl.classList.add('show');
    clearIds.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    const topicEl = document.getElementById('msgTopic');
    if (topicEl) topicEl.value = '';
  } catch (e) {
    errTx.textContent = e.message || 'حدث خطأ أثناء الإرسال، حاولي مجدداً';
    errEl.classList.add('show');
  } finally {
    btn.innerHTML = '<i class="ti ti-send"></i> إرسال الرسالة';
    btn.disabled = false;
  }
}

export async function registerTeacherContactFirebase(teacherId, subjectAr) {
  window._sendTeacherContactFirebase = async ({ topic, name, phone, body, subjectAr: subj }) => {
    const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
    const { getFirestore, collection, addDoc } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
    const { FIREBASE_CONFIG } = await import("./config.js");
    const app = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
    const db = getFirestore(app);
    const teacherName = document.getElementById('teacherName')?.textContent || '';
    await addDoc(collection(db, 'teachers', teacherId, 'messages'), {
      name, phone, topic, body,
      teacherId,
      teacherName,
      subject: subj || subjectAr,
      sentAt: Date.now(),
      read: false,
    });
  };
}
