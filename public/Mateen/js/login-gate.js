(function () {
  var TOKEN_KEY = 'mateen_api_token';
  var USER_KEY = 'mateen_api_user';
  var overlay = document.getElementById('loginGateModal');
  if (!overlay) return;
  if (localStorage.getItem(TOKEN_KEY)) return;

  overlay.classList.add('open');
  document.body.classList.add('login-gate-locked');

  var emailEl = document.getElementById('loginGateEmail');
  var passEl = document.getElementById('loginGatePassword');
  var btn = document.getElementById('loginGateSubmit');
  var err = document.getElementById('loginGateError');

  function showError(msg) {
    if (!err) return;
    err.textContent = msg;
    err.classList.add('show');
  }

  async function submit() {
    var email = (emailEl && emailEl.value || '').trim();
    var password = passEl && passEl.value || '';
    if (!email || !password) {
      showError('يرجى إدخال البريد وكلمة المرور');
      return;
    }
    err && err.classList.remove('show');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'جارٍ الدخول...';
    }
    try {
      var res = await fetch('/api/v1/auth/login', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, password: password }),
      });
      var data = {};
      try { data = await res.json(); } catch (e) { data = {}; }
      if (!res.ok) {
        throw new Error(data.message || (data.errors && data.errors.email && data.errors.email[0]) || 'بيانات الدخول غير صحيحة');
      }
      if (!data.token) throw new Error('تعذر إنشاء الجلسة');
      localStorage.setItem(TOKEN_KEY, data.token);
      localStorage.setItem(USER_KEY, JSON.stringify(data.user || {}));
      if (data.user && data.user.role) localStorage.setItem('userRole', data.user.role);
      window.location.reload();
    } catch (e) {
      showError(e.message || 'تعذر تسجيل الدخول');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-login"></i> دخول';
      }
    }
  }

  if (btn) btn.addEventListener('click', submit);
  [emailEl, passEl].forEach(function (el) {
    if (!el) return;
    el.addEventListener('keydown', function (ev) {
      if (ev.key === 'Enter') submit();
    });
  });
})();
