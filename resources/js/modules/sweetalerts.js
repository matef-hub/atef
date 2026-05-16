import { CONFIG } from './config';

export default function initLegalSweetAlertForms() {
  if (!CONFIG.hasSwal) return;

  document.querySelectorAll('form[data-swal-confirm]').forEach(form => {
    if (form.dataset.swalReady === 'true') return;

    form.addEventListener('submit', e => {
      if (form.dataset.swalConfirmed === 'true') {
        delete form.dataset.swalConfirmed;
        return;
      }

      e.preventDefault();

      const submitter = e.submitter || form.querySelector('[type="submit"]');
      const dir = document.documentElement.getAttribute('dir') || 'ltr';

      window.Swal.fire({
        title: form.dataset.swalTitle || 'هل أنت متأكد؟',
        text: form.dataset.swalText || '',
        icon: form.dataset.swalIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: form.dataset.swalConfirmButton || 'نعم',
        cancelButtonText: form.dataset.swalCancelButton || 'إلغاء',
        reverseButtons: dir === 'rtl',
        didOpen: popup => popup.setAttribute('dir', dir)
      }).then(result => {
        if (!result.isConfirmed) return;

        form.dataset.swalConfirmed = 'true';
        typeof form.requestSubmit === 'function' ? form.requestSubmit(submitter) : form.submit();
      });
    });

    form.dataset.swalReady = 'true';
  });
}
