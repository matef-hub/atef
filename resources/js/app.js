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
  initLegalSingleSelects();
  initLegalMultiSelects();
  initLegalDashboardCharts();
  initContractFileStages();
  initLegalDropzones();
  initLegalSweetAlertForms();
  initLegalCaseWizards();
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

    const hasUnsupportedColspanRow = [...table.querySelectorAll('tbody tr')].some(
      row => row.children.length === 1 && row.querySelector('td[colspan]')
    );

    if (hasUnsupportedColspanRow) {
      table.dataset.datatableReady = 'skipped';
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

  window.jQuery('.legal-project-combobox, .legal-tag-select').each(function initCombobox() {
    const field = window.jQuery(this);
    const tagsEnabled =
      field.hasClass('legal-project-combobox') || field.data('tags') === true || field.data('tags') === 'true';

    if (field.hasClass('select2-hidden-accessible')) {
      return;
    }

    if (!field.parent().hasClass('position-relative')) {
      field.wrap('<div class="position-relative"></div>');
    }

    field.select2({
      tags: tagsEnabled,
      dir: 'rtl',
      width: '100%',
      dropdownParent: field.parent(),
      placeholder: field.data('placeholder') || 'اختر أو اكتب اسم المشروع',
      allowClear: true,
      createTag(params) {
        if (!tagsEnabled) {
          return null;
        }

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

function initLegalSingleSelects() {
  if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
    return;
  }

  window.jQuery('.legal-single-select').each(function initSingleSelect() {
    const field = window.jQuery(this);

    if (field.hasClass('select2-hidden-accessible')) {
      return;
    }

    if (!field.parent().hasClass('position-relative')) {
      field.wrap('<div class="position-relative"></div>');
    }

    field.select2({
      dir: 'rtl',
      width: '100%',
      allowClear: true,
      dropdownParent: field.parent(),
      placeholder: field.data('placeholder') || 'اختر من القائمة'
    });
  });
}

// ---------------------------------------------------------------------------
// Select2 â€” multi-select
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
// Cases wizard
// ---------------------------------------------------------------------------

function initLegalCaseWizards() {
  if (typeof window.Stepper === 'undefined') {
    return;
  }

  document.querySelectorAll('[data-legal-case-wizard]').forEach(wizard => {
    if (wizard.dataset.caseWizardReady === 'true') {
      return;
    }

    const form = wizard.closest('form');

    if (!form) {
      return;
    }

    const stepper = new window.Stepper(wizard, {
      linear: false
    });

    const typeInputs = form.querySelectorAll('[data-case-type-input]');
    const typeSummary = form.querySelector('[data-case-summary="case_type"]');
    const caseNumberSummary = form.querySelector('[data-case-summary="case_number"]');
    const caseNumberPreview = form.querySelector('[data-case-summary-preview="case_number"]');
    const partiesSummary = form.querySelector('[data-case-summary="parties"]');
    const judgmentDateInput = form.querySelector('[data-judgment-date-input]');
    const appealToggle = form.querySelector('[data-has-appeal-toggle]');
    const appealPanel = form.querySelector('[data-appeal-panel]');
    const appealDeadlineDate = form.querySelector('[data-appeal-deadline-date]');
    const appealDeadlineNote = form.querySelector('[data-appeal-deadline-note]');

    const toggleControlState = (field, disabled) => {
      field.disabled = disabled;

      if (field._flatpickr?.altInput) {
        field._flatpickr.altInput.disabled = disabled;
      }

      if (
        typeof window.jQuery !== 'undefined' &&
        typeof window.jQuery.fn.select2 !== 'undefined' &&
        field.classList.contains('select2-hidden-accessible')
      ) {
        window.jQuery(field).prop('disabled', disabled).trigger('change.select2');
      }
    };

    const togglePanelControls = (panel, enabled) => {
      panel.querySelectorAll('input, select, textarea').forEach(field => {
        toggleControlState(field, !enabled);
      });
    };

    const getSelectedType = () =>
      form.querySelector('[data-case-type-input]:checked')?.value || 'civil';

    const getFieldValue = fieldName => {
      const activeField = [...form.querySelectorAll(`[name="${fieldName}"]`)].find(field => !field.disabled);
      return activeField?.value?.trim() || '';
    };

    const updateSummary = () => {
      const selectedType = getSelectedType();
      const typeLabel = selectedType === 'criminal' ? 'جنائية' : 'مدنية';
      const caseNumber = getFieldValue('case_number');
      const primaryParty = getFieldValue('primary_party_name');
      const opponentParty = getFieldValue('opponent_party_name');

      if (typeSummary) {
        typeSummary.textContent = typeLabel;
      }

      if (caseNumberSummary) {
        caseNumberSummary.textContent = caseNumber || '—';
      }

      if (caseNumberPreview) {
        caseNumberPreview.textContent = caseNumber || 'لم يتم إدخال رقم الدعوى بعد';
      }

      if (partiesSummary) {
        partiesSummary.textContent = primaryParty && opponentParty ? `${primaryParty} / ${opponentParty}` : '—';
      }
    };

    const updateAppealDeadline = () => {
      if (!appealDeadlineDate || !appealDeadlineNote) {
        return;
      }

      const selectedType = getSelectedType();

      if (selectedType !== 'civil') {
        appealDeadlineDate.textContent = 'غير مطبق على القضايا الجنائية';
        appealDeadlineNote.textContent = 'هذه الخطوة مخصصة للقضايا المدنية فقط.';
        return;
      }

      const judgmentValue = judgmentDateInput?.value;

      if (!judgmentValue) {
        appealDeadlineDate.textContent = 'لم يتم تحديد تاريخ الحكم';
        appealDeadlineNote.textContent = 'أدخل تاريخ الحكم ليتم حساب مهلة الاستئناف تلقائيًا.';
        return;
      }

      const judgmentDate = new Date(`${judgmentValue}T00:00:00`);

      if (Number.isNaN(judgmentDate.getTime())) {
        appealDeadlineDate.textContent = 'تاريخ غير صالح';
        appealDeadlineNote.textContent = 'يرجى مراجعة تاريخ الحكم.';
        return;
      }

      const deadline = new Date(judgmentDate);
      deadline.setDate(deadline.getDate() + 40);

      const today = new Date();
      today.setHours(0, 0, 0, 0);

      const msPerDay = 24 * 60 * 60 * 1000;
      const remainingDays = Math.round((deadline.getTime() - today.getTime()) / msPerDay);

      appealDeadlineDate.textContent = formatDateYmd(deadline);

      if (remainingDays >= 0) {
        appealDeadlineNote.textContent = `متبقي ${remainingDays} يومًا حتى نهاية مهلة الاستئناف.`;
        return;
      }

      appealDeadlineNote.textContent = `انتهت مهلة الاستئناف منذ ${Math.abs(remainingDays)} يومًا.`;
    };

    const syncAppealPanel = () => {
      const selectedType = getSelectedType();
      const showAppealPanel = selectedType === 'civil' && Boolean(appealToggle?.checked);

      if (!appealPanel) {
        return;
      }

      appealPanel.classList.toggle('d-none', !showAppealPanel);
      togglePanelControls(appealPanel, showAppealPanel);
    };

    const syncTypePanels = () => {
      const selectedType = getSelectedType();

      form.querySelectorAll('[data-case-type-card]').forEach(card => {
        card.classList.toggle('is-active', card.dataset.caseTypeCard === selectedType);
      });

      form.querySelectorAll('[data-case-panel]').forEach(panel => {
        const isActive = panel.dataset.casePanel === selectedType;
        panel.classList.toggle('d-none', !isActive);
        togglePanelControls(panel, isActive);
      });

      if (selectedType !== 'civil' && appealToggle) {
        appealToggle.checked = false;
      }

      syncAppealPanel();
      updateSummary();
      updateAppealDeadline();
    };

    wizard.querySelectorAll('.btn-next').forEach(button => {
      button.addEventListener('click', () => {
        stepper.next();
      });
    });

    wizard.querySelectorAll('.btn-prev').forEach(button => {
      button.addEventListener('click', () => {
        stepper.previous();
      });
    });

    typeInputs.forEach(input => input.addEventListener('change', syncTypePanels));
    appealToggle?.addEventListener('change', syncAppealPanel);
    judgmentDateInput?.addEventListener('change', updateAppealDeadline);

    form.addEventListener('input', event => {
      if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
        updateSummary();
      }
    });

    form.addEventListener('change', event => {
      if (event.target instanceof HTMLSelectElement) {
        updateSummary();
      }
    });

    syncTypePanels();
    updateSummary();
    updateAppealDeadline();

    wizard.dataset.caseWizardReady = 'true';
  });
}

// ---------------------------------------------------------------------------
// Dashboard
// ---------------------------------------------------------------------------

function initLegalDashboardCharts() {
  const chartElement = document.querySelector('[data-legal-contract-inflow-chart]');

  if (!chartElement || chartElement.dataset.chartReady === 'true') {
    return;
  }

  if (typeof window.ApexCharts === 'undefined' || typeof window.config === 'undefined') {
    return;
  }

  let categories = [];
  let seriesData = [];

  try {
    categories = JSON.parse(chartElement.dataset.categories || '[]');
    seriesData = JSON.parse(chartElement.dataset.series || '[]');
  } catch (error) {
    console.warn('[legal-dashboard] Failed to parse contract inflow chart data.', error);
    return;
  }

  const cardColor = config.colors.cardColor;
  const headingColor = config.colors.headingColor;
  const labelColor = config.colors.textMuted;
  const borderColor = config.colors.borderColor;
  const primaryColor = config.colors.primary;
  const primarySubtleColor =
    typeof window.Helpers !== 'undefined'
      ? window.Helpers.getCssVar('primary-bg-subtle')
      : config.colors.primary;

  const chart = new window.ApexCharts(chartElement, {
    chart: {
      type: 'area',
      height: 320,
      parentHeightOffset: 0,
      toolbar: {
        show: false
      },
      fontFamily: config.fontFamily
    },
    series: [
      {
        name: 'العقود',
        data: seriesData
      }
    ],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 3
    },
    colors: [primaryColor],
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 0.35,
        opacityFrom: 0.45,
        opacityTo: 0.08,
        stops: [0, 90, 100],
        gradientToColors: [primarySubtleColor]
      }
    },
    grid: {
      borderColor,
      strokeDashArray: 6,
      padding: {
        top: -12,
        left: 0,
        right: 8,
        bottom: 0
      }
    },
    markers: {
      size: 4,
      strokeWidth: 3,
      colors: [cardColor],
      strokeColors: primaryColor,
      hover: {
        size: 6
      }
    },
    xaxis: {
      categories,
      axisBorder: {
        show: false
      },
      axisTicks: {
        show: false
      },
      labels: {
        style: {
          colors: labelColor,
          fontSize: '12px'
        }
      }
    },
    yaxis: {
      min: 0,
      tickAmount: 4,
      labels: {
        style: {
          colors: labelColor,
          fontSize: '12px'
        }
      }
    },
    tooltip: {
      shared: true,
      intersect: false,
      x: {
        show: true
      }
    },
    legend: {
      show: false
    },
    responsive: [
      {
        breakpoint: 992,
        options: {
          chart: {
            height: 280
          }
        }
      },
      {
        breakpoint: 576,
        options: {
          chart: {
            height: 240
          }
        }
      }
    ]
  });

  chart.render();
  chartElement.dataset.chartReady = 'true';
}

// ---------------------------------------------------------------------------
// Contract file stages
// ---------------------------------------------------------------------------

function initContractFileStages() {
  document.querySelectorAll('[data-contract-file-stage]').forEach(stage => {
    if (stage.dataset.contractFileReady === 'true') return;

    const inputId = stage.dataset.fileInput;
    const fileInput = document.getElementById(inputId);
    const fileNameTarget = document.getElementById(stage.dataset.fileNameTarget);
    const previewButton = document.getElementById(stage.dataset.filePreviewTrigger);
    const modalElement = document.getElementById(stage.dataset.fileModal);
    const modalStage = document.getElementById(stage.dataset.fileModalStage);
    const modalTitle = document.getElementById(stage.dataset.fileModalTitle);
    const defaultTitle = stage.dataset.fileDefaultTitle || 'معاينة الملف';
    const previewEnabled = Boolean(modalStage);
    const triggerButtons = document.querySelectorAll(`[data-contract-file-trigger="${inputId}"]`);

    if (!fileInput) return;

    const updateFileName = name => {
      if (!fileNameTarget) return;
      const display = name || stage.dataset.emptyName || 'لم يتم اختيار ملف بعد.';
      fileNameTarget.textContent = display;
      fileNameTarget.setAttribute('title', display);
    };

    const updateModalTitle = name => {
      if (modalTitle) modalTitle.textContent = name || defaultTitle;
    };

    const togglePreviewButton = visible => {
      previewButton?.classList.toggle('d-none', !visible);
    };

    const openModal = () => {
      if (!previewEnabled || !modalElement || typeof window.bootstrap === 'undefined') return;
      window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
    };

    const setPendingPreview = (url, extension) => {
      stage.dataset.pendingPreviewUrl = url || '';
      stage.dataset.pendingPreviewExtension = extension || '';
    };

    const flushPendingPreview = () => {
      const url = stage.dataset.pendingPreviewUrl;
      const extension = stage.dataset.pendingPreviewExtension;
      if (!modalStage) return;
      if (!url) {
        renderEmptyPreview(modalStage);
        return;
      }
      if (extension === 'pdf') {
        const isBlobUrl = url.startsWith('blob:');
        const frameUrl = isBlobUrl ? url : buildPdfViewerUrl(url);
        renderPdfPreviewDirect(modalStage, frameUrl);
      } else {
        renderUnsupportedPreview(modalStage);
      }
    };

    const handleFileSelected = file => {
      if (!file) {
        const existingUrl = stage.dataset.previewUrl || '';
        const existingName = stage.dataset.currentName || '';
        const existingExt = stage.dataset.currentExtension || getFileExtension(existingName);
        updateFileName(existingName || null);
        updateModalTitle(existingName || null);
        togglePreviewButton(previewEnabled && Boolean(existingUrl || existingName));
        setPendingPreview(existingUrl, existingExt);
        return;
      }

      const extension = getFileExtension(file.name);

      if (stage.dataset.blobUrl) {
        URL.revokeObjectURL(stage.dataset.blobUrl);
      }
      const blobUrl = URL.createObjectURL(file);
      stage.dataset.blobUrl = blobUrl;

      updateFileName(file.name);
      updateModalTitle(file.name);
      togglePreviewButton(previewEnabled);
      setPendingPreview(blobUrl, extension);
    };

    triggerButtons.forEach(btn => {
      btn.addEventListener('click', () => fileInput.click());
    });

    stage.addEventListener('click', event => {
      if (event.target.closest('button, a, [data-no-file-trigger]')) return;
      fileInput.click();
    });

    fileInput.addEventListener('change', event => {
      const [file] = event.target.files || [];
      handleFileSelected(file || null);
    });

    if (modalElement) {
      modalElement.addEventListener('show.bs.modal', flushPendingPreview);
      modalElement.addEventListener('hidden.bs.modal', () => {
        cleanupPreviewUrl(modalStage);
      });
    }

    previewButton?.addEventListener('click', openModal);

    handleFileSelected(null);
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

function formatDateYmd(date) {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
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

  frame.removeAttribute('sandbox');
  frame.src = buildPdfViewerUrl(url);
  frame.classList.remove('d-none');
  emptyState.classList.add('d-none');
  unsupportedState.classList.add('d-none');
  previewCard.classList.add('is-previewing');
}

function renderPdfPreviewDirect(previewCard, frameUrl) {
  if (!previewCard) return;
  const frame = previewCard.querySelector('[data-pdf-frame]');
  const emptyState = previewCard.querySelector('[data-pdf-empty]');
  const unsupportedState = previewCard.querySelector('[data-pdf-unsupported]');
  if (!frame || !emptyState || !unsupportedState) return;
  frame.removeAttribute('sandbox');
  frame.removeAttribute('src');
  requestAnimationFrame(() => {
    frame.src = frameUrl;
  });
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
  if (!url) return url;
  if (url.startsWith('blob:')) return url;
  if (url.includes('#')) return url;
  return `${url}#toolbar=0&navpanes=0&scrollbar=0&view=FitH`;
}

import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);
