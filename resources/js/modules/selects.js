import { CONFIG } from './config';

const SELECTORS = ['.legal-single-select', '.legal-multi-select', '.legal-project-combobox', '.legal-tag-select'];

export default function initLegalSelects() {
  if (!CONFIG.hasjQuery) return;

  const selector = SELECTORS.join(',');

  if (!document.querySelector(selector)) return;

  window.jQuery(selector).each(function () {
    const field = window.jQuery(this);

    if (field.hasClass('select2-hidden-accessible')) return;

    const isProject = field.hasClass('legal-project-combobox') || field.hasClass('legal-tag-select');
    const tagsEnabled = isProject || field.data('tags') === true || field.data('tags') === 'true';

    if (!field.parent().hasClass('position-relative')) {
      field.wrap('<div class="position-relative"></div>');
    }

    field.select2({
      tags: tagsEnabled,
      dir: 'rtl',
      width: '100%',
      allowClear: true,
      dropdownParent: field.parent(),
      placeholder: field.data('placeholder') || (isProject ? 'اختر أو اكتب' : 'اختر من القائمة'),
      closeOnSelect: !field.hasClass('legal-multi-select'),
      createTag: tagsEnabled
        ? params => {
            const term = window.jQuery.trim(params.term);

            return term
              ? {
                  id: term,
                  text: term,
                  newTag: true
                }
              : null;
          }
        : undefined,
      language: {
        noResults: () => field.data('no-results') || 'لا توجد نتائج'
      }
    });
  });
}
