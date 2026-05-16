import { CONFIG, PREVIEW_IMAGE_EXTENSIONS, dropzonePreviewTemplate } from './config';
import { getExt } from './helpers';
import { renderEmptyPreview, renderImagePreview, renderPdfPreview, renderUnsupportedPreview } from './previews';

export default function initLegalDropzones() {
  if (window.__legalDropzonesInitialized) return;

  if (!CONFIG.hasDropzone) return;

  if (!CONFIG.dataTransferSupported) {
    console.warn('[legal-utils] DataTransfer API unavailable. Dropzones disabled.');
    return;
  }

  window.Dropzone.autoDiscover = false;

  const dropzones = document.querySelectorAll('.legal-dropzone');

  if (!dropzones.length) return;

  window.__legalDropzonesInitialized = true;

  dropzones.forEach(dz => {
    if (dz.dataset.dropzoneReady === 'true') return;

    const inputId = dz.dataset.dropzoneInput;
    const fileInput = document.getElementById(inputId);
    if (!fileInput) return;

    const uploader = new window.Dropzone(dz, {
      url: window.location.href,
      autoProcessQueue: false,
      clickable: false,
      maxFiles: 1,
      addRemoveLinks: true,
      acceptedFiles: dz.dataset.dropzoneAccept || '.pdf,.doc,.docx',
      createImageThumbnails: false,
      previewTemplate: dropzonePreviewTemplate,
      dictInvalidFileType: 'نوع الملف غير مدعوم.',
      dictFileTooBig: 'حجم الملف كبير جدًا.',
      dictRemoveFile: 'حذف الملف'
    });

    uploader.on('maxfilesexceeded', file => {
      uploader.removeAllFiles(true);
      uploader.addFile(file);
    });

    uploader.on('addedfile', file => {
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;

      const icon = dz.querySelector('.dz-preview:last-child .legal-dropzone-file-icon');

      if (icon) {
        const ext = getExt(file.name);
        icon.className = `icon-base ${ext === 'pdf' ? 'ti tabler-file-type-pdf text-danger' : ext === 'doc' || ext === 'docx' ? 'ti tabler-file-word text-primary' : 'ti tabler-file-text text-secondary'} legal-dropzone-file-icon`;
      }

      const targetId = dz.dataset.previewTarget;

      if (targetId) {
        const previewCard = document.getElementById(targetId);
        const ext = getExt(file.name);
        const previewUrl = URL.createObjectURL(file);

        if (file.type === 'application/pdf' || ext === 'pdf') {
          renderPdfPreview(previewCard, previewUrl, true);
        } else if (PREVIEW_IMAGE_EXTENSIONS.has(ext)) {
          renderImagePreview(previewCard, previewUrl, true);
        } else {
          URL.revokeObjectURL(previewUrl);
          renderUnsupportedPreview(previewCard);
        }
      }
    });

    uploader.on('removedfile', () => {
      fileInput.value = '';

      const targetId = dz.dataset.previewTarget;

      if (targetId) {
        renderEmptyPreview(document.getElementById(targetId));
      }
    });

    dz.addEventListener('click', e => {
      if (!e.target.closest('[data-dz-remove]')) fileInput.click();
    });

    fileInput.addEventListener('change', e => {
      const file = e.target.files?.[0];

      if (file) {
        uploader.removeAllFiles(true);
        uploader.addFile(file);
      }
    });

    const initialUrl = dz.dataset.previewUrl;

    if (initialUrl) {
      const targetId = dz.dataset.previewTarget;
      if (targetId) renderPdfPreview(document.getElementById(targetId), initialUrl);
    }

    dz.dataset.dropzoneReady = 'true';
  });
}
