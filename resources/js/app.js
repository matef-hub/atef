import './bootstrap';
document.addEventListener('DOMContentLoaded', function () {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

import { Arabic } from 'flatpickr/dist/l10n/ar.js';

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const dataTableLanguage = {
  search: '',
  searchPlaceholder: 'ابحث...',
  lengthMenu: 'اعرض _MENU_',
  info: 'عرض _START_ إلى _END_ من _TOTAL_ عنصر',
  infoEmpty: 'لا توجد بيانات لعرضها',
  infoFiltered: '(تمت التصفية من إجمالي _MAX_ عنصر)',
  zeroRecords: 'مفيش نتائج مطابقة',
  emptyTable: 'لا توجد بيانات حالياً',
  paginate: {
    next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
    previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
    first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
    last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
  }
};

const dropzonePreviewTemplate = `
  <div class="dz-preview dz-file-preview">
    <div class="dz-details">
      <div class="dz-file-icon">
        <i class="icon-base ti tabler-file-text legal-dropzone-file-icon"></i>
      </div>
      <div class="flex-grow-1 min-w-0">
        <div class="dz-filename"><span data-dz-name></span></div>
        <div class="dz-size" data-dz-size></div>
        <div class="dz-error-message"><span data-dz-errormessage></span></div>
      </div>
    </div>
    <a class="dz-remove" href="javascript:void(0);" data-dz-remove>حذف الملف</a>
  </div>
`;

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  initLegalDataTables();
  initLegalDatePickers();
  initLegalProjectComboboxes();
  initLegalMultiSelects();
  initContractFileStages();
  initLegalDropzones();
  initLegalSweetAlertForms();
});

// ---------------------------------------------------------------------------
// DataTables
// ---------------------------------------------------------------------------

function initLegalDataTables() {
  if (typeof window.DataTable === 'undefined') {
    return;
  }

  document.querySelectorAll('.legal-datatable').forEach(table => {
    if (table.dataset.datatableReady === 'true') {
      return;
    }

    new window.DataTable(table, {
      pageLength: 10,
      autoWidth: false,
      responsive: true,
      order: [],
      layout: {
        topStart: {
          rowClass: 'row mx-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: 'اعرض_MENU_'
              }
            }
          ]
        },
        topEnd: {
          search: {
            placeholder: 'ابحث...'
          }
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      language: dataTableLanguage,

      initComplete() {
        decorateDataTableUi();
      }
    });

    table.dataset.datatableReady = 'true';
  });
}

function decorateDataTableUi() {
  [
    {
      selector: '.dt-search .form-control',
      remove: ['form-control-sm'],
      add: ['ms-0']
    },
    {
      selector: '.dt-length .form-select',
      remove: ['form-select-sm'],
      add: []
    },
    {
      selector: '.dt-layout-end',
      remove: [],
      add: ['gap-md-2', 'gap-0', 'mt-0']
    },
    {
      selector: '.dt-layout-start',
      remove: [],
      add: ['mt-0']
    },
    {
      selector: '.dt-layout-full',
      remove: ['col-md', 'col-12'],
      add: ['table-responsive']
    }
  ].forEach(({ selector, remove, add }) => {
    document.querySelectorAll(selector).forEach(element => {
      remove.forEach(className => element.classList.remove(className));
      add.forEach(className => element.classList.add(className));
    });
  });
}

// ---------------------------------------------------------------------------
// Date pickers
// ---------------------------------------------------------------------------

function initLegalDatePickers() {
  if (typeof window.flatpickr === 'undefined') {
    return;
  }

  document.querySelectorAll('.flatpickr-date').forEach(input => {
    if (input.dataset.flatpickrReady === 'true') {
      return;
    }

    window.flatpickr(input, {
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'j F Y',
      locale: Arabic,
      monthSelectorType: 'static',
      static: true
    });

    input.dataset.flatpickrReady = 'true';
  });
}

// ---------------------------------------------------------------------------
// Select2 — project combobox
// ---------------------------------------------------------------------------

function initLegalProjectComboboxes() {
  if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
    return;
  }

  window.jQuery('.legal-project-combobox').each(function initCombobox() {
    const field = window.jQuery(this);

    if (field.hasClass('select2-hidden-accessible')) {
      return;
    }

    field.wrap('<div class="position-relative"></div>').select2({
      tags: true,
      dir: 'rtl',
      width: '100%',
      dropdownParent: field.parent(),
      placeholder: field.data('placeholder') || 'اختر أو اكتب اسم المشروع',
      allowClear: true,
      createTag(params) {
        const term = window.jQuery.trim(params.term);

        if (!term) {
          return null;
        }

        return {
          id: term,
          text: term,
          newTag: true
        };
      },
      language: {
        noResults() {
          return 'اكتب اسم مشروع جديد';
        }
      }
    });
  });
}

// ---------------------------------------------------------------------------
// Select2 — multi-select
// ---------------------------------------------------------------------------

function initLegalMultiSelects() {
  if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
    return;
  }

  window.jQuery('.legal-multi-select').each(function initMultiSelect() {
    const field = window.jQuery(this);

    if (field.hasClass('select2-hidden-accessible')) {
      return;
    }

    field.wrap('<div class="position-relative"></div>').select2({
      dir: 'rtl',
      width: '100%',
      dropdownParent: field.parent(),
      placeholder: field.data('placeholder') || 'اختر من القائمة',
      closeOnSelect: false
    });
  });
}

// ---------------------------------------------------------------------------
// Contract file stages
// ---------------------------------------------------------------------------

function initContractFileStages() {
  document.querySelectorAll('[data-contract-file-stage]').forEach(stage => {
    if (stage.dataset.contractFileReady === 'true') {
      return;
    }

    const inputId = stage.dataset.fileInput;
    const fileInput = document.getElementById(inputId);
    const fileNameTarget = document.getElementById(stage.dataset.fileNameTarget);
    const previewButton = document.getElementById(stage.dataset.filePreviewTrigger);
    const modalElement = document.getElementById(stage.dataset.fileModal);
    const modalStage = document.getElementById(stage.dataset.fileModalStage);
    const modalTitle = document.getElementById(stage.dataset.fileModalTitle);
    const defaultModalTitle = stage.dataset.fileDefaultTitle || 'معاينة الملف';
    const previewEnabled = Boolean(modalStage);
    const triggerButtons = document.querySelectorAll(`[data-contract-file-trigger="${inputId}"]`);

    if (!fileInput) {
      return;
    }

    const updateFileName = name => {
      if (!fileNameTarget) {
        return;
      }

      const displayName = name || stage.dataset.emptyName || 'لم يتم اختيار ملف بعد.';
      fileNameTarget.textContent = displayName;
      fileNameTarget.setAttribute('title', displayName);
    };

    const updateModalTitle = name => {
      if (!modalTitle) {
        return;
      }

      modalTitle.textContent = name || defaultModalTitle;
    };

    const togglePreviewButton = visible => {
      if (!previewButton) {
        return;
      }

      previewButton.classList.toggle('d-none', !visible);
    };

    const openPreviewModal = () => {
      if (!previewEnabled || !modalElement || typeof window.bootstrap === 'undefined') {
        return;
      }

      window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
    };

    const renderExistingPreview = () => {
      const previewUrl = stage.dataset.previewUrl;
      const currentName = stage.dataset.currentName;
      const currentExtension = stage.dataset.currentExtension || getFileExtension(currentName || '');

      if (!previewUrl && !currentName) {
        updateFileName();
        updateModalTitle();
        togglePreviewButton(false);

        if (previewEnabled) {
          renderEmptyPreview(modalStage);
        }

        return;
      }

      updateFileName(currentName);
      updateModalTitle(currentName);
      togglePreviewButton(previewEnabled);

      if (!previewEnabled) {
        return;
      }

      if (currentExtension === 'pdf' && previewUrl) {
        renderPdfPreview(modalStage, previewUrl);
        return;
      }

      renderUnsupportedPreview(modalStage);
    };

    const updateStageFromFile = (file, { openPreview = false } = {}) => {
      const extension = file ? getFileExtension(file.name) : '';
      const isPdf = Boolean(file) && (file.type === 'application/pdf' || extension === 'pdf');

      if (!file) {
        renderExistingPreview();
        return;
      }

      updateFileName(file.name);
      updateModalTitle(file.name);
      togglePreviewButton(previewEnabled);

      if (!previewEnabled) {
        return;
      }

      if (isPdf) {
        // cleanupPreviewUrl is now called inside renderPdfPreview, so passing
        // temporary=true is sufficient to ensure the old URL is revoked first.
        renderPdfPreview(modalStage, URL.createObjectURL(file), true);
      } else {
        renderUnsupportedPreview(modalStage);
      }

      if (openPreview) {
        openPreviewModal();
      }
    };

    triggerButtons.forEach(button => {
      button.addEventListener('click', () => fileInput.click());
    });

    stage.addEventListener('click', event => {
      if (
        event.target.closest('button') ||
        event.target.closest('a') ||
        event.target.closest('[data-no-file-trigger]')
      ) {
        return;
      }

      fileInput.click();
    });

    fileInput.addEventListener('change', event => {
      const [file] = event.target.files || [];
      updateStageFromFile(file || null, { openPreview: Boolean(file) });
    });

    if (previewEnabled) {
      previewButton?.addEventListener('click', () => {
        openPreviewModal();
      });
    }

    updateStageFromFile(null);
    stage.dataset.contractFileReady = 'true';
  });
}

// ---------------------------------------------------------------------------
// Dropzones
// ---------------------------------------------------------------------------

function initLegalDropzones() {
  if (typeof window.Dropzone === 'undefined') {
    return;
  }

  const dataTransferSupported = typeof DataTransfer !== 'undefined';

  if (!dataTransferSupported) {
    console.warn(
      '[legal-utils] DataTransfer API is unavailable in this environment. ' +
        'Dropzone file inputs will not sync correctly. Dropzones are disabled.'
    );
  }

  window.Dropzone.autoDiscover = false;

  document.querySelectorAll('.legal-dropzone').forEach(dropzoneElement => {
    if (dropzoneElement.dataset.dropzoneReady === 'true') {
      return;
    }

    const inputId = dropzoneElement.dataset.dropzoneInput;
    const acceptedFiles = dropzoneElement.dataset.dropzoneAccept || '.pdf,.doc,.docx';
    const fileInput = document.getElementById(inputId);

    if (!fileInput) {
      return;
    }

    // Disable the entire dropzone widget when DataTransfer is unavailable to
    // prevent a broken UX where the user believes a file was attached but the
    // form will submit with an empty input.
    if (!dataTransferSupported) {
      dropzoneElement.dataset.dropzoneReady = 'true';
      dropzoneElement.setAttribute('aria-disabled', 'true');
      dropzoneElement.style.opacity = '0.5';
      dropzoneElement.style.pointerEvents = 'none';
      return;
    }

    const uploader = new window.Dropzone(dropzoneElement, {
      url: window.location.href,
      autoProcessQueue: false,
      clickable: false,
      maxFiles: 1,
      addRemoveLinks: true,
      acceptedFiles,
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
      syncFileInput(fileInput, file);
      updatePreviewIcon(dropzoneElement, file);
      updateDropzonePreview(dropzoneElement, file);
      uploader.emit('complete', file);
    });

    uploader.on('removedfile', () => {
      clearFileInput(fileInput);
      updateDropzonePreview(dropzoneElement, null);
    });

    dropzoneElement.addEventListener('click', event => {
      if (event.target.closest('[data-dz-remove]')) {
        return;
      }

      fileInput.click();
    });

    fileInput.addEventListener('change', event => {
      const [file] = event.target.files;

      if (!file) {
        return;
      }

      uploader.removeAllFiles(true);
      uploader.addFile(file);
    });

    initDropzonePreview(dropzoneElement);
    dropzoneElement.dataset.dropzoneReady = 'true';
  });
}

// ---------------------------------------------------------------------------
// SweetAlert forms
// ---------------------------------------------------------------------------

function initLegalSweetAlertForms() {
  if (typeof window.Swal === 'undefined') {
    return;
  }

  document.querySelectorAll('form[data-swal-confirm]').forEach(form => {
    if (form.dataset.swalReady === 'true') {
      return;
    }

    form.addEventListener('submit', event => {
      if (form.dataset.swalConfirmed === 'true') {
        delete form.dataset.swalConfirmed;
        return;
      }

      event.preventDefault();

      const submitter = event.submitter || form.querySelector('[type="submit"]');
      const pageDirection = document.documentElement.getAttribute('dir') || 'ltr';

      window.Swal.fire({
        title: form.dataset.swalTitle || 'هل أنت متأكد؟',
        text: form.dataset.swalText || '',
        icon: form.dataset.swalIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: form.dataset.swalConfirmButton || 'نعم',
        cancelButtonText: form.dataset.swalCancelButton || 'إلغاء',
        reverseButtons: pageDirection === 'rtl',
        didOpen: popup => {
          popup.setAttribute('dir', pageDirection);
        }
      }).then(result => {
        if (!result.isConfirmed) {
          return;
        }

        form.dataset.swalConfirmed = 'true';

        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit(submitter || undefined);
          return;
        }

        // Fallback for browsers that don't support requestSubmit (very old).
        form.submit();
      });
    });

    form.dataset.swalReady = 'true';
  });
}

// ---------------------------------------------------------------------------
// File input sync helpers
// ---------------------------------------------------------------------------

function syncFileInput(input, file) {
  if (typeof DataTransfer === 'undefined') {
    console.warn(
      '[legal-utils] syncFileInput: DataTransfer is unavailable. ' +
        'The file input could not be synced with the dropzone selection.'
    );
    return;
  }

  const dataTransfer = new DataTransfer();
  dataTransfer.items.add(file);
  input.files = dataTransfer.files;
}

function clearFileInput(input) {
  input.value = '';
}

// ---------------------------------------------------------------------------
// File extension helper
// ---------------------------------------------------------------------------

function getFileExtension(filename) {
  if (!filename || typeof filename !== 'string') {
    return '';
  }

  const match = filename.match(/\.([^.]+)$/);
  return match ? match[1].toLowerCase() : '';
}

// ---------------------------------------------------------------------------
// Dropzone preview helpers
// ---------------------------------------------------------------------------

function updatePreviewIcon(dropzoneElement, file) {
  const icon = dropzoneElement.querySelector('.dz-preview:last-child .legal-dropzone-file-icon');

  if (!icon) {
    return;
  }

  const extension = getFileExtension(file.name);
  icon.className = `icon-base ${resolveFileIcon(extension)} legal-dropzone-file-icon`;
}

function resolveFileIcon(extension) {
  if (extension === 'pdf') {
    return 'ti tabler-file-type-pdf text-danger';
  }

  if (extension === 'doc' || extension === 'docx') {
    return 'ti tabler-file-word text-primary';
  }

  return 'ti tabler-file-text text-secondary';
}

function initDropzonePreview(dropzoneElement) {
  const initialUrl = dropzoneElement.dataset.previewUrl;

  if (!initialUrl) {
    return;
  }

  // Render the server-side preview URL without a local File object.
  updateDropzonePreview(dropzoneElement, null, initialUrl);
}

function updateDropzonePreview(dropzoneElement, file = null, fallbackUrl = null) {
  const targetId = dropzoneElement.dataset.previewTarget;

  if (!targetId) {
    return;
  }

  const previewCard = document.getElementById(targetId);

  if (!previewCard) {
    return;
  }

  const extension = file ? getFileExtension(file.name) : '';
  const isPdf = Boolean(file) && (file.type === 'application/pdf' || extension === 'pdf');

  if (file && isPdf) {
    renderPdfPreview(previewCard, URL.createObjectURL(file), true);
    return;
  }

  if (file && !isPdf) {
    renderUnsupportedPreview(previewCard);
    return;
  }

  const currentUrl = fallbackUrl || dropzoneElement.dataset.previewUrl;

  if (currentUrl) {
    renderPdfPreview(previewCard, currentUrl);
    return;
  }

  renderEmptyPreview(previewCard);
}

// ---------------------------------------------------------------------------
// PDF preview renderers
// ---------------------------------------------------------------------------

function renderPdfPreview(previewCard, url, temporary = false) {
  if (!previewCard) {
    return;
  }

  const frame = previewCard.querySelector('[data-pdf-frame]');
  const emptyState = previewCard.querySelector('[data-pdf-empty]');
  const unsupportedState = previewCard.querySelector('[data-pdf-unsupported]');

  if (!frame || !emptyState || !unsupportedState) {
    return;
  }

  cleanupPreviewUrl(previewCard);

  if (temporary) {
    previewCard.dataset.previewObjectUrl = url;
  }

  frame.src = buildPdfViewerUrl(url);
  frame.classList.remove('d-none');
  emptyState.classList.add('d-none');
  unsupportedState.classList.add('d-none');
  previewCard.classList.add('is-previewing');
}

function renderUnsupportedPreview(previewCard) {
  if (!previewCard) {
    return;
  }

  const frame = previewCard.querySelector('[data-pdf-frame]');
  const emptyState = previewCard.querySelector('[data-pdf-empty]');
  const unsupportedState = previewCard.querySelector('[data-pdf-unsupported]');

  if (!frame || !emptyState || !unsupportedState) {
    return;
  }

  cleanupPreviewUrl(previewCard);
  frame.src = '';
  frame.classList.add('d-none');
  emptyState.classList.add('d-none');
  unsupportedState.classList.remove('d-none');
  previewCard.classList.add('is-previewing');
}

function renderEmptyPreview(previewCard) {
  if (!previewCard) {
    return;
  }

  const frame = previewCard.querySelector('[data-pdf-frame]');
  const emptyState = previewCard.querySelector('[data-pdf-empty]');
  const unsupportedState = previewCard.querySelector('[data-pdf-unsupported]');

  if (!frame || !emptyState || !unsupportedState) {
    return;
  }

  cleanupPreviewUrl(previewCard);
  frame.src = '';
  frame.classList.add('d-none');
  emptyState.classList.remove('d-none');
  unsupportedState.classList.add('d-none');
  previewCard.classList.remove('is-previewing');
}

function cleanupPreviewUrl(previewCard) {
  if (!previewCard) {
    return;
  }

  const objectUrl = previewCard.dataset.previewObjectUrl;

  if (objectUrl) {
    URL.revokeObjectURL(objectUrl);
    delete previewCard.dataset.previewObjectUrl;
  }
}

function buildPdfViewerUrl(url) {
  if (!url) {
    return url;
  }

  if (url.includes('#')) {
    return url;
  }

  return `${url}#toolbar=0&navpanes=0&scrollbar=0&view=FitH`;
}

import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);
