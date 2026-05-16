import { CONFIG, PREVIEW_IMAGE_EXTENSIONS } from './config';
import {
  buildPdfViewerUrl,
  cleanupPreviewUrl,
  clearPreviewStage,
  fetchAsObjectUrl,
  getExtFromUrl,
  normalizeStorageUrl
} from './helpers';

export function renderPdfPreview(card, url, temporary = false) {
  const frame = card?.querySelector('[data-pdf-frame]');
  if (!frame) return;

  cleanupPreviewUrl(card);
  clearPreviewStage(card);

  if (temporary) card.dataset.previewObjectUrl = url;

  frame.removeAttribute('sandbox');
  frame.src = buildPdfViewerUrl(url);
  frame.classList.remove('d-none');
  card.classList.add('is-previewing');
}

export function renderPdfPreviewDirect(card, frameUrl) {
  const frame = card?.querySelector('[data-pdf-frame]');
  if (!frame) return;

  clearPreviewStage(card);
  frame.removeAttribute('sandbox');
  frame.removeAttribute('src');

  requestAnimationFrame(() => {
    frame.src = frameUrl;
  });

  frame.classList.remove('d-none');
  card.classList.add('is-previewing');
}

export function renderPdfPreviewLoading(card) {
  const loading = card?.querySelector('[data-pdf-loading]');
  if (!loading) return;

  clearPreviewStage(card);
  loading.classList.remove('d-none');
  card.classList.add('is-previewing');
}

export function renderImagePreview(card, url, temporary = false) {
  const img = card?.querySelector('[data-image-preview]');

  if (!img) {
    renderUnsupportedPreview(card);
    return;
  }

  cleanupPreviewUrl(card);
  clearPreviewStage(card);

  if (temporary) card.dataset.previewObjectUrl = url;

  img.src = url;
  img.classList.remove('d-none');
  card.classList.add('is-previewing');
}

export function renderUnsupportedPreview(card) {
  const unsupported = card?.querySelector('[data-pdf-unsupported]');
  if (!unsupported) return;

  cleanupPreviewUrl(card);
  clearPreviewStage(card);
  unsupported.classList.remove('d-none');
  card.classList.add('is-previewing');
}

export function renderEmptyPreview(card) {
  const empty = card?.querySelector('[data-pdf-empty]');
  if (!empty) return;

  cleanupPreviewUrl(card);
  clearPreviewStage(card);
  empty.classList.remove('d-none');
  card.classList.remove('is-previewing');
}

export default function initDocumentPreviewModal() {
  if (!CONFIG.hasBootstrap) return;

  const modalEl = document.querySelector('[data-document-preview-modal]');
  if (!modalEl || modalEl.dataset.documentPreviewReady === 'true') return;

  const previewCard = modalEl.querySelector('[data-document-preview-stage]');
  const titleEl = modalEl.querySelector('[data-document-preview-title]');
  const defaultTitle = modalEl.dataset.previewDefaultTitle || 'معاينة الملف';

  if (!previewCard || !titleEl) return;

  const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
  let activeUrl = '';
  let activeExt = '';
  let previewRequestId = 0;
  let previewController = null;

  const cancelPendingPreviewFetch = () => {
    previewRequestId += 1;

    if (previewController) {
      previewController.abort();
      previewController = null;
    }
  };

  document.addEventListener('click', e => {
    const trigger = e.target.closest('[data-document-preview-trigger]');
    if (!trigger || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const url = trigger.dataset.previewUrl || trigger.getAttribute('href') || '';
    if (!url) return;

    e.preventDefault();
    activeUrl = url;
    activeExt = (trigger.dataset.previewExtension || getExtFromUrl(url)).toLowerCase();
    titleEl.textContent = trigger.dataset.previewTitle || defaultTitle;
    modal.show(trigger);
  });

  modalEl.addEventListener('show.bs.modal', () => {
    cancelPendingPreviewFetch();

    if (!activeUrl) {
      renderEmptyPreview(previewCard);
      return;
    }

    if (PREVIEW_IMAGE_EXTENSIONS.has(activeExt)) {
      renderImagePreview(previewCard, activeUrl);
      return;
    }

    if (activeExt && activeExt !== 'pdf') {
      renderUnsupportedPreview(previewCard);
      return;
    }

    const requestId = previewRequestId;
    const controller = new AbortController();
    previewController = controller;

    cleanupPreviewUrl(previewCard);
    renderPdfPreviewLoading(previewCard);

    fetchAsObjectUrl(activeUrl, 'application/pdf', controller.signal)
      .then(blobUrl => {
        if (requestId !== previewRequestId || !activeUrl) {
          URL.revokeObjectURL(blobUrl);
          return;
        }

        previewCard.dataset.previewObjectUrl = blobUrl;
        renderPdfPreviewDirect(previewCard, blobUrl);
      })
      .catch(err => {
        if (controller.signal.aborted || requestId !== previewRequestId) return;

        console.warn('[document-preview] blob fetch failed, trying direct src.', err);
        renderPdfPreviewDirect(previewCard, normalizeStorageUrl(activeUrl));
      })
      .finally(() => {
        if (previewController === controller) {
          previewController = null;
        }
      });
  });

  modalEl.addEventListener('hidden.bs.modal', () => {
    cancelPendingPreviewFetch();
    activeUrl = '';
    activeExt = '';
    titleEl.textContent = defaultTitle;
    renderEmptyPreview(previewCard);
  });

  modalEl.dataset.documentPreviewReady = 'true';
}
