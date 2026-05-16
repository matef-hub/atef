import { CONFIG } from './config';

const PANEL_CONTROL_SELECTOR = 'input, select, textarea';

export const getExt = f => (f && typeof f === 'string' && f.match(/\.([^.]+)$/)?.[1]?.toLowerCase()) || '';

export const getExtFromUrl = url => {
  if (!url || typeof url !== 'string') return '';

  try {
    return getExt(new URL(url, window.location.origin).pathname);
  } catch {
    return getExt(url.split('#')[0].split('?')[0]);
  }
};

export const formatDateYmd = d =>
  `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

export const toggleControlState = (field, disabled) => {
  field.disabled = disabled;

  if (field._flatpickr?.altInput) field._flatpickr.altInput.disabled = disabled;

  if (CONFIG.hasjQuery && field.classList?.contains('select2-hidden-accessible')) {
    window.jQuery(field).prop('disabled', disabled).trigger('change.select2');
  }
};

export const togglePanelControls = (panel, enabled) =>
  panel.querySelectorAll(PANEL_CONTROL_SELECTOR).forEach(field => toggleControlState(field, !enabled));

export const cleanupPreviewUrl = card => {
  if (!card) return;

  const url = card.dataset.previewObjectUrl;

  if (url) {
    if (url.startsWith('blob:')) {
      URL.revokeObjectURL(url);
    }

    delete card.dataset.previewObjectUrl;
  }
};

export const normalizeStorageUrl = url => {
  if (!url || url.startsWith('blob:') || url.startsWith('data:')) return url;

  try {
    const target = new URL(url);
    const { origin, protocol, hostname, port } = window.location;

    if (target.origin === origin) return url;

    target.protocol = protocol;
    target.hostname = hostname;
    target.port = port;

    return target.toString();
  } catch {
    return url;
  }
};

export const buildPdfViewerUrl = url =>
  url && !url.includes('#') && !url.startsWith('blob:') ? `${url}#toolbar=1&navpanes=0&scrollbar=0&view=FitH` : url;

export const clearPreviewStage = card => {
  if (!card) return;

  const frame = card.querySelector('[data-pdf-frame]');
  const empty = card.querySelector('[data-pdf-empty]');
  const loading = card.querySelector('[data-pdf-loading]');
  const unsupported = card.querySelector('[data-pdf-unsupported]');
  const img = card.querySelector('[data-image-preview]');

  if (frame) {
    frame.src = '';
    frame.classList.add('d-none');
  }

  if (empty) empty.classList.add('d-none');
  if (loading) loading.classList.add('d-none');
  if (unsupported) unsupported.classList.add('d-none');

  if (img) {
    img.src = '';
    img.classList.add('d-none');
  }
};

export const fetchAsObjectUrl = async (url, forceMime = null, signal = null) => {
  const controller = new AbortController();
  const abortFetch = () => controller.abort();
  const timeout = setTimeout(abortFetch, 15000);

  if (signal) {
    if (signal.aborted) {
      controller.abort();
    } else {
      signal.addEventListener('abort', abortFetch, { once: true });
    }
  }

  try {
    const response = await fetch(normalizeStorageUrl(url), {
      credentials: 'same-origin',
      signal: controller.signal
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const raw = await response.blob();
    const blob = forceMime ? new Blob([raw], { type: forceMime }) : raw;

    return URL.createObjectURL(blob);
  } finally {
    clearTimeout(timeout);

    if (signal) {
      signal.removeEventListener('abort', abortFetch);
    }
  }
};

export const debounce = (fn, wait = 150) => {
  let timeoutId = null;

  function debounced(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), wait);
  }

  debounced.cancel = () => {
    clearTimeout(timeoutId);
    timeoutId = null;
  };

  return debounced;
};

export default {
  getExt,
  getExtFromUrl,
  formatDateYmd,
  toggleControlState,
  togglePanelControls,
  cleanupPreviewUrl,
  normalizeStorageUrl,
  buildPdfViewerUrl,
  clearPreviewStage,
  fetchAsObjectUrl,
  debounce
};
