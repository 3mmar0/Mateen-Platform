renderNav('news.html')

import { getAuth, onAuthStateChanged as _onAuth }
  from "https://www.gstatic.com/firebasejs/12.13.0/firebase-auth.js";
import { getApps, getApp, initializeApp as _init }
  from "https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js";
import { FIREBASE_CONFIG as _CFG, USE_LARAVEL_API } from "./config.js";
import { getStoredUser, isLaravelApi } from "./api.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();

(function markNewsSeen() {
  if (useApi()) {
    const user = getStoredUser();
    if (user?.id) localStorage.setItem(`news_last_seen_${user.id}`, Date.now().toString());
    return;
  }
  const _app  = getApps().length ? getApp() : _init(_CFG);
  const _auth = getAuth(_app);
  _onAuth(_auth, user => {
    if (!user) return;
    localStorage.setItem(`news_last_seen_${user.uid}`, Date.now().toString());
  });
})();
