/**
 * App session — Laravel is the system of record when USE_LARAVEL_API is true.
 * Firebase Auth/Firestore must not be used for data in that mode.
 * Firebase is reserved for FCM push (messaging SDK) only.
 */
import { USE_LARAVEL_API } from './config.js';
import { api, clearSession, getStoredUser, getToken, isLaravelApi, setSession } from './api.js';

export function useLaravelBackend() {
  return USE_LARAVEL_API === true || isLaravelApi();
}

/** Normalize Laravel user resource (top-level or { data }) into a common session shape. */
export function normalizeUser(raw) {
  const u = raw?.data && typeof raw.data === 'object' && !Array.isArray(raw.data) ? raw.data : raw;
  if (!u || typeof u !== 'object') return null;
  return {
    id: u.id,
    uid: String(u.id),
    email: u.email || '',
    name: u.name || u.email?.split('@')[0] || 'مستخدم',
    role: u.role || 'student',
    subject_id: u.subject_id ?? null,
    subject: u.subject?.slug || u.subject?.title || u.subject || '',
    theme_id: u.theme_id ?? null,
    ornament_id: u.ornament_id ?? null,
    is_active: u.is_active !== false,
    status: u.is_active === false ? 'suspended' : 'active',
    must_reset_password: !!u.must_reset_password,
    linkedStudentId: u.linked_student_id || u.linkedStudentId || null,
    raw: u,
  };
}

export function isLoggedInLocal() {
  if (useLaravelBackend()) {
    return !!(getToken() && getStoredUser());
  }
  try {
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && key.startsWith('firebase:authUser:')) {
        const val = localStorage.getItem(key);
        if (val && val !== 'null') return true;
      }
    }
  } catch (_) {}
  return false;
}

/**
 * Resolve current Laravel session (from storage, optionally refreshed via /auth/me).
 * Returns null if not logged in / token invalid.
 */
export async function resolveLaravelSession({ refresh = true } = {}) {
  if (!getToken()) return null;
  let user = normalizeUser(getStoredUser());
  if (refresh) {
    try {
      const me = await api.me();
      user = normalizeUser(me);
      if (user) setSession(getToken(), user.raw || user);
    } catch (e) {
      if (e.status === 401) {
        clearSession();
        return null;
      }
      // keep cached user on network blips
    }
  }
  return user;
}

/**
 * Unified session listener.
 * Laravel mode: invokes once with session or null (no Firebase Auth).
 * Legacy mode: caller should use Firebase onAuthStateChanged instead (callback not used).
 */
export async function onAppSession(callback) {
  if (!useLaravelBackend()) {
    callback(null, { mode: 'firebase' });
    return () => {};
  }
  const session = await resolveLaravelSession({ refresh: true });
  callback(session, { mode: 'laravel' });
  return () => {};
}

export async function logoutApp(redirectTo = '../html/login.html') {
  if (useLaravelBackend()) {
    try { await api.logout(); } catch (_) {}
    clearSession();
    localStorage.removeItem('userRole');
    localStorage.removeItem('userSubjectId');
    window.location.href = redirectTo;
    return;
  }
  // Legacy Firebase logout is handled by the page (signOut(auth)).
}

export async function deleteCurrentAccount() {
  if (!useLaravelBackend()) {
    throw new Error('Firebase delete path required when USE_LARAVEL_API is false');
  }
  const user = await resolveLaravelSession({ refresh: false });
  if (!user?.id) throw new Error('لا توجد جلسة نشطة');
  await api.deleteUser(user.id);
  clearSession();
}
