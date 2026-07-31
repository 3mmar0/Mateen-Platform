// ── رفع ملف/صورة على Cloudinary ──
import { USE_LARAVEL_API } from './config.js';
import { api, isLaravelApi } from './api.js';

const useApi = () => USE_LARAVEL_API === true || isLaravelApi();
const CLOUD_NAME    = 'dqqtznoqt';
const UPLOAD_PRESET = 'mateen_uploads';

function hasSignedParams(sign) {
  return sign?.cloud_name && sign?.api_key && sign?.timestamp != null && sign?.signature;
}

async function uploadWithPreset(file, resourceType) {
  const fd = new FormData();
  fd.append('file', file);
  fd.append('upload_preset', UPLOAD_PRESET);
  const res = await fetch(
    `https://api.cloudinary.com/v1_1/${CLOUD_NAME}/${resourceType}/upload`,
    { method: 'POST', body: fd }
  );
  const data = await res.json();
  if (!data.secure_url) throw new Error(data.error?.message || 'فشل رفع الملف');
  return data.secure_url;
}

async function uploadWithSignature(file, resourceType, sign) {
  const fd = new FormData();
  fd.append('file', file);
  fd.append('api_key', sign.api_key);
  fd.append('timestamp', String(sign.timestamp));
  fd.append('signature', sign.signature);
  if (sign.folder) fd.append('folder', sign.folder);
  const res = await fetch(
    `https://api.cloudinary.com/v1_1/${sign.cloud_name}/${resourceType}/upload`,
    { method: 'POST', body: fd }
  );
  const data = await res.json();
  if (!data.secure_url) throw new Error(data.error?.message || 'فشل رفع الملف');
  return data.secure_url;
}

/**
 * يرفع أي ملف (صورة، PDF، Word، إلخ) ويرجع رابط عام (secure_url) قابل للمشاركة.
 */
export async function uploadToCloudinary(file) {
  const isImage = (file.type || '').startsWith('image/');
  const resourceType = isImage ? 'image' : 'raw';

  if (useApi()) {
    try {
      const res = await api.media.signUpload();
      const sign = res?.data ?? res;
      if (hasSignedParams(sign)) {
        return await uploadWithSignature(file, resourceType, sign);
      }
      console.info('[cloud-upload] incomplete sign response — falling back to unsigned preset');
    } catch (e) {
      console.info('[cloud-upload] sign-upload unavailable — falling back to unsigned preset', e.message);
    }
  }

  return uploadWithPreset(file, resourceType);
}

export async function uploadBlobToCloudinary(blob, { resourceType = 'video' } = {}) {
  const file = blob instanceof File ? blob : new File([blob], 'upload.bin', { type: blob.type || 'application/octet-stream' });

  if (useApi()) {
    try {
      const res = await api.media.signUpload();
      const sign = res?.data ?? res;
      if (hasSignedParams(sign)) {
        return await uploadWithSignature(file, resourceType, sign);
      }
      console.info('[cloud-upload] incomplete sign response — falling back to unsigned preset');
    } catch (e) {
      console.info('[cloud-upload] sign-upload unavailable — falling back to unsigned preset', e.message);
    }
  }

  return uploadWithPreset(file, resourceType);
}
