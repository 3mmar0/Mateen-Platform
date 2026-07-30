/**
 * home-msg.js — عدادات الرسائل والأخبار
 * Laravel mode: poll API. Legacy: Firestore listeners.
 */
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { api, getStoredUser, getToken, isLaravelApi } from "./api.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

function setBadge(ids, count) {
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    if (count > 0) {
      el.textContent = count > 99 ? '99+' : String(count);
      el.classList.remove('d-none');
    } else {
      el.classList.add('d-none');
    }
  });
}

async function refreshApiBadges() {
  if (!getToken()) {
    setBadge(['navMsgBadge','sidebarMsgBadge'], 0);
    setBadge(['navNewsBadge','navNewsBadge2','sidebarNewsBadge'], 0);
    return;
  }
  const me = getStoredUser();
  const uid = me?.id || me?.data?.id;
  try {
    const res = await api.conversations.list();
    const list = res?.data || [];
    let total = 0;
    list.forEach(c => { total += Number(c.unread || 0); });
    setBadge(['navMsgBadge', 'sidebarMsgBadge'], total);
  } catch (e) {
    console.warn('[home-msg] messages badge API error:', e);
  }
  try {
    const lastSeenKey = `news_last_seen_${uid || 'api'}`;
    const lastSeen = parseInt(localStorage.getItem(lastSeenKey) || '0', 10);
    const newsRes = await api.news.list();
    const items = newsRes?.data || [];
    let count = 0;
    items.forEach(n => {
      const ts = n.published_at || n.created_at;
      const ms = ts ? Date.parse(ts) : 0;
      if (ms > lastSeen) count++;
    });
    setBadge(['navNewsBadge', 'navNewsBadge2', 'sidebarNewsBadge'], count);
  } catch (e) {
    console.warn('[home-msg] news badge API error:', e);
  }
}

async function bootFirebaseLegacy() {
  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const { getAuth, onAuthStateChanged } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js");
  const { getFirestore, collection, query, where, orderBy, getDocs, onSnapshot } =
    await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const app  = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  const auth = getAuth(app);
  const db   = getFirestore(app);

  onAuthStateChanged(auth, async user => {
    if (!user) {
      setBadge(['navMsgBadge','sidebarMsgBadge'], 0);
      setBadge(['navNewsBadge','navNewsBadge2','sidebarNewsBadge'], 0);
      return;
    }
    try {
      const convQ = query(
        collection(db, 'conversations'),
        where('participants', 'array-contains', user.uid)
      );
      onSnapshot(convQ, snap => {
        let total = 0;
        snap.forEach(d => {
          const data = d.data();
          const nested = data.unread?.[user.uid];
          const flat   = data[`unread.${user.uid}`];
          total += Math.max(Number(nested || 0), Number(flat || 0));
        });
        setBadge(['navMsgBadge', 'sidebarMsgBadge'], total);
      });
    } catch (e) {
      console.warn('[home-msg] messages badge error:', e);
    }
    try {
      const lastSeenKey = `news_last_seen_${user.uid}`;
      const lastSeen = parseInt(localStorage.getItem(lastSeenKey) || '0', 10);
      const newsSnap = await getDocs(query(collection(db, 'news'), orderBy('createdAt', 'desc')));
      let count = 0;
      newsSnap.forEach(d => {
        const ts = d.data().createdAt;
        if (ts && ts.toMillis() > lastSeen) count++;
      });
      setBadge(['navNewsBadge', 'navNewsBadge2', 'sidebarNewsBadge'], count);
    } catch (e) {
      console.warn('[home-msg] news badge error:', e);
    }
  });
}

if (useApi()) {
  refreshApiBadges();
  setInterval(refreshApiBadges, 30000);
} else {
  bootFirebaseLegacy();
}
