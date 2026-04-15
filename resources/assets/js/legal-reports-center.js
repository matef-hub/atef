'use strict';

const REPORT_TYPE_LABELS = {
  all: 'جميع السجلات القانونية',
  general_contracts: 'العقود العامة',
  contractor_specific: 'تقرير حسب المقاول',
  expired_leases: 'الإيجارات المنتهية'
};

const REPORT_DATA_TABLE_LANGUAGE = {
  search: '',
  searchPlaceholder: 'ابحث...',
  lengthMenu: 'عرض _MENU_',
  info: 'عرض _START_ إلى _END_ من _TOTAL_ سجل',
  infoEmpty: 'لا توجد بيانات مطابقة',
  infoFiltered: '(تمت التصفية من إجمالي _MAX_ سجل)',
  zeroRecords: 'لا توجد نتائج مطابقة للفلاتر الحالية',
  emptyTable: 'لا توجد بيانات قانونية متاحة حالياً',
  paginate: {
    next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
    previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
    first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
    last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
  }
};

function bootLegalReportsCenter() {
  document.querySelectorAll('[data-legal-reports-center]').forEach(initLegalReportsCenter);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootLegalReportsCenter);
} else {
  bootLegalReportsCenter();
}

function initLegalReportsCenter(root) {
  if (root.dataset.reportsReady === 'true') {
    return;
  }

  if (typeof window.DataTable === 'undefined') {
    return;
  }

  const datasetNode = root.querySelector('[data-report-dataset]');
  const tableElement = root.querySelector('[data-report-table]');

  if (!datasetNode || !tableElement) {
    return;
  }

  let rows = [];

  try {
    rows = JSON.parse(datasetNode.textContent || '[]');
  } catch (error) {
    console.warn('[legal-reports-center] Failed to parse report dataset.', error);
    return;
  }

  const normalizedRows = rows.map(normalizeRow);
  const filters = {
    type: root.querySelector('[data-report-filter="type"]'),
    from: root.querySelector('[data-report-filter="from"]'),
    to: root.querySelector('[data-report-filter="to"]'),
    keyword: root.querySelector('[data-report-filter="keyword"]')
  };

  if (filters.from && !filters.from.value) {
    filters.from.value = root.dataset.defaultPeriodFrom || '';
  }

  if (filters.to && !filters.to.value) {
    filters.to.value = root.dataset.defaultPeriodTo || '';
  }

  const dataTable = new window.DataTable(tableElement, {
    data: [],
    columns: buildColumns(),
    order: [],
    autoWidth: false,
    responsive: true,
    pageLength: 10,
    searching: false,
    language: REPORT_DATA_TABLE_LANGUAGE,
    layout: {
      topStart: {
        features: [
          {
            buttons: [
              {
                extend: 'excelHtml5',
                className: 'btn btn-outline-success',
                title: 'Legal Reports Center',
                filename: 'legal-reports-center',
                exportOptions: {
                  columns: ':visible',
                  format: {
                    body: value => extractPlainText(value)
                  }
                }
              }
            ]
          }
        ]
      },
      topEnd: null,
      bottomStart: {
        rowClass: 'row mx-3 justify-content-between',
        features: ['info']
      },
      bottomEnd: 'paging'
    },
    initComplete() {
      decorateReportDataTableUi(root);
    }
  });

  const state = {
    root,
    allRows: normalizedRows,
    filteredRows: [],
    table: dataTable,
    filters,
    title: root.querySelector('[data-report-print-name]'),
    period: root.querySelector('[data-report-period-badge]'),
    resultsBadge: root.querySelector('[data-report-results-badge]'),
    statement: root.querySelector('[data-report-statement]'),
    contractorSummaryBody: root.querySelector('[data-contractor-summary-body]'),
    printTitle: root.querySelector('[data-report-print-name]'),
    printPeriod: root.querySelector('[data-report-print-period]'),
    printDate: root.querySelector('[data-report-print-date]'),
    statTotal: root.querySelector('[data-report-stat="total"]'),
    statContracts: root.querySelector('[data-report-stat="contracts"]'),
    statExpired: root.querySelector('[data-report-stat="expired"]'),
    statContractors: root.querySelector('[data-report-stat="contractors"]')
  };

  bindFilterEvents(state);
  bindActionButtons(state);
  applyFilters(state);

  root.dataset.reportsReady = 'true';
}

function normalizeRow(row) {
  const filterDate = parseYmd(row.filter_date);

  return {
    ...row,
    start_date: row.start_date || '',
    end_date: row.end_date || '',
    filter_date: row.filter_date || '',
    filterDateObject: filterDate,
    contractor_id: row.contractor_id || 'CTR-UNASSIGNED',
    contractor_name: row.contractor_name || 'غير محدد',
    search_index: `${row.search_index || ''}`.toLowerCase()
  };
}

function buildColumns() {
  return [
    {
      title: 'نوع التقرير',
      data: 'display_type',
      className: 'text-nowrap'
    },
    {
      title: 'المرجع',
      data: 'reference',
      className: 'text-nowrap fw-semibold'
    },
    {
      title: 'تفاصيل العقد',
      data: null,
      render(data, type, row) {
        if (type !== 'display') {
          return [row.subject, row.contract_details].filter(Boolean).join(' - ');
        }

        return `
          <div class="legal-report-cell">
            <span class="legal-report-cell__title">${escapeHtml(row.subject)}</span>
            <span class="legal-report-cell__meta">${escapeHtml(row.contract_details || 'لا توجد تفاصيل إضافية')}</span>
          </div>
        `;
      }
    },
    {
      title: 'الأطراف / المقاول',
      data: null,
      render(data, type, row) {
        if (type !== 'display') {
          return [row.contractor_id, row.contractor_name, row.parties].filter(Boolean).join(' - ');
        }

        return `
          <div class="legal-report-cell">
            <span class="legal-report-cell__title">${escapeHtml(row.contractor_name)}</span>
            <span class="legal-report-cell__meta">${escapeHtml(row.contractor_id)} • ${escapeHtml(row.parties || 'غير محدد')}</span>
          </div>
        `;
      }
    },
    {
      title: 'الحالة القانونية',
      data: null,
      className: 'text-nowrap',
      render(data, type, row) {
        if (type !== 'display') {
          return row.status;
        }

        return `<span class="badge bg-label-${escapeHtml(row.status_class)}">${escapeHtml(row.status)}</span>`;
      }
    },
    {
      title: 'تاريخ البداية',
      data: 'start_date',
      className: 'text-nowrap',
      render(data, type) {
        return type === 'display' ? escapeHtml(data || '—') : data || '';
      }
    },
    {
      title: 'تاريخ النهاية / العقد',
      data: 'end_date',
      className: 'text-nowrap',
      render(data, type) {
        return type === 'display' ? escapeHtml(data || '—') : data || '';
      }
    },
    {
      title: 'البيان النصي',
      data: 'text_report',
      render(data, type) {
        const safeData = data || 'لا يوجد بيان نصي إضافي';
        return type === 'display'
          ? `<span class="legal-report-inline-text">${escapeHtml(safeData)}</span>`
          : safeData;
      }
    }
  ];
}

function bindFilterEvents(state) {
  Object.values(state.filters).forEach(element => {
    if (!element) {
      return;
    }

    const eventName = element.tagName === 'SELECT' ? 'change' : 'input';
    element.addEventListener(eventName, () => applyFilters(state));

    if (eventName !== 'change') {
      element.addEventListener('change', () => applyFilters(state));
    }
  });
}

function bindActionButtons(state) {
  const printButton = state.root.querySelector('[data-report-print-button]');
  const exportButtons = state.root.querySelectorAll('[data-report-export-button]');

  printButton?.addEventListener('click', () => {
    runPrintableReport(state, { updateTitle: false });
  });

  exportButtons.forEach(button => {
    const exportType = button.dataset.reportExportButton;

    button.addEventListener('click', () => {
      if (exportType === 'excel') {
        state.table.button(0).trigger();
        return;
      }

      if (exportType === 'pdf') {
        runPrintableReport(state, { updateTitle: true });
        return;
      }

      if (exportType === 'docx') {
        exportCurrentViewToDocx(state);
      }
    });
  });
}

function applyFilters(state) {
  const type = state.filters.type?.value || 'all';
  const keyword = (state.filters.keyword?.value || '').trim().toLowerCase();
  const fromDate = parseYmd(state.filters.from?.value || '');
  const toDate = parseYmd(state.filters.to?.value || '');
  const from = fromDate && toDate && fromDate > toDate ? toDate : fromDate;
  const to = fromDate && toDate && fromDate > toDate ? fromDate : toDate;

  let filteredRows = state.allRows.filter(row => {
    if (!matchesReportType(row, type)) {
      return false;
    }

    if (!matchesDateRange(row, from, to)) {
      return false;
    }

    if (keyword && !row.search_index.includes(keyword)) {
      return false;
    }

    return true;
  });

  filteredRows = sortRowsForView(filteredRows, type);
  state.filteredRows = filteredRows;

  state.table.clear();
  state.table.rows.add(filteredRows);
  state.table.draw();

  decorateReportDataTableUi(state.root);
  updateSummaryCards(state, filteredRows);
  updateStatement(state, filteredRows, type, from, to);
  updateContractorSummary(state, filteredRows);
  updatePrintMeta(state, filteredRows, type, from, to);
}

function matchesReportType(row, type) {
  if (type === 'general_contracts') {
    return row.record_type === 'contract';
  }

  if (type === 'contractor_specific') {
    return row.record_type === 'contract';
  }

  if (type === 'expired_leases') {
    return row.record_type === 'lease' && Boolean(row.is_expired);
  }

  return true;
}

function matchesDateRange(row, from, to) {
  if (!from && !to) {
    return true;
  }

  if (!row.filterDateObject) {
    return false;
  }

  if (from && row.filterDateObject < from) {
    return false;
  }

  if (to && row.filterDateObject > to) {
    return false;
  }

  return true;
}

function sortRowsForView(rows, type) {
  const sortedRows = [...rows];

  sortedRows.sort((left, right) => {
    const leftTime = left.filterDateObject ? left.filterDateObject.getTime() : 0;
    const rightTime = right.filterDateObject ? right.filterDateObject.getTime() : 0;

    if (type === 'contractor_specific') {
      const contractorCompare = left.contractor_name.localeCompare(right.contractor_name, 'ar');

      if (contractorCompare !== 0) {
        return contractorCompare;
      }
    }

    return rightTime - leftTime;
  });

  return sortedRows;
}

function updateSummaryCards(state, rows) {
  const contractRows = rows.filter(row => row.record_type === 'contract');
  const expiredLeaseRows = rows.filter(row => row.record_type === 'lease' && row.is_expired);
  const contractorCount = new Set(contractRows.map(row => row.contractor_id)).size;

  if (state.statTotal) {
    state.statTotal.textContent = formatNumber(rows.length);
  }

  if (state.statContracts) {
    state.statContracts.textContent = formatNumber(contractRows.length);
  }

  if (state.statExpired) {
    state.statExpired.textContent = formatNumber(expiredLeaseRows.length);
  }

  if (state.statContractors) {
    state.statContractors.textContent = formatNumber(contractorCount);
  }

  if (state.resultsBadge) {
    state.resultsBadge.textContent = `${formatNumber(rows.length)} سجل`;
  }
}

function updateStatement(state, rows, type, from, to) {
  if (!state.statement) {
    return;
  }

  const contractRows = rows.filter(row => row.record_type === 'contract');
  const leaseRows = rows.filter(row => row.record_type === 'lease');
  const expiredLeaseRows = leaseRows.filter(row => row.is_expired);
  const completedContracts = contractRows.filter(row => row.status === 'مكتمل').length;
  const pendingContracts = contractRows.filter(row => row.status === 'قيد الاستكمال').length;
  const newContracts = contractRows.filter(row => row.status === 'جديد').length;
  const visiblePeriod = resolvePeriodLabel(rows, from, to);
  const reportTypeLabel = REPORT_TYPE_LABELS[type] || REPORT_TYPE_LABELS.all;

  state.statement.innerHTML = `
    <p class="mb-2">
      يوضح هذا البيان المكتبي نتائج <strong>${escapeHtml(reportTypeLabel)}</strong> خلال
      <strong>${escapeHtml(visiblePeriod)}</strong>، بعد تطبيق البحث المباشر على السجلات القانونية الحالية.
    </p>
    <p class="mb-2">
      إجمالي النتائج المعروضة هو <strong>${formatNumber(rows.length)}</strong> سجل، منها
      <strong>${formatNumber(contractRows.length)}</strong> عقداً عاماً و
      <strong>${formatNumber(leaseRows.length)}</strong> عقد إيجار، مع
      <strong>${formatNumber(expiredLeaseRows.length)}</strong> إيجاراً منتهياً يحتاج متابعة قانونية.
    </p>
    <p class="mb-0">
      حالة العقود الحالية تتوزع بين <strong>${formatNumber(completedContracts)}</strong> عقود مكتملة،
      <strong>${formatNumber(pendingContracts)}</strong> عقود قيد الاستكمال،
      و<strong>${formatNumber(newContracts)}</strong> عقود جديدة، دون عرض أي بيانات مالية أو محاسبية.
    </p>
  `;
}

function updateContractorSummary(state, rows) {
  if (!state.contractorSummaryBody) {
    return;
  }

  const grouped = new Map();

  rows.filter(row => row.record_type === 'contract').forEach(row => {
    const current = grouped.get(row.contractor_id) || {
      contractor_id: row.contractor_id,
      contractor_name: row.contractor_name,
      contracts_count: 0,
      latest_contract_date: ''
    };

    current.contracts_count += 1;

    if (!current.latest_contract_date || (row.end_date && row.end_date > current.latest_contract_date)) {
      current.latest_contract_date = row.end_date || row.start_date || row.filter_date || '';
    }

    grouped.set(row.contractor_id, current);
  });

  const summaryRows = [...grouped.values()].sort((left, right) => {
    if (right.contracts_count !== left.contracts_count) {
      return right.contracts_count - left.contracts_count;
    }

    return left.contractor_name.localeCompare(right.contractor_name, 'ar');
  });

  if (summaryRows.length === 0) {
    state.contractorSummaryBody.innerHTML = `
      <tr>
        <td colspan="4" class="text-center text-muted py-4">لا توجد عقود مطابقة لعرض ملخص المقاولين.</td>
      </tr>
    `;
    return;
  }

  state.contractorSummaryBody.innerHTML = summaryRows
    .map(
      row => `
        <tr>
          <td class="text-nowrap fw-semibold">${escapeHtml(row.contractor_id)}</td>
          <td>${escapeHtml(row.contractor_name)}</td>
          <td>${formatNumber(row.contracts_count)}</td>
          <td class="text-nowrap">${escapeHtml(row.latest_contract_date || '—')}</td>
        </tr>
      `
    )
    .join('');
}

function updatePrintMeta(state, rows, type, from, to) {
  const reportTypeLabel = REPORT_TYPE_LABELS[type] || REPORT_TYPE_LABELS.all;
  const periodLabel = resolvePeriodLabel(rows, from, to);
  const generatedOn = state.root.dataset.generatedOn || formatYmd(new Date());

  if (state.printTitle) {
    state.printTitle.textContent = reportTypeLabel;
  }

  if (state.printPeriod) {
    state.printPeriod.textContent = `الفترة: ${periodLabel}`;
  }

  if (state.printDate) {
    state.printDate.textContent = `تاريخ الإصدار: ${generatedOn}`;
  }

  if (state.period) {
    state.period.textContent = `الفترة الحالية: ${periodLabel}`;
  }
}

function runPrintableReport(state, { updateTitle }) {
  const currentTitle = document.title;
  const nextTitle = updateTitle ? buildReportFileStem(state) : currentTitle;
  const originalLength = state.table.page.len();
  let restored = false;

  const restore = () => {
    if (restored) {
      return;
    }

    restored = true;
    document.title = currentTitle;
    window.removeEventListener('afterprint', restore);
    state.table.page.len(originalLength).draw();
    decorateReportDataTableUi(state.root);
  };

  document.title = nextTitle;
  state.table.page.len(-1).draw();
  decorateReportDataTableUi(state.root);

  window.addEventListener('afterprint', restore, { once: true });
  window.setTimeout(() => {
    window.print();
    window.setTimeout(restore, 1200);
  }, 80);
}

async function exportCurrentViewToDocx(state) {
  if (typeof window.JSZip === 'undefined') {
    console.warn('[legal-reports-center] JSZip is required to export DOCX files.');
    return;
  }

  const contractorSummaryRows = collectContractorSummaryRows(state.filteredRows);
  const reportTitle = state.printTitle?.textContent || REPORT_TYPE_LABELS.all;
  const reportPeriod = state.printPeriod?.textContent || 'الفترة: كافة الفترات';
  const reportDate = state.printDate?.textContent || `تاريخ الإصدار: ${state.root.dataset.generatedOn || formatYmd(new Date())}`;
  const statementText = extractPlainText(state.statement?.innerHTML || '');
  const fileStem = buildReportFileStem(state);

  const zip = new window.JSZip();

  zip.file(
    '[Content_Types].xml',
    `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
        <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
        <Default Extension="xml" ContentType="application/xml"/>
        <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
        <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
        <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
        <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
      </Types>`
  );

  zip.folder('_rels')?.file(
    '.rels',
    `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
        <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
        <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
      </Relationships>`
  );

  zip.folder('docProps')?.file(
    'app.xml',
    `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"
        xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
        <Application>Codex</Application>
        <TitlesOfParts>
          <vt:vector size="1" baseType="lpstr">
            <vt:lpstr>${escapeXml(reportTitle)}</vt:lpstr>
          </vt:vector>
        </TitlesOfParts>
      </Properties>`
  );

  zip.folder('docProps')?.file(
    'core.xml',
    `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:dcterms="http://purl.org/dc/terms/"
        xmlns:dcmitype="http://purl.org/dc/dcmitype/"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <dc:title>${escapeXml(reportTitle)}</dc:title>
        <dc:creator>Codex</dc:creator>
        <cp:lastModifiedBy>Codex</cp:lastModifiedBy>
        <dcterms:created xsi:type="dcterms:W3CDTF">${new Date().toISOString()}</dcterms:created>
        <dcterms:modified xsi:type="dcterms:W3CDTF">${new Date().toISOString()}</dcterms:modified>
      </cp:coreProperties>`
  );

  zip.folder('word')?.file(
    'styles.xml',
    `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
        <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
          <w:name w:val="Normal"/>
          <w:qFormat/>
          <w:pPr>
            <w:jc w:val="right"/>
            <w:bidi/>
          </w:pPr>
        </w:style>
      </w:styles>`
  );

  zip.folder('word')?.folder('_rels')?.file(
    'document.xml.rels',
    `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
      </Relationships>`
  );

  const reportTableRows = state.filteredRows.map(row => [
    row.display_type,
    row.reference,
    row.subject,
    `${row.contractor_name} / ${row.contractor_id}`,
    row.status,
    row.start_date || '—',
    row.end_date || '—',
    row.text_report
  ]);

  const contractorTableRows = contractorSummaryRows.map(row => [
    row.contractor_id,
    row.contractor_name,
    `${row.contracts_count}`,
    row.latest_contract_date || '—'
  ]);

  zip.folder('word')?.file(
    'document.xml',
    `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"
        xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
        xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
        xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
        xmlns:v="urn:schemas-microsoft-com:vml"
        xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"
        xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
        xmlns:w10="urn:schemas-microsoft-com:office:word"
        xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
        xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"
        xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"
        xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"
        xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
        xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"
        mc:Ignorable="w14 wp14">
        <w:body>
          ${buildDocxParagraph('مكتب الأستاذ محمد عاطف - المحامي', { bold: true, size: 34 })}
          ${buildDocxParagraph('Professional Office Statement', { size: 24 })}
          ${buildDocxParagraph(reportTitle, { bold: true, size: 28 })}
          ${buildDocxParagraph(reportPeriod)}
          ${buildDocxParagraph(reportDate)}
          ${buildDocxParagraph(statementText)}
          ${buildDocxParagraph('نتائج التقرير', { bold: true, size: 26, spacingBefore: 240 })}
          ${buildDocxTable(
            ['نوع التقرير', 'المرجع', 'تفاصيل العقد', 'المقاول / المعرف', 'الحالة', 'تاريخ البداية', 'تاريخ النهاية / العقد', 'البيان النصي'],
            reportTableRows
          )}
          ${buildDocxParagraph('ملخص المقاولين', { bold: true, size: 26, spacingBefore: 240 })}
          ${buildDocxTable(['معرف المقاول', 'اسم المقاول', 'عدد العقود', 'آخر تاريخ عقد'], contractorTableRows)}
          <w:sectPr>
            <w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/>
            <w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720" w:header="708" w:footer="708" w:gutter="0"/>
          </w:sectPr>
        </w:body>
      </w:document>`
  );

  const blob = await zip.generateAsync({
    type: 'blob',
    mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
  });

  downloadBlob(blob, `${fileStem}.docx`);
}

function collectContractorSummaryRows(rows) {
  const grouped = new Map();

  rows.filter(row => row.record_type === 'contract').forEach(row => {
    const current = grouped.get(row.contractor_id) || {
      contractor_id: row.contractor_id,
      contractor_name: row.contractor_name,
      contracts_count: 0,
      latest_contract_date: ''
    };

    current.contracts_count += 1;

    if (!current.latest_contract_date || (row.end_date && row.end_date > current.latest_contract_date)) {
      current.latest_contract_date = row.end_date || row.start_date || row.filter_date || '';
    }

    grouped.set(row.contractor_id, current);
  });

  return [...grouped.values()].sort((left, right) => right.contracts_count - left.contracts_count);
}

function buildDocxParagraph(text, options = {}) {
  const safeText = escapeXml(text || '');
  const size = options.size || 22;
  const spacingBefore = options.spacingBefore || 0;
  const boldXml = options.bold ? '<w:b/>' : '';

  return `
    <w:p>
      <w:pPr>
        <w:jc w:val="right"/>
        <w:bidi/>
        <w:spacing w:before="${spacingBefore}" w:after="120"/>
      </w:pPr>
      <w:r>
        <w:rPr>
          ${boldXml}
          <w:sz w:val="${size}"/>
          <w:szCs w:val="${size}"/>
          <w:rtl/>
        </w:rPr>
        <w:t xml:space="preserve">${safeText}</w:t>
      </w:r>
    </w:p>
  `;
}

function buildDocxTable(headers, rows) {
  const safeRows = rows.length > 0 ? rows : [['لا توجد نتائج مطابقة للفلاتر الحالية']];
  const headerRow = `
    <w:tr>
      ${headers
        .map(
          header => `
            <w:tc>
              <w:tcPr>
                <w:shd w:fill="E9EEF7"/>
              </w:tcPr>
              ${buildDocxParagraph(header, { bold: true, size: 22 })}
            </w:tc>
          `
        )
        .join('')}
    </w:tr>
  `;

  const bodyRows = safeRows
    .map(
      row => `
        <w:tr>
          ${row
            .map(
              cell => `
                <w:tc>
                  ${buildDocxParagraph(cell || '—')}
                </w:tc>
              `
            )
            .join('')}
        </w:tr>
      `
    )
    .join('');

  return `
    <w:tbl>
      <w:tblPr>
        <w:tblW w:w="0" w:type="auto"/>
        <w:tblBorders>
          <w:top w:val="single" w:sz="8" w:space="0" w:color="8A8D93"/>
          <w:left w:val="single" w:sz="8" w:space="0" w:color="8A8D93"/>
          <w:bottom w:val="single" w:sz="8" w:space="0" w:color="8A8D93"/>
          <w:right w:val="single" w:sz="8" w:space="0" w:color="8A8D93"/>
          <w:insideH w:val="single" w:sz="8" w:space="0" w:color="8A8D93"/>
          <w:insideV w:val="single" w:sz="8" w:space="0" w:color="8A8D93"/>
        </w:tblBorders>
        <w:bidiVisual/>
      </w:tblPr>
      ${headerRow}
      ${bodyRows}
    </w:tbl>
  `;
}

function buildReportFileStem(state) {
  const reportName = (state.printTitle?.textContent || 'legal-report')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w\u0600-\u06FF-]+/g, '');
  const generatedOn = state.root.dataset.generatedOn || formatYmd(new Date());

  return `${reportName || 'legal-report'}-${generatedOn}`;
}

function resolvePeriodLabel(rows, from, to) {
  if (from || to) {
    return `من ${from ? formatYmd(from) : 'بداية السجلات'} إلى ${to ? formatYmd(to) : 'آخر السجلات'}`;
  }

  const availableDates = rows
    .map(row => row.filter_date)
    .filter(Boolean)
    .sort();

  if (availableDates.length === 0) {
    return 'كافة الفترات';
  }

  return `من ${availableDates[0]} إلى ${availableDates[availableDates.length - 1]}`;
}

function decorateReportDataTableUi(root) {
  root.querySelectorAll('.dt-buttons').forEach(element => element.classList.add('d-none'));
  root.querySelectorAll('.dt-length .form-select').forEach(element => element.classList.remove('form-select-sm'));
  root.querySelectorAll('.dt-layout-end, .dt-layout-start').forEach(element => element.classList.add('mt-0'));
  root.querySelectorAll('.dt-layout-full').forEach(element => {
    element.classList.remove('col-md', 'col-12');
    element.classList.add('table-responsive');
  });
}

function extractPlainText(value) {
  if (typeof value !== 'string') {
    return value ?? '';
  }

  if (!value.includes('<')) {
    return value;
  }

  const parser = new DOMParser();
  const documentFragment = parser.parseFromString(value, 'text/html');

  return (documentFragment.body.textContent || documentFragment.body.innerText || '').replace(/\s+/g, ' ').trim();
}

function parseYmd(value) {
  if (!value) {
    return null;
  }

  const parsedDate = new Date(`${value}T00:00:00`);
  return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
}

function formatYmd(date) {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function formatNumber(value) {
  return new Intl.NumberFormat('ar-EG').format(value || 0);
}

function escapeHtml(value) {
  return `${value ?? ''}`
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function escapeXml(value) {
  return `${value ?? ''}`
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

function downloadBlob(blob, filename) {
  const blobUrl = URL.createObjectURL(blob);
  const link = document.createElement('a');

  link.href = blobUrl;
  link.download = filename;
  document.body.append(link);
  link.click();
  link.remove();

  window.setTimeout(() => {
    URL.revokeObjectURL(blobUrl);
  }, 1000);
}
