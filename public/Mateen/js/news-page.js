/**
 * news-page.js — أخبار + مواعيد for news.html
 * Laravel mode: /api/v1/news + /api/v1/schedules
 * Legacy: Firestore news + events
 */
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { api, getStoredUser, getToken, isLaravelApi } from "./api.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

let isAdmin = false;
let _currentUserRoleForNews = '';
let _editingId = null;
let db = null;
let auth = null;
let collection, query, orderBy, onSnapshot, deleteDoc, doc, getDoc, addDoc, updateDoc, serverTimestamp;

function unwrapList(res) {
  if (Array.isArray(res)) return res;
  if (Array.isArray(res?.data)) return res.data;
  return [];
}

function formatArDate(value) {
  if (!value) return '';
  const d = value instanceof Date ? value : new Date(value);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleDateString('ar', { day: 'numeric', month: 'long', year: 'numeric' });
}

function mapApiNews(n) {
  return {
    id: String(n.id),
    title: n.title || '',
    body: n.body || '',
    tag: '📝 خبر',
    pinned: false,
    visibility: 'public',
    targetRoles: [],
    createdAt: n.published_at || n.created_at || null,
    status: n.status,
  };
}

function renderNewsCards(items) {
  document.getElementById('newsLoading')?.classList.add('hidden');
  const list = document.getElementById('newsList');
  const empty = document.getElementById('newsEmpty');
  if (!list) return;

  if (!items.length) {
    empty?.classList.remove('hidden');
    list.innerHTML = '';
    return;
  }

  empty?.classList.add('hidden');
  list.innerHTML = items.map((n) => {
    const date = formatArDate(n.createdAt);
    const safe = JSON.stringify({
      title: n.title,
      body: n.body,
      tag: n.tag,
      pinned: n.pinned,
      visibility: n.visibility || 'public',
      targetRoles: n.targetRoles || [],
    }).replace(/</g, '\\u003c');
    return `
      <div class="news-card ${n.pinned ? 'featured' : ''}">
        <div class="news-card-head">
          <div>
            ${n.pinned ? '<div class="news-tag">📌 مثبت</div>' : `<div class="news-tag">${n.tag || '📝 خبر'}</div>`}
            <div class="news-date"><i class="ti ti-calendar"></i> ${date}</div>
          </div>
          ${isAdmin ? `
            <div class="news-admin-btns">
              <button onclick='editNews(${JSON.stringify(n.id)}, ${safe})' class="btn-edit-news"><i class="ti ti-pencil"></i> تعديل</button>
              <button onclick="deleteNews('${n.id}')" class="btn-delete-news"><i class="ti ti-trash"></i> حذف</button>
            </div>` : ''}
        </div>
        <h3>${n.title || ''}</h3>
        <p>${n.body || ''}</p>
      </div>`;
  }).join('');
}

function renderEvents(items) {
  const el = document.getElementById('eventsList');
  if (!el) return;
  if (!items.length) {
    el.innerHTML = '<div class="tl-item"><div class="tl-dot"></div><div><div class="tl-label" style="color:#aaa">لا توجد مواعيد</div></div></div>';
    return;
  }
  el.innerHTML = items.map((e) => `
    <div class="tl-item">
      <div class="tl-dot ${e.highlight ? 'gold' : ''}"></div>
      <div>
        <div class="tl-label">${e.label || e.title || ''}</div>
        <div class="tl-date">${e.date || ''}</div>
      </div>
    </div>
  `).join('');
}

async function loadNewsApi() {
  try {
    const res = await api.news.list();
    const items = unwrapList(res).map(mapApiNews);
    // Guests: all published API items are visible. Members see the same catalog for now.
    renderNewsCards(items);
  } catch (e) {
    console.warn('[news] API load failed:', e);
    document.getElementById('newsLoading')?.classList.add('hidden');
    document.getElementById('newsEmpty')?.classList.remove('hidden');
  }
}

async function loadEventsApi() {
  try {
    const res = await api.schedules.list();
    const items = unwrapList(res).map((s) => ({
      title: s.title || '',
      label: s.title || '',
      date: formatArDate(s.starts_at),
      highlight: false,
    }));
    renderEvents(items);
  } catch (e) {
    console.warn('[news] schedules API failed:', e);
    renderEvents([]);
  }
}

async function ensureFirebase() {
  if (db) return;
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const firestore = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const firebaseAuth = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  ({ collection, query, orderBy, onSnapshot, deleteDoc, doc, getDoc, addDoc, updateDoc, serverTimestamp } = firestore);
  const app = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  auth = firebaseAuth.getAuth(app);
  db = firestore.getFirestore(app);
}

function loadNewsFirebase(showAll) {
  const q = query(collection(db, 'news'), orderBy('createdAt', 'desc'));
  onSnapshot(q, (snap) => {
    const visibleDocs = snap.docs.filter((d) => {
      const data = d.data();
      const v = data.visibility;
      if (v === 'public') return true;
      const membersVisible = (!v || v === 'members') ? showAll : showAll;
      if (!membersVisible) return false;
      const targetRoles = data.targetRoles || [];
      if (targetRoles.length && !targetRoles.includes(_currentUserRoleForNews)) return false;
      return true;
    });
    const items = visibleDocs.map((d) => {
      const n = d.data();
      return {
        id: d.id,
        ...n,
        createdAt: n.createdAt?.toDate?.() || null,
      };
    });
    renderNewsCards(items);
  });
}

function applyStaffUi(role) {
  _currentUserRoleForNews = role || '';
  isAdmin = ['admin', 'supervisor'].includes(role);
  if (isAdmin) document.getElementById('addNewsBtn')?.classList.add('show');
}

window.toggleNewsRolesBox = (which) => {
  if (which === 'news') {
    document.getElementById('newsRolesBox').style.display =
      document.getElementById('newsVisibility').value === 'members' ? 'flex' : 'none';
  } else {
    document.getElementById('editRolesBox').style.display =
      document.getElementById('editVisibility').value === 'members' ? 'flex' : 'none';
  }
};

window.editNews = (id, data) => {
  _editingId = id;
  document.getElementById('editTitle').value = data.title || '';
  document.getElementById('editBody').value = data.body || '';
  document.getElementById('editTag').value = data.tag || '📝 خبر';
  document.getElementById('editPinned').checked = data.pinned || false;
  document.getElementById('editVisibility').value = data.visibility || 'members';
  const targetRoles = data.targetRoles || [];
  document.querySelectorAll('.edit-role-check').forEach((cb) => {
    cb.checked = targetRoles.includes(cb.value);
  });
  toggleNewsRolesBox('edit');
  document.getElementById('editNewsModal').classList.add('show');
};

window.deleteNews = async (id) => {
  if (!confirm('حذف هذا الخبر نهائياً؟')) return;
  try {
    if (useApi()) {
      await api.news.remove(id);
      await loadNewsApi();
    } else {
      await deleteDoc(doc(db, 'news', id));
    }
  } catch (e) {
    alert(e?.message || 'تعذر الحذف');
  }
};

window.submitEditNews = async () => {
  if (!_editingId) return;
  const title = document.getElementById('editTitle').value.trim();
  const body = document.getElementById('editBody').value.trim();
  const tag = document.getElementById('editTag').value;
  const pinned = document.getElementById('editPinned').checked;
  const visibility = document.getElementById('editVisibility').value;
  const targetRoles = visibility === 'members'
    ? [...document.querySelectorAll('.edit-role-check:checked')].map((cb) => cb.value)
    : [];
  if (!title) { alert('يرجى إدخال العنوان'); return; }

  try {
    if (useApi()) {
      await api.news.update(_editingId, {
        title,
        body,
        status: 'published',
        published_at: new Date().toISOString(),
      });
      await loadNewsApi();
    } else {
      await updateDoc(doc(db, 'news', _editingId), {
        title, body, tag, pinned, visibility, targetRoles,
        updatedAt: serverTimestamp(),
      });
    }
    _editingId = null;
    document.getElementById('editNewsModal').classList.remove('show');
  } catch (e) {
    alert(e?.message || 'تعذر الحفظ');
  }
};

window.submitNews = async () => {
  const title = document.getElementById('newsTitle').value.trim();
  const body = document.getElementById('newsBody').value.trim();
  const tag = document.getElementById('newsTag').value;
  const pinned = document.getElementById('newsPinned').checked;
  const visibility = document.getElementById('newsVisibility').value;
  const targetRoles = visibility === 'members'
    ? [...document.querySelectorAll('.news-role-check:checked')].map((cb) => cb.value)
    : [];
  const submitBtn = document.getElementById('newsSubmitBtn');
  if (!submitBtn) { alert('خطأ: لم يتم تحميل الصفحة بشكل صحيح، أعيدي التحميل'); return; }
  if (!title) { alert('يرجى إدخال العنوان'); return; }

  try {
    if (useApi()) {
      await api.news.create({
        title,
        body: body || title,
        status: 'published',
        published_at: new Date().toISOString(),
      });
      await loadNewsApi();
    } else {
      await addDoc(collection(db, 'news'), {
        title, body, tag, pinned, visibility, targetRoles,
        createdAt: serverTimestamp(),
      });
    }
    document.getElementById('newsTitle').value = '';
    document.getElementById('newsBody').value = '';
    document.getElementById('newsPinned').checked = false;
    document.getElementById('newsVisibility').value = 'members';
    document.querySelectorAll('.news-role-check').forEach((cb) => { cb.checked = false; });
    toggleNewsRolesBox('news');
    document.getElementById('addNewsModal').classList.remove('show');
  } catch (e) {
    alert(e?.message || 'تعذر النشر');
  }
};

async function bootApi() {
  const stored = getStoredUser();
  if (stored?.role) applyStaffUi(stored.role);
  if (stored?.id) localStorage.setItem(`news_last_seen_${stored.id}`, Date.now().toString());

  // Guest list may already be filled by the inline script in news.html —
  // still refresh so staff get edit/delete controls when logged in.
  const needsStaffRefresh = !!getToken() || !!stored?.role;
  if (needsStaffRefresh || !document.getElementById('newsList')?.children?.length) {
    await Promise.all([loadNewsApi(), loadEventsApi()]);
  } else {
    document.getElementById('newsLoading')?.classList.add('hidden');
    const loading = document.getElementById('newsLoading');
    if (loading) loading.style.display = 'none';
  }

  if (getToken()) {
    try {
      const me = await api.me();
      const user = me?.data || me;
      if (user?.role) {
        applyStaffUi(user.role);
        await loadNewsApi();
      }
    } catch { /* guest ok */ }
  }
}

async function bootFirebase() {
  await ensureFirebase();
  onSnapshot(query(collection(db, 'events'), orderBy('order', 'asc')), (snap) => {
    const items = snap.docs.map((d) => {
      const e = d.data();
      return { label: e.label || e.title || '', date: e.date || '', highlight: !!e.highlight };
    });
    renderEvents(items);
  });
  loadNewsFirebase(false);
  const { onAuthStateChanged } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  onAuthStateChanged(auth, async (user) => {
    if (!user) return;
    localStorage.setItem(`news_last_seen_${user.uid}`, Date.now().toString());
    const snap = await getDoc(doc(db, 'users', user.uid));
    const role = snap.exists() ? snap.data().role : '';
    applyStaffUi(role);
    loadNewsFirebase(true);
  });
}

if (useApi()) bootApi().catch((e) => console.warn('[news] API boot error:', e));
else bootFirebase().catch((e) => console.warn('[news] Firebase boot error:', e));
