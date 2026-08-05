<div class="reg-modal-overlay" id="reg-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="reg-modal-box">
    <div class="reg-modal-header">
      <div>
        <div class="hero-badge hero-badge-inline">📝 طلب تسجيل</div>
        <h2>التسجيل في برنامج متين العلمي</h2>
        <p>أدخلي بياناتك وسنتواصل معكِ في أقرب وقت</p>
      </div>
      <button class="reg-close" onclick="document.getElementById('reg-modal').classList.remove('open')">
        <i class="ti ti-x"></i>
      </button>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-12 col-sm-6">
        <label class="form-label-custom">الاسم الكامل *</label>
        <input class="form-input" id="regName" placeholder="اسمك الكريم" type="text"/>
      </div>
      <div class="col-12 col-sm-6">
        <label class="form-label-custom">رقم الجوال *</label>
        <input class="form-input" id="regPhone" dir="ltr" placeholder="05XXXXXXXX" type="text"/>
      </div>
      <div class="col-12 col-sm-6">
        <label class="form-label-custom">البريد الإلكتروني *</label>
        <input class="form-input" id="regEmail" dir="ltr" placeholder="email@example.com" type="email"/>
      </div>
      <div class="col-12 col-sm-6">
        <label class="form-label-custom">العمر</label>
        <input class="form-input" id="regAge" dir="ltr" placeholder="مثال: 22" type="number"/>
      </div>
      <div class="col-12 col-sm-6">
        <label class="form-label-custom">المستوى المطلوب *</label>
        <select class="form-input" id="regLevel">
          <option value="">اختاري المستوى</option>
          <option>المستوى الأول (مبتدئ)</option>
          <option>المستوى الثاني</option>
          <option>المستوى الثالث</option>
          <option>المستوى الرابع</option>
          <option>المستوى المتقدم</option>
        </select>
      </div>
      <div class="col-12 col-sm-6">
        <label class="form-label-custom">كيف عرفتِ البرنامج؟</label>
        <select class="form-input" id="regSource">
          <option value="">اختاري</option>
          <option>من صديقة أو معارف</option>
          <option>منصات التواصل الاجتماعي</option>
          <option>إعلان</option>
          <option>أخرى</option>
        </select>
      </div>
      <div class="col-12">
        <button class="contact-submit w-100 justify-content-center" onclick="submitReg(this)">
          <i class="ti ti-send"></i> إرسال طلب التسجيل
        </button>
      </div>
    </div>
  </div>
</div>
