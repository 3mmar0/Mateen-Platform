(function () {
  var TOKEN_KEY = 'mateen_api_token';
  var PROTECTED = ['/courses', '/library'];

  function isLoggedIn() {
    try { return !!localStorage.getItem(TOKEN_KEY); } catch (e) { return false; }
  }

  function pathOf(href) {
    try {
      return new URL(href, location.origin).pathname.replace(/\/$/, '') || '/';
    } catch (e) {
      return href;
    }
  }

  function isProtectedPath(pathname) {
    var p = (pathname || '').replace(/\/$/, '') || '/';
    return PROTECTED.indexOf(p) !== -1;
  }

  window.showLoginRequiredModal = function (nextPath) {
    var existing = document.getElementById('loginPromptModal');
    if (existing) existing.remove();
    var next = nextPath || '/courses';
    var modal = document.createElement('div');
    modal.id = 'loginPromptModal';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:2147483647;';
    window.scrollTo(0, 0);
    document.body.style.overflow = 'hidden';
    modal.innerHTML =
      '<div style="position:fixed;top:80px;left:50%;transform:translateX(-50%);background:#fff;border-radius:20px;padding:36px 32px;max-width:360px;width:90%;text-align:center;font-family:inherit;box-shadow:0 18px 50px rgba(0,0,0,.2);">' +
        '<div style="font-size:42px;margin-bottom:12px;">🔒</div>' +
        '<div style="font-family:Amiri,serif;font-size:22px;color:var(--green-dark,#3d2b1f);font-weight:700;margin-bottom:10px;">يلزم تسجيل الدخول</div>' +
        '<p style="color:var(--text-mid,#6b6b6b);font-size:14px;line-height:1.7;margin-bottom:24px;">هذا القسم متاح للطالبات المسجلات فقط. سجّلي دخولك للاستمرار.</p>' +
        '<div style="display:flex;gap:10px;justify-content:center;">' +
          '<a href="/login?next=' + encodeURIComponent(next) + '" style="background:var(--green-dark,#3d2b1f);color:#fff;padding:10px 24px;border-radius:10px;text-decoration:none;font-size:14px;font-weight:600;">تسجيل الدخول</a>' +
          '<button type="button" id="loginPromptCancel" style="background:var(--beige,#f3ead8);border:1px solid var(--border,#e4d9c5);color:var(--text-mid,#5c4a3a);padding:10px 20px;border-radius:10px;font-family:inherit;font-size:14px;cursor:pointer;">إلغاء</button>' +
        '</div>' +
      '</div>';
    document.body.insertBefore(modal, document.body.firstChild);
    function closePrompt() {
      modal.remove();
      document.body.style.overflow = '';
      if (isProtectedPath(location.pathname)) location.replace('/');
    }
    document.getElementById('loginPromptCancel').onclick = closePrompt;
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closePrompt();
    });
  };

  document.addEventListener('click', function (e) {
    if (isLoggedIn()) return;
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href') || '';
    if (!isProtectedPath(pathOf(href))) return;
    e.preventDefault();
    e.stopPropagation();
    window.showLoginRequiredModal(pathOf(href));
  }, true);

  if (!isLoggedIn() && isProtectedPath(location.pathname)) {
    document.documentElement.style.visibility = 'hidden';
    document.addEventListener('DOMContentLoaded', function () {
      document.documentElement.style.visibility = 'visible';
      Array.prototype.forEach.call(document.body.children, function (el) {
        if (el.id !== 'loginPromptModal') el.style.display = 'none';
      });
      window.showLoginRequiredModal(location.pathname.replace(/\/$/, '') || '/');
    });
  }
})();
