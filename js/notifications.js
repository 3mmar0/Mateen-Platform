
// ── كشف البيئة ──────────────────────────────────────────────────────────
const BASE = window.location.hostname.includes('github.io') ? '/Mateen' : '';
// ═══════════════════════════════════════════════════════
//  notifications.js
//  Laravel mode: Firebase Messaging (FCM) ONLY → POST /devices
//  Legacy mode: Firestore onSnapshot + Auth (when USE_LARAVEL_API=false)
// ═══════════════════════════════════════════════════════
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { api, getStoredUser, getToken, isLaravelApi } from "./api.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

let _fbApp = null;
let notifUnsub = null;
let newsUnsub = null;
let initialized = false;

async function getFirebaseApp() {
  if (_fbApp) return _fbApp;
  const { initializeApp, getApps, getApp } = await import(
    "https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js"
  );
  _fbApp = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  return _fbApp;
}

async function getLegacyFirestore() {
  const app = await getFirebaseApp();
  const { getFirestore } = await import(
    "https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js"
  );
  return getFirestore(app);
}

function playSound() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.setValueAtTime(880, ctx.currentTime);
    osc.frequency.setValueAtTime(660, ctx.currentTime + 0.1);
    gain.gain.setValueAtTime(0.3, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.4);
  } catch (_) {}
}

async function pushToSW(userId, title, body, url, convId) {
  if (useApi()) return;
  try {
    const { collection, addDoc, serverTimestamp } = await import(
      "https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js"
    );
    const db = await getLegacyFirestore();
    await addDoc(
      collection(db, 'notifications', userId, 'pending'),
      { title, body, url, convId: convId || null, createdAt: serverTimestamp() }
    );
  } catch (e) {
    console.warn('[Notif] SW push failed:', e);
  }
}

function getToastContainer() {
  let c = document.getElementById('mateen-toast-container');
  if (!c) {
    c = document.createElement('div');
    c.id = 'mateen-toast-container';
    c.style.cssText = 'position:fixed;top:16px;left:16px;z-index:99999;display:flex;flex-direction:column;gap:8px;max-width:320px';
    document.body.appendChild(c);
  }
  return c;
}

function showNotifToast(title, body, url) {
  const container = getToastContainer();
  const t = document.createElement('div');
  t.style.cssText = `
    background:#1a4a2e;color:#fff;border-radius:12px;
    padding:12px 16px;min-width:260px;max-width:320px;
    box-shadow:0 4px 20px rgba(0,0,0,.3);
    font-family:'Noto Naskh Arabic',serif;direction:rtl;cursor:pointer;
    animation:notifIn .3s ease;position:relative;
    transition:opacity .3s ease;`;
  t.innerHTML = `
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
      <div onclick="window.location.href='${url || BASE + '/html/messages.html'}'" style="flex:1">
        <div style="font-weight:700;font-size:14px;margin-bottom:3px">${title}</div>
        <div style="font-size:12.5px;opacity:.85">${body}</div>
      </div>
      <button style="background:none;border:none;color:rgba(255,255,255,.6);font-size:16px;cursor:pointer;padding:0;line-height:1;flex-shrink:0"
        onclick="this.closest('[data-notif]').remove()">✕</button>
    </div>`;
  t.setAttribute('data-notif', '1');
  if (!document.getElementById('notif-style')) {
    const s = document.createElement('style');
    s.id = 'notif-style';
    s.textContent = '@keyframes notifIn{from{transform:translateY(-10px);opacity:0}to{transform:translateY(0);opacity:1}}';
    document.head.appendChild(s);
  }
  t.dataset.convUrl = url || '';
  const toastId = Date.now().toString() + Math.random().toString(36).slice(2);
  t.dataset.toastId = toastId;
  const stored = JSON.parse(localStorage.getItem('pendingToasts') || '[]');
  const isDup = stored.some(s => s.url === url && s.title === title);
  if (!isDup) {
    stored.push({ title, body, url, id: toastId });
    localStorage.setItem('pendingToasts', JSON.stringify(stored));
  }
  t.querySelector('button').addEventListener('click', () => {
    const arr = JSON.parse(localStorage.getItem('pendingToasts') || '[]');
    localStorage.setItem('pendingToasts', JSON.stringify(arr.filter(s => s.id !== toastId)));
  });
  container.appendChild(t);
}

function restorePendingToasts() {
  const stored = JSON.parse(localStorage.getItem('pendingToasts') || '[]');
  stored.forEach(item => {
    const container = getToastContainer();
    const t = document.createElement('div');
    t.style.cssText = `background:#1a4a2e;color:#fff;border-radius:12px;padding:12px 16px;min-width:260px;max-width:320px;box-shadow:0 4px 20px rgba(0,0,0,.3);font-family:'Noto Naskh Arabic',serif;direction:rtl;cursor:pointer;animation:notifIn .3s ease;position:relative;transition:opacity .3s ease;`;
    t.innerHTML = `<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px"><div onclick="window.location.href='${item.url||''}'" style="flex:1"><div style="font-weight:700;font-size:14px;margin-bottom:3px">${item.title}</div><div style="font-size:12.5px;opacity:.85">${item.body}</div></div><button style="background:none;border:none;color:rgba(255,255,255,.6);font-size:16px;cursor:pointer;padding:0;line-height:1;flex-shrink:0" id="close-${item.id}">✕</button></div>`;
    t.setAttribute('data-notif', '1');
    t.dataset.convUrl = item.url || '';
    t.dataset.toastId = item.id;
    container.appendChild(t);
    document.getElementById('close-' + item.id)?.addEventListener('click', () => {
      const arr = JSON.parse(localStorage.getItem('pendingToasts') || '[]');
      localStorage.setItem('pendingToasts', JSON.stringify(arr.filter(s => s.id !== item.id)));
      t.remove();
    });
  });
}

async function showBrowserNotif(title, body) {
  if (!('Notification' in window)) return;
  if (Notification.permission === 'default') await Notification.requestPermission();
  if (Notification.permission === 'granted') {
    new Notification(title, {
      body,
      icon: window.location.origin + BASE + '/logo.png',
      badge: window.location.origin + BASE + '/favicon.ico',
      dir: 'rtl',
      lang: 'ar',
      tag: 'mateen-msg',
      renotify: true,
    });
  }
}

/** Legacy Firestore realtime listeners — never called when USE_LARAVEL_API=true */
async function startListening(userId) {
  if (useApi()) return;
  const {
    collection, query, where, orderBy, onSnapshot, doc, getDoc,
  } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const db = await getLegacyFirestore();

  if (notifUnsub) { notifUnsub(); notifUnsub = null; }

  const q = query(
    collection(db, 'conversations'),
    where('participants', 'array-contains', userId)
  );

  let firstLoad = true;
  const lastSeen = {};

  function updateMsgBadge(snap) {
    let total = 0;
    snap.docs.forEach(d => {
      const data = d.data();
      const flat = Number(data[`unread.${userId}`] ?? 0);
      const nested = Number(data.unread?.[userId] ?? 0);
      total += Math.max(flat, nested);
    });
    ['navMsgBadge', 'sidebarMsgBadge'].forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      if (total > 0) {
        el.textContent = total > 9 ? '9+' : total;
        el.classList.remove('d-none');
      } else {
        el.classList.add('d-none');
      }
    });
  }

  notifUnsub = onSnapshot(q, snap => {
    updateMsgBadge(snap);
    if (firstLoad) {
      snap.docs.forEach(d => { lastSeen[d.id] = d.data().lastAt?.seconds || 0; });
      firstLoad = false;
      return;
    }
    snap.docChanges().forEach(async change => {
      if (change.type !== 'modified' && change.type !== 'added') return;
      const data = change.doc.data();
      const convId = change.doc.id;
      const lastMsg = data.lastMsg || '';
      const lastAt = data.lastAt?.seconds || 0;
      const nested = data.unread?.[userId];
      const flat = data[`unread.${userId}`];
      const unread = Math.max(Number(nested || 0), Number(flat || 0));
      void unread;
      const lastSenderId = data.lastSenderId || '';
      const isNewMsg = lastAt > (lastSeen[convId] || 0);
      const notFromMe = lastSenderId !== '' && lastSenderId !== userId;
      const hasContent = lastMsg.trim() !== '';
      const isReadEvent = (Number(data[`unread.${userId}`] || 0) === 0 && Number(data.unread?.[userId] || 0) === 0) && !isNewMsg;
      if (isNewMsg && notFromMe && hasContent && !isReadEvent) {
        lastSeen[convId] = lastAt;
        const onMsgsPage = window.location.pathname.includes('messages.html');
        playSound();
        let senderName = 'رسالة جديدة';
        try {
          const senderSnap = await getDoc(doc(db, 'users', lastSenderId));
          if (senderSnap.exists()) senderName = senderSnap.data().name || senderName;
        } catch (_) {}
        const notifTitle = `💬 ${senderName}`;
        pushToSW(userId, notifTitle, lastMsg, window.location.origin + BASE + '/html/messages.html', convId);
        if (!onMsgsPage) {
          showNotifToast(notifTitle, lastMsg, BASE + '/html/messages.html');
          showBrowserNotif(notifTitle, lastMsg);
        }
      }
    });
  });

  if (newsUnsub) { newsUnsub(); newsUnsub = null; }
  let newsFirstLoad = true;
  newsUnsub = onSnapshot(
    query(collection(db, 'news'), orderBy('createdAt', 'desc')),
    snap => {
      if (newsFirstLoad) { newsFirstLoad = false; return; }
      snap.docChanges().forEach(change => {
        if (change.type !== 'added') return;
        const n = change.doc.data();
        const onNewsPage = window.location.pathname.includes('news.html');
        playSound();
        pushToSW(userId, '📢 ' + (n.title || 'خبر جديد'), n.body?.slice(0, 80) || '', 'https://mateenweb.github.io/Mateen/html/news.html');
        if (!onNewsPage) {
          showNotifToast('📢 ' + (n.title || 'خبر جديد'), n.body ? n.body.slice(0, 80) : '', '/Mateen/html/news.html');
          showBrowserNotif('📢 ' + (n.title || 'خبر جديد — متين'), n.body?.slice(0, 80) || '');
        }
      });
    }
  );
}

let audioUnlocked = false;
function unlockAudio() {
  if (audioUnlocked) return;
  audioUnlocked = true;
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    ctx.resume();
  } catch (_) {}
}
document.addEventListener('click', unlockAudio, { once: true });
document.addEventListener('touchstart', unlockAudio, { once: true });

async function saveFCMToken(userId) {
  try {
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') return;

    const VAPID_KEY = 'BAMZ2n5lXHUV_qZnniDhTbJZTAI2uqHnJai6ukrnNIZhIc-8-wgwci_CaDpcH25oacehhSYScFgk14XIp7aZJ2c';
    const swReg = await navigator.serviceWorker.ready;
    const { getMessaging, getToken: getFcmToken } = await import(
      "https://www.gstatic.com/firebasejs/12.13.0/firebase-messaging.js"
    );
    const messaging = getMessaging(await getFirebaseApp());
    const token = await getFcmToken(messaging, {
      vapidKey: VAPID_KEY,
      serviceWorkerRegistration: swReg,
    });
    if (!token) return;

    if (useApi()) {
      console.log('[Notif] registering FCM token via Laravel API');
      await api.devices.register({ fcm_token: token, platform: 'web' });
      return;
    }

    const { doc, updateDoc } = await import(
      "https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js"
    );
    const db = await getLegacyFirestore();
    await updateDoc(doc(db, 'users', userId), { [`fcmTokens.${token}`]: true });
    console.log('[Notif] FCM token saved to Firestore');
  } catch (e) {
    console.warn('[Notif] FCM token error:', e);
  }
}

async function registerApiDeviceIfPossible(userId) {
  if (!('Notification' in window) || !('serviceWorker' in navigator)) return;
  try {
    await saveFCMToken(userId);
  } catch (e) {
    console.info('[Notif] API device registration skipped:', e.message);
  }
}

async function showMissedNotifications(userId) {
  if (useApi()) return;
  try {
    const { collection, query, orderBy, getDocs, deleteDoc } = await import(
      "https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js"
    );
    const db = await getLegacyFirestore();
    const pendingSnap = await getDocs(
      query(collection(db, 'notifications', userId, 'pending'), orderBy('createdAt', 'asc'))
    );
    if (pendingSnap.empty) return;
    pendingSnap.forEach(d => {
      const n = d.data();
      showNotifToast(n.title || 'إشعار جديد', n.body || '', n.url || '');
      playSound();
      deleteDoc(d.ref).catch(() => {});
    });
  } catch (e) {
    console.warn('[Notif] missed notifications error:', e);
  }
}

async function listenAdminNotifications(userId) {
  if (useApi()) return;
  const { collection, query, where, orderBy, onSnapshot } = await import(
    "https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js"
  );
  const db = await getLegacyFirestore();
  const q = query(
    collection(db, 'userNotifications', userId, 'items'),
    where('read', '==', false),
    orderBy('createdAt', 'asc')
  );
  onSnapshot(q, snap => {
    snap.docChanges().forEach(change => {
      if (change.type !== 'added') return;
      const n = change.doc.data();
      showNotifToast(n.title || 'إشعار جديد', n.body || '', n.url || '');
      playSound();
      change.doc.ref.update({ read: true }).catch(() => {});
    });
  });
}

if (useApi()) {
  restorePendingToasts();
  const apiUser = getStoredUser();
  const uid = apiUser?.id || apiUser?.data?.id;
  if (getToken() && uid) {
    console.info('[Notif] Laravel API mode — FCM only (no Firestore listeners)');
    registerApiDeviceIfPossible(String(uid));
  }
} else {
  (async () => {
    const { getAuth, onAuthStateChanged } = await import(
      "https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js"
    );
    const auth = getAuth(await getFirebaseApp());
    onAuthStateChanged(auth, user => {
      if (user) {
        console.log('[Notif] user logged in:', user.uid);
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
          navigator.serviceWorker.controller.postMessage({ type: 'SET_USER', uid: user.uid });
        }
        startListening(user.uid);
        showMissedNotifications(user.uid);
      } else {
        console.log('[Notif] no user');
        if (notifUnsub) { notifUnsub(); notifUnsub = null; }
        if (newsUnsub) { newsUnsub(); newsUnsub = null; }
      }
    });
  })();
}

export async function initNotifications(userId) {
  restorePendingToasts();
  if (!userId) return;
  if (useApi()) {
    await registerApiDeviceIfPossible(String(userId));
    return;
  }
}

export async function initAdminNotifications(userId, role) {
  if (!userId || useApi()) return;
  if (role === 'admin') await listenAdminNotifications(userId);
}

export { showNotifToast as showToast };

export async function deletePendingNotificationsForConv(convId) {
  if (!convId) return;
  if (useApi()) {
    dismissToastForConv('messages.html');
    return 0;
  }
  try {
    const { collectionGroup, query, where, getDocs, deleteDoc } = await import(
      "https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js"
    );
    const db = await getLegacyFirestore();
    const snap = await getDocs(
      query(collectionGroup(db, 'pending'), where('convId', '==', convId))
    );
    await Promise.all(snap.docs.map(d => deleteDoc(d.ref).catch(() => {})));
    return snap.docs.length;
  } catch (e) {
    console.warn('[Notif] deletePendingNotificationsForConv failed:', e);
    return 0;
  }
}

export function dismissToastForConv(url) {
  document.querySelectorAll('[data-notif][data-conv-url]').forEach(t => {
    if (t.dataset.convUrl && t.dataset.convUrl.includes(url)) {
      t.style.opacity = '0';
      setTimeout(() => t.remove(), 300);
    }
  });
  const stored = JSON.parse(localStorage.getItem('pendingToasts') || '[]');
  localStorage.setItem('pendingToasts', JSON.stringify(stored.filter(s => !(s.url || '').includes(url))));
}

void initialized;
