import './bootstrap';

import initLegalDataTables from './modules/datatables';
import initLegalDatePickers from './modules/datepickers';
import initLegalSelects from './modules/selects';
import initLegalSweetAlertForms from './modules/sweetalerts';
import initContractFileStages from './modules/contracts';
import initDocumentPreviewModal, {
  renderEmptyPreview,
  renderImagePreview,
  renderPdfPreview,
  renderUnsupportedPreview
} from './modules/previews';
import initWeatherWidget from './modules/weather';
import initLegalDashboardCharts from './modules/dashboard';
import initLegalCaseWizards from './modules/caseWizard';
import initLegalDropzones from './modules/dropzones';
import { cleanupPreviewUrl } from './modules/helpers';

const safeInit = fn => {
  try {
    fn();
  } catch (e) {
    console.error(e);
  }
};

const requestIdle = callback => {
  const idle =
    window.requestIdleCallback ||
    function (cb) {
      return setTimeout(cb, 1);
    };

  return idle(callback);
};

const initBootstrapTooltips = () => {
  if (typeof window.bootstrap === 'undefined') return;

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    window.bootstrap.Tooltip.getOrCreateInstance(el);
  });
};

document.addEventListener(
  'DOMContentLoaded',
  () => {
    [
      initBootstrapTooltips,
      initLegalDataTables,
      initLegalDatePickers,
      initLegalSelects,
      initLegalSweetAlertForms,
      initContractFileStages,
      initDocumentPreviewModal,
      initWeatherWidget
    ].forEach(safeInit);

    requestIdle(() => {
      [initLegalDashboardCharts, initLegalCaseWizards, initLegalDropzones].forEach(safeInit);
    });
  },
  { once: true }
);

window.legalUtils = {
  renderPdfPreview,
  renderImagePreview,
  renderUnsupportedPreview,
  renderEmptyPreview,
  cleanupPreviewUrl
};

import.meta.glob(['../assets/img/**', '../assets/vendor/fonts/**']);
