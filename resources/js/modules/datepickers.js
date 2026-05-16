import { Arabic } from 'flatpickr/dist/l10n/ar.js';
import { CONFIG } from './config';

export default function initLegalDatePickers() {
  if (!CONFIG.hasFlatpickr) return;

  document.querySelectorAll('.flatpickr-date').forEach(input => {
    if (input.dataset.flatpickrReady === 'true') return;

    const fp = window.flatpickr(input, {
      dateFormat: 'Y-m-d',

      altInput: true,
      altFormat: 'd/m/Y',
      altInputClass: 'form-control',

      locale: Arabic,

      static: true,
      allowInput: true,
      clickOpens: true,

      monthSelectorType: 'dropdown'
    });

    if (fp.altInput) {
      fp.altInput.removeAttribute('readonly');
      fp.altInput.setAttribute('autocomplete', 'off');
    }

    input.dataset.flatpickrReady = 'true';
  });
}
