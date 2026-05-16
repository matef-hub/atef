import { CONFIG } from './config';
import { debounce, formatDateYmd, togglePanelControls } from './helpers';

export default function initLegalCaseWizards() {
  if (!CONFIG.hasStepper) return;

  document.querySelectorAll('[data-legal-case-wizard]').forEach(wizard => {
    if (wizard.dataset.caseWizardReady === 'true') return;

    const form = wizard.closest('form');
    if (!form) return;

    const stepper = new window.Stepper(wizard, { linear: false });
    const typeInputs = form.querySelectorAll('[data-case-type-input]');
    const typeCards = form.querySelectorAll('[data-case-type-card]');
    const casePanels = form.querySelectorAll('[data-case-panel]');
    const typeSummary = form.querySelector('[data-case-summary="case_type"]');
    const caseNumberSummary = form.querySelector('[data-case-summary="case_number"]');
    const caseNumberPreview = form.querySelector('[data-case-summary-preview="case_number"]');
    const partiesSummary = form.querySelector('[data-case-summary="parties"]');
    const judgmentDateInput = form.querySelector('[data-judgment-date-input]');
    const appealToggle = form.querySelector('[data-has-appeal-toggle]');
    const appealPanel = form.querySelector('[data-appeal-panel]');
    const appealDeadlineDate = form.querySelector('[data-appeal-deadline-date]');
    const appealDeadlineNote = form.querySelector('[data-appeal-deadline-note]');

    const getSelectedType = () => form.querySelector('[data-case-type-input]:checked')?.value || 'civil';
    const getFieldValue = name =>
      [...form.querySelectorAll(`[name="${name}"]`)].find(field => !field.disabled)?.value?.trim() || '';

    const updateSummary = () => {
      const typeLabel = getSelectedType() === 'criminal' ? 'جنائية' : 'مدنية';
      const caseNumber = getFieldValue('case_number');
      const primary = getFieldValue('primary_party_name');
      const opponent = getFieldValue('opponent_party_name');

      if (typeSummary) typeSummary.textContent = typeLabel;
      if (caseNumberSummary) caseNumberSummary.textContent = caseNumber || '—';
      if (caseNumberPreview) caseNumberPreview.textContent = caseNumber || 'لم يتم إدخال رقم الدعوى بعد';
      if (partiesSummary) partiesSummary.textContent = primary && opponent ? `${primary} / ${opponent}` : '—';
    };

    const debouncedUpdateSummary = debounce(updateSummary, 100);

    const updateAppealDeadline = () => {
      if (!appealDeadlineDate || !appealDeadlineNote) return;

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

      if (isNaN(judgmentDate.getTime())) {
        appealDeadlineDate.textContent = 'تاريخ غير صالح';
        appealDeadlineNote.textContent = 'يرجى مراجعة تاريخ الحكم.';
        return;
      }

      const deadline = new Date(judgmentDate);
      deadline.setDate(deadline.getDate() + 40);

      const remainingDays = Math.round((deadline.getTime() - new Date().setHours(0, 0, 0, 0)) / (24 * 60 * 60 * 1000));

      appealDeadlineDate.textContent = formatDateYmd(deadline);
      appealDeadlineNote.textContent =
        remainingDays >= 0
          ? `متبقي ${remainingDays} يومًا حتى نهاية مهلة الاستئناف.`
          : `انتهت مهلة الاستئناف منذ ${Math.abs(remainingDays)} يومًا.`;
    };

    const syncAppealPanel = () => {
      if (!appealPanel) return;

      const show = getSelectedType() === 'civil' && Boolean(appealToggle?.checked);

      appealPanel.classList.toggle('d-none', !show);
      if (show) togglePanelControls(appealPanel, true);
    };

    const syncTypePanels = () => {
      const selectedType = getSelectedType();

      typeCards.forEach(card => {
        card.classList.toggle('is-active', card.dataset.caseTypeCard === selectedType);
      });

      casePanels.forEach(panel => {
        const isActive = panel.dataset.casePanel === selectedType;

        panel.classList.toggle('d-none', !isActive);
        if (isActive) togglePanelControls(panel, true);
      });

      if (selectedType !== 'civil' && appealToggle) appealToggle.checked = false;

      syncAppealPanel();
      updateSummary();
      updateAppealDeadline();
    };

    const refreshFlatpickr = () =>
      setTimeout(() => {
        document.querySelectorAll('.flatpickr-date').forEach(el => el._flatpickr?.redraw());
      }, 150);

    wizard.querySelectorAll('.btn-next, .btn-prev').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.classList.contains('btn-next') ? stepper.next() : stepper.previous();
        refreshFlatpickr();
      });
    });

    typeInputs.forEach(input => input.addEventListener('change', syncTypePanels));
    appealToggle?.addEventListener('change', syncAppealPanel);
    judgmentDateInput?.addEventListener('change', updateAppealDeadline);

    form.addEventListener('input', e => {
      if (e.target instanceof HTMLInputElement || e.target instanceof HTMLTextAreaElement) debouncedUpdateSummary();
    });

    form.addEventListener('change', e => {
      if (e.target instanceof HTMLSelectElement) updateSummary();
    });

    window.addEventListener('pagehide', debouncedUpdateSummary.cancel, { once: true });

    syncTypePanels();
    wizard.dataset.caseWizardReady = 'true';
  });
}
