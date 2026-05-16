import { CONFIG, LANG } from './config';

export default function initLegalDataTables() {
  if (!CONFIG.hasDataTable) return;

  document.querySelectorAll('.legal-datatable').forEach(table => {
    if (table.dataset.datatableReady === 'true') return;

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
          features: [{ pageLength: { menu: [10, 25, 50, 100], text: 'اعرض_MENU_' } }]
        },
        topEnd: { search: { placeholder: 'ابحث...' } },
        bottomStart: { rowClass: 'row mx-3 justify-content-between', features: ['info'] },
        bottomEnd: 'paging'
      },
      language: LANG,
      initComplete() {
        [
          { selector: '.dt-search .form-control', remove: ['form-control-sm'], add: ['ms-0'] },
          { selector: '.dt-length .form-select', remove: ['form-select-sm'], add: [] },
          { selector: '.dt-layout-end', remove: [], add: ['gap-md-2', 'gap-0', 'mt-0'] },
          { selector: '.dt-layout-start', remove: [], add: ['mt-0'] },
          { selector: '.dt-layout-full', remove: ['col-md', 'col-12'], add: ['table-responsive'] }
        ].forEach(({ selector, remove, add }) => {
          document.querySelectorAll(selector).forEach(el => {
            remove.forEach(c => el.classList.remove(c));
            add.forEach(c => el.classList.add(c));
          });
        });
      }
    });

    table.dataset.datatableReady = 'true';
  });
}
