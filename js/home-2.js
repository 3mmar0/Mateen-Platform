import { USE_LARAVEL_API } from "./config.js";
import { api, isLaravelApi } from "./api.js";
import { resolveLaravelSession } from "./session.js";
import { effectiveRole } from "./test-mode.js";

// Sidebar auth is in home-1.js — this file handles the contact form only.

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

function unwrapUsers(res) {
  const data = res?.data;
  if (Array.isArray(data)) return data;
  if (Array.isArray(data?.data)) return data.data;
  return [];
}

async function fillSenderName(session) {
  const ctName = document.getElementById('ctName');
  if (!ctName || !session) return;
  const role = session.role || '';
  if (role === 'admin') {
    ctName.value = 'إدارة متين';
    ctName.readOnly = true;
  } else {
    ctName.value = session.name || '';
  }
}

async function loadRecipientsApi() {
  const select = document.getElementById('ctRecipient');
  if (!select) return;

  try {
    let users = [];
    try {
      const res = await api.support.users();
      users = unwrapUsers(res);
    } catch {
      const session = await resolveLaravelSession({ refresh: false });
      if (session) {
        const convRes = await api.conversations.list();
        const seen = new Set();
        (convRes?.data || []).forEach(c => {
          (c.participants || []).forEach(p => {
            const id = String(p.id);
            if (id !== String(session.id) && !seen.has(id)) {
              seen.add(id);
              users.push(p);
            }
          });
        });
      }
    }

    const admins = users.filter(u => u.role === 'admin');
    const teachers = users.filter(u => u.role === 'teacher' && u.is_active !== false);

    let html = '<option value="">اختاري الجهة</option>';
    if (admins.length) {
      html += '<optgroup label="── الإدارة ──">';
      admins.forEach(u => {
        html += `<option value="${u.id}">${u.name || 'الإدارة العامة'}</option>`;
      });
      html += '</optgroup>';
    }
    if (teachers.length) {
      html += '<optgroup label="── المعلمات ──">';
      teachers.forEach(u => {
        html += `<option value="${u.id}">${u.name || 'معلمة'}</option>`;
      });
      html += '</optgroup>';
    }
    if (!admins.length && !teachers.length) {
      html = '<option value="">لا يوجد مستلمون متاحون</option>';
    }
    select.innerHTML = html;
  } catch (e) {
    console.error('loadRecipients error:', e);
    select.innerHTML = '<option value="">تعذر التحميل</option>';
  }
}

async function loadRecipientsFirebase(db, getDocs, query, collection, where) {
  const select = document.getElementById('ctRecipient');
  if (!select) return;

  try {
    const [adminSnap, teacherSnap] = await Promise.all([
      getDocs(query(collection(db, 'users'), where('role', '==', 'admin'))),
      getDocs(query(collection(db, 'users'), where('role', '==', 'teacher'), where('status', '==', 'active')))
    ]);

    let html = '<option value="">اختاري الجهة</option>';
    if (!adminSnap.empty) {
      html += '<optgroup label="── الإدارة ──">';
      adminSnap.forEach(d => {
        html += `<option value="${d.id}">${d.data().name || 'الإدارة العامة'}</option>`;
      });
      html += '</optgroup>';
    }
    if (!teacherSnap.empty) {
      html += '<optgroup label="── المعلمات ──">';
      teacherSnap.forEach(d => {
        html += `<option value="${d.id}">${d.data().name || 'معلمة'}</option>`;
      });
      html += '</optgroup>';
    }
    if (adminSnap.empty && teacherSnap.empty) {
      html = '<option value="">لا يوجد مستلمون متاحون</option>';
    }
    select.innerHTML = html;
  } catch (e) {
    console.error('loadRecipients error:', e);
    select.innerHTML = '<option value="">تعذر التحميل</option>';
  }
}

window.submitContactNew = async () => {
  const nameEl      = document.getElementById('ctName');
  const recipientEl = document.getElementById('ctRecipient');
  const topicEl     = document.getElementById('ctTopic');
  const bodyEl      = document.getElementById('ctBody');
  const btn         = document.getElementById('ctBtn');
  const successEl   = document.getElementById('ctSuccess');

  [nameEl, recipientEl, topicEl, bodyEl].forEach(el => {
    if (!el || !el.value.trim()) { if (el) el.style.borderColor = '#c0392b'; }
    else el.style.borderColor = '';
  });
  if (!recipientEl?.value || !topicEl?.value.trim() || !bodyEl?.value.trim()) return;

  const recipientUid = recipientEl.value;
  const bodyText     = `[${topicEl.value}]\n${bodyEl.value.trim()}`;

  btn.disabled = true;
  btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> جارٍ الإرسال...';

  try {
    if (useApi()) {
      const session = await resolveLaravelSession({ refresh: false });
      if (!session) {
        alert('يجب تسجيل الدخول أولاً');
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-send"></i> إرسال الرسالة';
        return;
      }
      const created = await api.conversations.create({ participant_id: Number(recipientUid) || recipientUid });
      const convId = created?.data?.id ?? created?.id;
      if (!convId) throw new Error('تعذر إنشاء المحادثة');
      await api.conversations.send(convId, { body: bodyText });
    } else {
      await window._submitContactFirebase(bodyText, recipientUid, nameEl, topicEl, bodyEl);
    }

    btn.innerHTML = '<i class="ti ti-check"></i> تم الإرسال بنجاح!';
    btn.style.background = 'var(--green-mid)';
    if (successEl) successEl.style.display = 'block';
    [nameEl, recipientEl, topicEl, bodyEl].forEach(el => { if (el) el.value = ''; });
    if (useApi()) loadRecipientsApi();
    else window._loadRecipientsFirebase?.();

    setTimeout(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="ti ti-send"></i> إرسال الرسالة';
      btn.style.background = '';
      if (successEl) successEl.style.display = 'none';
    }, 3500);
  } catch (e) {
    console.error(e);
    btn.disabled = false;
    btn.innerHTML = '<i class="ti ti-send"></i> إرسال الرسالة';
    alert('حدث خطأ أثناء الإرسال: ' + e.message);
  }
};

async function bootLaravel() {
  const session = await resolveLaravelSession({ refresh: false });
  if (session) await fillSenderName(session);
  loadRecipientsApi();
}

async function bootFirebase() {
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const { getAuth, onAuthStateChanged } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  const { getFirestore, doc, getDoc, getDocs, addDoc, setDoc, collection, query, where, serverTimestamp } =
    await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const { FIREBASE_CONFIG } = await import("./config.js");

  const app  = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  const auth = getAuth(app);
  const db   = getFirestore(app);

  onAuthStateChanged(auth, async (user) => {
    const ctName = document.getElementById('ctName');
    if (!ctName || !user) return;
    const snap = await getDoc(doc(db, 'users', user.uid));
    const role = snap.exists() ? effectiveRole(snap.data(), user.email) : '';
    if (role === 'admin') {
      ctName.value = 'إدارة متين';
      ctName.readOnly = true;
    } else {
      ctName.value = (snap.exists() && snap.data().name) ? snap.data().name : '';
    }
  });

  window._loadRecipientsFirebase = () => loadRecipientsFirebase(db, getDocs, query, collection, where);
  window._loadRecipientsFirebase();

  window._submitContactFirebase = async (bodyText, recipientUid, nameEl, topicEl, bodyEl) => {
    const user = auth.currentUser;
    if (!user) throw new Error('يجب تسجيل الدخول أولاً');

    const senderSnap = await getDoc(doc(db, 'users', user.uid));
    const senderRole2 = (senderSnap.exists() && senderSnap.data().role) || '';
    const senderName = senderRole2 === 'admin'
      ? 'إدارة متين'
      : (senderSnap.exists() && senderSnap.data().name)
        ? senderSnap.data().name
        : (nameEl.value.trim() || user.email || '');
    const senderRole = (senderSnap.exists() && senderSnap.data().role) || 'student';

    const cid = [user.uid, recipientUid].sort().join('__');
    await setDoc(doc(db, 'conversations', cid), {
      participants: [user.uid, recipientUid],
      lastMsg: bodyText.slice(0, 60) || '',
      lastAt: serverTimestamp(),
      [`unread.${recipientUid}`]: 1,
      [`unread.${user.uid}`]: 0,
    }, { merge: true });

    await addDoc(collection(db, 'conversations', cid, 'messages'), {
      text: bodyText || '',
      senderId: user.uid || '',
      senderName: senderName || '',
      senderRole: senderRole || '',
      sentAt: serverTimestamp(),
    });

    if (recipientUid) {
      await addDoc(collection(db, 'notifications', recipientUid, 'pending'), {
        title: `💬 ${senderName}`,
        body: bodyText.slice(0, 80),
        url: 'https://mateenweb.github.io/Mateen/html/messages.html',
        senderId: user.uid,
        createdAt: serverTimestamp(),
      });
    }
  };
}

if (useApi()) {
  bootLaravel();
} else {
  bootFirebase();
}
