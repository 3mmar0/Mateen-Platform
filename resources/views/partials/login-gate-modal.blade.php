<div class="login-gate-overlay" id="loginGateModal" role="dialog" aria-modal="true" aria-labelledby="loginGateTitle">
  <div class="login-gate-box" onclick="event.stopPropagation()">
    <img src="/Mateen/logo.png" alt="متين" class="login-gate-logo"/>
    <h2 id="loginGateTitle">سجّلي الدخول أولاً</h2>
    <p>محتوى هذه الصفحة متاح للطالبات والمعلمات المسجلات فقط</p>
    <div class="login-gate-error" id="loginGateError"></div>
    <label class="login-gate-label">البريد الإلكتروني</label>
    <input class="login-gate-input" id="loginGateEmail" type="email" dir="ltr" autocomplete="username" placeholder="email@example.com"/>
    <label class="login-gate-label">كلمة المرور</label>
    <input class="login-gate-input" id="loginGatePassword" type="password" dir="ltr" autocomplete="current-password" placeholder="••••••••"/>
    <button type="button" class="login-gate-submit" id="loginGateSubmit">
      <i class="ti ti-login"></i> دخول
    </button>
    <div class="login-gate-links">
      <a href="/login">صفحة تسجيل الدخول الكاملة</a>
      <a href="/">العودة للرئيسية</a>
    </div>
  </div>
</div>
<style>
.login-gate-overlay {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 20000;
  background: rgba(20, 40, 28, 0.72);
  backdrop-filter: blur(6px);
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.login-gate-overlay.open { display: flex; }
.login-gate-box {
  background: #fff;
  border-radius: 18px;
  padding: 28px 24px 22px;
  width: 100%;
  max-width: 420px;
  text-align: center;
  box-shadow: 0 24px 64px rgba(0,0,0,0.28);
  font-family: inherit;
}
.login-gate-logo {
  width: 56px; height: 56px; border-radius: 50%;
  object-fit: cover; border: 2px solid #c9a227; margin-bottom: 10px;
}
.login-gate-box h2 {
  font-family: Amiri, serif;
  color: #1a4a2e;
  font-size: 22px;
  margin: 0 0 6px;
}
.login-gate-box p {
  color: #6b6b6b;
  font-size: 13px;
  margin: 0 0 18px;
  line-height: 1.7;
}
.login-gate-label {
  display: block;
  text-align: right;
  font-size: 12px;
  font-weight: 600;
  color: #4a4a4a;
  margin: 0 0 6px;
}
.login-gate-input {
  width: 100%;
  box-sizing: border-box;
  border: 1.5px solid #e4d9c5;
  background: #faf6ee;
  border-radius: 10px;
  padding: 11px 12px;
  font-size: 14px;
  margin-bottom: 12px;
  font-family: inherit;
}
.login-gate-input:focus { outline: none; border-color: #1a4a2e; background: #fff; }
.login-gate-submit {
  width: 100%;
  margin-top: 6px;
  background: #1a4a2e;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 12px;
  font-family: inherit;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.login-gate-submit:disabled { opacity: .7; cursor: wait; }
.login-gate-error {
  display: none;
  background: #fde8e8;
  color: #c0392b;
  border-radius: 8px;
  padding: 8px 10px;
  font-size: 12px;
  margin-bottom: 12px;
}
.login-gate-error.show { display: block; }
.login-gate-links {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-top: 14px;
  font-size: 12px;
}
.login-gate-links a { color: #1a4a2e; text-decoration: none; }
body.login-gate-locked { overflow: hidden; }
</style>
<script src="/Mateen/js/login-gate.js?v=20260819b" defer></script>
