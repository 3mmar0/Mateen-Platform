

// Material CRUD for teacher subject pages lives in courses-firebase.js (API-gated via USE_LARAVEL_API).

import { bootTeacherPageAuth, registerTeacherContactFirebase, sendTeacherContactMessage, useApi } from "./teacher-contact-shared.js";

const TEACHER_ID = "ithraiyat";
const SUBJECT_AR = "الإثرائيات";

bootTeacherPageAuth();
if (!useApi()) registerTeacherContactFirebase(TEACHER_ID, SUBJECT_AR);

window.sendMessage = async () => {
  sendTeacherContactMessage({
    subjectAr: SUBJECT_AR,
    topic: document.getElementById('msgTopic').value,
    name: document.getElementById('msgName').value.trim(),
    phone: document.getElementById('msgPhone').value.trim(),
    body: document.getElementById('msgBody').value.trim(),
    errEl: document.getElementById('errMsg'),
    errTx: document.getElementById('errText'),
    btn: document.getElementById('sendBtn'),
    successEl: document.getElementById('successMsg'),
  });
};
