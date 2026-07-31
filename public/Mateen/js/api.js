/**
 * Mateen Laravel API client — used when USE_LARAVEL_API is true in config.js
 */
import { API_BASE_URL, USE_LARAVEL_API } from './config.js';

const TOKEN_KEY = 'mateen_api_token';
const USER_KEY = 'mateen_api_user';

export function isLaravelApi() {
  return USE_LARAVEL_API === true;
}

export function getToken() {
  return localStorage.getItem(TOKEN_KEY) || '';
}

export function getStoredUser() {
  try {
    return JSON.parse(localStorage.getItem(USER_KEY) || 'null');
  } catch {
    return null;
  }
}

export function setSession(token, user) {
  localStorage.setItem(TOKEN_KEY, token);
  localStorage.setItem(USER_KEY, JSON.stringify(user || {}));
  if (user?.role) localStorage.setItem('userRole', user.role);
  if (user?.subject_id != null) localStorage.setItem('userSubjectId', String(user.subject_id));
}

export function clearSession() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
}

async function request(path, { method = 'GET', body, auth = true } = {}) {
  const headers = { Accept: 'application/json' };
  if (body !== undefined) headers['Content-Type'] = 'application/json';
  if (auth) {
    const token = getToken();
    if (token) headers.Authorization = `Bearer ${token}`;
  }
  const res = await fetch(`${API_BASE_URL}${path}`, {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });
  if (res.status === 204) return null;
  let data = null;
  try {
    data = await res.json();
  } catch {
    data = null;
  }
  if (!res.ok) {
    const err = new Error(data?.message || 'طلب غير ناجح');
    err.status = res.status;
    err.data = data;
    err.code = data?.errors ? 'validation' : `http_${res.status}`;
    throw err;
  }
  return data;
}

export const api = {
  register: (payload) => request('/auth/register', { method: 'POST', body: payload, auth: false }),
  login: (payload) => request('/auth/login', { method: 'POST', body: payload, auth: false }),
  logout: () => request('/auth/logout', { method: 'POST' }),
  me: () => request('/auth/me'),
  forgotPassword: (email) => request('/auth/password/forgot', { method: 'POST', body: { email }, auth: false }),
  resetPassword: (payload) => request('/auth/password/reset', { method: 'POST', body: payload, auth: false }),
  deleteUser: (id) => request(`/users/${id}`, { method: 'DELETE' }),

  subjects: {
    list: () => request('/subjects'),
    get: (id) => request(`/subjects/${id}`),
    create: (body) => request('/subjects', { method: 'POST', body }),
    update: (id, body) => request(`/subjects/${id}`, { method: 'PATCH', body }),
    enroll: (id) => request(`/subjects/${id}/enrollments`, { method: 'POST' }),
    materials: (id) => request(`/subjects/${id}/materials`),
    addMaterial: (id, body) => request(`/subjects/${id}/materials`, { method: 'POST', body }),
  },
  materials: {
    update: (id, body) => request(`/materials/${id}`, { method: 'PATCH', body }),
    remove: (id) => request(`/materials/${id}`, { method: 'DELETE' }),
  },
  students: {
    list: (q = '') => request(`/students${q}`),
    create: (body) => request('/students', { method: 'POST', body }),
    bulk: (students) => request('/students/bulk', { method: 'POST', body: { students } }),
    update: (id, body) => request(`/students/${id}`, { method: 'PATCH', body }),
    export: (body) => request('/students/export', { method: 'POST', body }),
  },
  schedules: {
    list: () => request('/schedules'),
    create: (body) => request('/schedules', { method: 'POST', body }),
    update: (id, body) => request(`/schedules/${id}`, { method: 'PATCH', body }),
    remove: (id) => request(`/schedules/${id}`, { method: 'DELETE' }),
  },
  assignments: {
    list: (q = '') => request(`/assignments${q}`),
    create: (body) => request('/assignments', { method: 'POST', body }),
    submissions: (id) => request(`/assignments/${id}/submissions`),
    submit: (id, body) => request(`/assignments/${id}/submissions`, { method: 'POST', body }),
    grade: (id, body) => request(`/submissions/${id}`, { method: 'PATCH', body }),
  },
  library: {
    list: (section) => request(section ? `/library?section=${encodeURIComponent(section)}` : '/library'),
    create: (body) => request('/library', { method: 'POST', body }),
    update: (id, body) => request(`/library/${id}`, { method: 'PATCH', body }),
    remove: (id) => request(`/library/${id}`, { method: 'DELETE' }),
  },
  news: {
    list: () => request('/news'),
    create: (body) => request('/news', { method: 'POST', body }),
    update: (id, body) => request(`/news/${id}`, { method: 'PATCH', body }),
    remove: (id) => request(`/news/${id}`, { method: 'DELETE' }),
  },
  conversations: {
    list: () => request('/conversations'),
    create: (body) => request('/conversations', { method: 'POST', body }),
    messages: (id) => request(`/conversations/${id}/messages`),
    send: (id, body) => request(`/conversations/${id}/messages`, { method: 'POST', body }),
  },
  media: {
    signUpload: () => request('/media/sign-upload', { method: 'POST' }),
  },
  devices: {
    register: (body) => request('/devices', { method: 'POST', body }),
  },
  support: {
    users: () => request('/support/users'),
    setTheme: (id, body) => request(`/support/users/${id}/theme`, { method: 'PATCH', body }),
  },
  stats: {
    summary: (q = '') => request(`/stats/summary${q}`),
    export: (body) => request('/stats/export', { method: 'POST', body }),
  },
};

export default api;
