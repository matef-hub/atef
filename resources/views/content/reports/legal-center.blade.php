@extends('layouts/layoutMaster')

@section('title', 'مركز التقارير القانونية')

@section('vendor-style')
  @vite([
      'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
      'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
      'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  ])
@endsection

@section('page-style')
  @vite(['resources/css/legal-reports-center.css'])
@endsection

@section('vendor-script')
  @vite([
      'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
      'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/legal-reports-center.js'])
@endsection

@section('content')
  <div class="legal-reports-page d-flex flex-column gap-4" dir="rtl" data-legal-reports-center
    data-today="{{ $today }}" data-generated-on="{{ $generatedOn }}" data-default-period-from="{{ $defaultPeriodFrom }}"
    data-default-period-to="{{ $defaultPeriodTo }}">
    <x-page-alerts />

    <div class="legal-print-header" data-report-print-header>
      <div class="legal-print-header__title">مكتب الأستاذ محمد عاطف - المحامي</div>
      <div class="legal-print-header__subtitle">Professional Office Statement</div>
      <div class="legal-print-header__meta">
        <span data-report-print-name>تقرير قانوني شامل</span>
        <span>•</span>
        <span data-report-print-period>الفترة: {{ $defaultPeriodFrom }} إلى {{ $defaultPeriodTo }}</span>
        <span>•</span>
        <span data-report-print-date>تاريخ الإصدار: {{ $generatedOn }}</span>
      </div>
    </div>

    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3"
      data-report-screen-title>
      <div>
        <span class="badge bg-label-primary rounded-pill mb-2">Legal Reports Center</span>
        <h4 class="mb-1">مركز التقارير القانونية</h4>
        <p class="mb-0 text-muted">
          صفحة مخصصة لإعداد تقارير قانونية نصية قابلة للطباعة والتصدير، مع استبعاد كامل لأي حقول مالية أو محاسبية.
        </p>
      </div>

      <div class="d-flex flex-wrap gap-2 legal-report-actions" data-report-actions>
        <button type="button" class="btn btn-primary" data-report-print-button>
          <i class="icon-base ti tabler-printer me-1"></i>
          طباعة التقرير
        </button>
        <button type="button" class="btn btn-outline-success" data-report-export-button="excel">
          <i class="icon-base ti tabler-file-spreadsheet me-1"></i>
          Excel
        </button>
        <button type="button" class="btn btn-outline-danger" data-report-export-button="pdf">
          <i class="icon-base ti tabler-file-type-pdf me-1"></i>
          PDF
        </button>
        <button type="button" class="btn btn-outline-info" data-report-export-button="docx">
          <i class="icon-base ti tabler-file-type-docx me-1"></i>
          DOCX
        </button>
      </div>
    </div>

    <div class="card legal-report-filter-card" data-report-filters>
      <div class="card-header">
        <h5 class="card-title mb-1">تصفية مباشرة للتقارير</h5>
        <p class="card-subtitle mb-0">تتحدث النتائج والملخصات تلقائياً أثناء الكتابة أو تغيير نوع التقرير والفترة الزمنية.</p>
      </div>

      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-3 col-md-6">
            <label class="form-label" for="legalReportType">نوع التقرير</label>
            <select id="legalReportType" class="form-select" data-report-filter="type">
              <option value="all">جميع السجلات القانونية</option>
              <option value="general_contracts">العقود العامة</option>
              <option value="contractor_specific">تقرير حسب المقاول</option>
              <option value="expired_leases">الإيجارات المنتهية</option>
            </select>
          </div>

          <div class="col-lg-3 col-md-6">
            <label class="form-label" for="legalReportDateFrom">من تاريخ</label>
            <input id="legalReportDateFrom" type="text" class="form-control flatpickr-date" placeholder="YYYY-MM-DD"
              autocomplete="off" data-report-filter="from" />
          </div>

          <div class="col-lg-3 col-md-6">
            <label class="form-label" for="legalReportDateTo">إلى تاريخ</label>
            <input id="legalReportDateTo" type="text" class="form-control flatpickr-date" placeholder="YYYY-MM-DD"
              autocomplete="off" data-report-filter="to" />
          </div>

          <div class="col-lg-3 col-md-6">
            <label class="form-label" for="legalReportSearch">بحث بالكلمات المفتاحية</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
              <input id="legalReportSearch" type="search" class="form-control" placeholder="رقم العقد، المقاول، الوحدة..."
                autocomplete="off" data-report-filter="keyword" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-primary" data-report-summary-card>
          <div class="card-body">
            <span class="badge bg-label-primary rounded-pill mb-2">النتائج الحالية</span>
            <h3 class="mb-1" data-report-stat="total">0</h3>
            <p class="mb-0 text-muted">عدد السجلات المعروضة بعد تطبيق البحث والتصفية المباشرة.</p>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-success" data-report-summary-card>
          <div class="card-body">
            <span class="badge bg-label-success rounded-pill mb-2">العقود العامة</span>
            <h3 class="mb-1" data-report-stat="contracts">0</h3>
            <p class="mb-0 text-muted">العقود التي تركز على الحالة القانونية والتواريخ والتفاصيل التعاقدية فقط.</p>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-danger" data-report-summary-card>
          <div class="card-body">
            <span class="badge bg-label-danger rounded-pill mb-2">الإيجارات المنتهية</span>
            <h3 class="mb-1" data-report-stat="expired">0</h3>
            <p class="mb-0 text-muted">عقود الإيجار التي تجاوز تاريخ انتهائها تاريخ اليوم وتحتاج متابعة قانونية.</p>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-info" data-report-summary-card>
          <div class="card-body">
            <span class="badge bg-label-info rounded-pill mb-2">المقاولون النشطون</span>
            <h3 class="mb-1" data-report-stat="contractors">0</h3>
            <p class="mb-0 text-muted">عدد معرفات المقاولين الظاهرة داخل العقود الحالية في التقرير.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-1">البيان النصي للمكتب</h5>
        <p class="card-subtitle mb-0">ملخص احترافي يتبدل وفق نتائج البحث الحالية ويظهر داخل نسخة الطباعة أيضاً.</p>
      </div>

      <div class="card-body">
        <div class="legal-report-statement" data-report-statement>
          <p class="mb-0 text-muted">جارٍ تجهيز البيان القانوني الحالي...</p>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
          <h5 class="card-title mb-1">ملخص المقاولين</h5>
          <p class="card-subtitle mb-0">يتم التجميع حسب معرف المقاول المشتق من بيانات المقاول الحالية داخل العقود.</p>
        </div>

        <span class="badge bg-label-secondary align-self-start align-self-lg-center" data-report-period-badge>
          الفترة الحالية: {{ $defaultPeriodFrom }} إلى {{ $defaultPeriodTo }}
        </span>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table border-top legal-report-summary-table mb-0">
          <thead>
            <tr>
              <th>معرف المقاول</th>
              <th>اسم المقاول</th>
              <th>عدد العقود</th>
              <th>آخر تاريخ عقد</th>
            </tr>
          </thead>
          <tbody data-contractor-summary-body>
            <tr>
              <td colspan="4" class="text-center text-muted py-4">لا توجد بيانات متاحة حالياً.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
          <h5 class="card-title mb-1">نتائج التقرير</h5>
          <p class="card-subtitle mb-0">جدول عربي بعرض كامل ومهيأ للطباعة والتصدير وفق الفلاتر الحالية.</p>
        </div>

        <span class="badge bg-label-primary align-self-start align-self-lg-center" data-report-results-badge>
          0 سجل
        </span>
      </div>

      <div class="card-datatable table-responsive" data-report-table-wrapper>
        <table class="table border-top legal-report-results-table w-100" data-report-table></table>
      </div>
    </div>

    <script type="application/json" data-report-dataset>
      @json($reportRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    </script>
  </div>
@endsection
