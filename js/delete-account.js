/**
 * delete-account.js
 * Laravel mode → DELETE /users/{id} via AccountDeletionService.
 * Legacy → Firestore + Cloud Function deleteAuthUser.
 */
import { FIREBASE_CONFIG, USE_LARAVEL_API } from "./config.js";
import { isLaravelApi } from "./api.js";
import { deleteCurrentAccount, useLaravelBackend } from "./session.js";

const useApi = () => USE_LARAVEL_API === true || isLaravelApi() || useLaravelBackend();

export async function fullDeleteUser(uid) {
  if (useApi()) {
    await deleteCurrentAccount();
    return { ok: true, mode: 'laravel' };
  }

  const { initializeApp, getApps, getApp } = await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-app.js");
  const { getFirestore, doc, getDoc, getDocs, deleteDoc, collection, query, where } =
    await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-firestore.js");
  const { getFunctions, httpsCallable } =
    await import("https://www.gstatic.com/firebasejs/12.13.0/firebase-functions.js");

  const app  = getApps().length ? getApp() : initializeApp(FIREBASE_CONFIG);
  const db   = getFirestore(app);
  const fns  = getFunctions(app);
  const deleteAuthUserFn = httpsCallable(fns, "deleteAuthUser");

  async function deleteSubcollection(parentRef, subcollName) {
    const snap = await getDocs(collection(parentRef, subcollName));
    await Promise.all(snap.docs.map(d => deleteDoc(d.ref)));
  }

  const errors = [];
  try { await deleteDoc(doc(db, 'users', uid)); } catch (e) { errors.push('users: ' + e.message); }
  try {
    const studentRef = doc(db, 'students', uid);
    const studentSnap = await getDoc(studentRef);
    if (studentSnap.exists()) {
      await deleteSubcollection(studentRef, 'sessions');
      await deleteSubcollection(studentRef, 'grades');
      await deleteDoc(studentRef);
    }
  } catch (e) { errors.push('students: ' + e.message); }
  try {
    const convSnap = await getDocs(
      query(collection(db, 'conversations'), where('participants', 'array-contains', uid))
    );
    await Promise.all(convSnap.docs.map(async convDoc => {
      await deleteSubcollection(convDoc.ref, 'messages');
      await deleteDoc(convDoc.ref);
    }));
  } catch (e) { errors.push('conversations: ' + e.message); }
  try {
    await deleteAuthUserFn({ uid });
  } catch (e) { errors.push('auth: ' + e.message); }

  if (errors.length) console.warn('[delete-account]', errors);
  return { ok: errors.length === 0, errors, mode: 'firebase' };
}
