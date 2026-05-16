import { CONFIG, PREVIEW_IMAGE_EXTENSIONS } from './config';
import { buildPdfViewerUrl, cleanupPreviewUrl, getExt } from './helpers';
import { renderEmptyPreview, renderImagePreview, renderPdfPreviewDirect, renderUnsupportedPreview } from './previews';

export default function initContractFileStages() {
  document.querySelectorAll('[data-contract-file-stage]').forEach(stage => {
    if (stage.dataset.contractFileReady === 'true') return;

    const inputId = stage.dataset.fileInput;
    const fileInput = document.getElementById(inputId);
    if (!fileInput) return;

    const fileNameTarget = document.getElementById(stage.dataset.fileNameTarget);
    const previewButton = document.getElementById(stage.dataset.filePreviewTrigger);
    const modalElement = document.getElementById(stage.dataset.fileModal);
    const modalStage = document.getElementById(stage.dataset.fileModalStage);
    const modalTitle = document.getElementById(stage.dataset.fileModalTitle);
    const defaultTitle = stage.dataset.fileDefaultTitle || 'معاينة الملف';
    const previewEnabled = Boolean(modalStage);

    const revokeStageBlob = () => {
      if (!stage.dataset.blobUrl) return;

      URL.revokeObjectURL(stage.dataset.blobUrl);
      delete stage.dataset.blobUrl;
    };

    const updateFileName = name => {
      if (!fileNameTarget) return;

      fileNameTarget.textContent = name || stage.dataset.emptyName || 'لم يتم اختيار ملف بعد.';
      fileNameTarget.setAttribute('title', fileNameTarget.textContent);
    };

    const setPendingPreview = (url, ext) => {
      stage.dataset.pendingPreviewUrl = url || '';
      stage.dataset.pendingPreviewExtension = ext || '';
    };

    const flushPendingPreview = () => {
      if (!modalStage) return;

      const url = stage.dataset.pendingPreviewUrl;
      const ext = stage.dataset.pendingPreviewExtension;

      if (!url) {
        renderEmptyPreview(modalStage);
        return;
      }

      if (PREVIEW_IMAGE_EXTENSIONS.has(ext)) {
        renderImagePreview(modalStage, url);
      } else if (ext === 'pdf') {
        renderPdfPreviewDirect(modalStage, url.startsWith('blob:') ? url : buildPdfViewerUrl(url));
      } else {
        renderUnsupportedPreview(modalStage);
      }
    };

    const handleFileSelected = file => {
      if (!file) {
        revokeStageBlob();

        const existingUrl = stage.dataset.previewUrl || '';
        const existingName = stage.dataset.currentName || '';
        const existingExt = stage.dataset.currentExtension || getExt(existingName);

        updateFileName(existingName || null);
        if (modalTitle) modalTitle.textContent = existingName || defaultTitle;
        if (previewButton) previewButton.classList.toggle('d-none', !(previewEnabled && (existingUrl || existingName)));
        setPendingPreview(existingUrl, existingExt);
        return;
      }

      revokeStageBlob();

      const blobUrl = URL.createObjectURL(file);

      stage.dataset.blobUrl = blobUrl;
      updateFileName(file.name);
      if (modalTitle) modalTitle.textContent = file.name;
      if (previewButton) previewButton.classList.toggle('d-none', previewEnabled);
      setPendingPreview(blobUrl, getExt(file.name));
    };

    document.querySelectorAll(`[data-contract-file-trigger="${inputId}"]`).forEach(btn => {
      btn.addEventListener('click', () => fileInput.click());
    });

    stage.addEventListener('click', e => {
      if (!e.target.closest('button, a, [data-no-file-trigger]')) fileInput.click();
    });

    fileInput.addEventListener('change', e => handleFileSelected(e.target.files?.[0] || null));

    if (modalElement) {
      modalElement.addEventListener('show.bs.modal', flushPendingPreview);
      modalElement.addEventListener('hidden.bs.modal', () => cleanupPreviewUrl(modalStage));
    }

    previewButton?.addEventListener(
      'click',
      () => CONFIG.hasBootstrap && modalElement && window.bootstrap.Modal.getOrCreateInstance(modalElement).show()
    );

    window.addEventListener('pagehide', revokeStageBlob, { once: true });

    handleFileSelected(null);
    stage.dataset.contractFileReady = 'true';
  });
}
