@extends('layouts/layoutMaster')

@section('title', 'مركز التقارير القانونية')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('page-style')
  @vite(['resources/css/legal-reports-center.css'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/legal-reports-center.js'])
@endsection

@section('content')
  <div class="legal-reports-page d-flex flex-column gap-4" dir="rtl" data-legal-reports-center
    data-today="{{ $today }}" data-generated-on="{{ $generatedOn }}"
    data-default-period-from="{{ $defaultPeriodFrom }}" data-default-period-to="{{ $defaultPeriodTo }}">
    <x-page-alerts />

    <div class="legal-print-header" data-report-print-header>
      <div class="legal-print-header__label">نوع التقرير</div>
      <div class="legal-print-header__title" data-report-print-name>تقرير قانوني شامل</div>
      <div class="legal-print-header__period" data-report-print-period>
        الفترة: {{ $defaultPeriodFrom }} إلى {{ $defaultPeriodTo }}
      </div>
    </div>

    <div class="legal-report-hero" data-report-screen-title>
      <div class="legal-report-hero__content">
        <span class="legal-report-hero__eyebrow">Legal Reports Center</span>
        <h4>مركز التقارير القانونية</h4>
        <div class="legal-report-hero__meta">
          <span class="legal-report-hero__pill" data-report-period-badge>
            الفترة الحالية: {{ $defaultPeriodFrom }} إلى {{ $defaultPeriodTo }}
          </span>
        </div>
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
        <h5 class="card-title mb-0">تصفية مباشرة للتقارير</h5>
      </div>

      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-3 col-md-6">
            <label class="form-label" for="legalReportType">نوع التقرير</label>
            <select class="form-select" id="legalReportType" data-report-filter="type">
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
              <input id="legalReportSearch" type="search" class="form-control"
                placeholder="رقم العقد، المقاول، الوحدة..." autocomplete="off" data-report-filter="keyword" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 legal-report-stat-card legal-report-stat-card--primary" data-report-summary-card>
          <div class="card-body">
            <span class="legal-report-stat-card__label">النتائج الحالية</span>
            <h3 class="legal-report-stat-card__value" data-report-stat="total">0</h3>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 legal-report-stat-card legal-report-stat-card--success" data-report-summary-card>
          <div class="card-body">
            <span class="legal-report-stat-card__label">العقود العامة</span>
            <h3 class="legal-report-stat-card__value" data-report-stat="contracts">0</h3>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 legal-report-stat-card legal-report-stat-card--danger" data-report-summary-card>
          <div class="card-body">
            <span class="legal-report-stat-card__label">الإيجارات المنتهية</span>
            <h3 class="legal-report-stat-card__value" data-report-stat="expired">0</h3>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 legal-report-stat-card legal-report-stat-card--info" data-report-summary-card>
          <div class="card-body">
            <span class="legal-report-stat-card__label">المقاولون النشطون</span>
            <h3 class="legal-report-stat-card__value" data-report-stat="contractors">0</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="card" data-report-contractor-card>
      <div class="card-header">
        <h5 class="card-title mb-0">ملخص المقاولين</h5>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table legal-report-summary-table mb-0">
          <thead>
            <tr>
              <th>اسم المقاول</th>
              <th>عدد العقود</th>
              <th>آخر تاريخ عقد</th>
            </tr>
          </thead>
          <tbody data-contractor-summary-body>
            <tr>
              <td colspan="3" class="text-center text-muted py-4">لا توجد بيانات متاحة حالياً.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card legal-report-results-card">
      <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <h5 class="card-title mb-0">نتائج التقرير</h5>

        <span class="legal-report-results-badge align-self-start align-self-lg-center" data-report-results-badge>
          0 سجل
        </span>
      </div>

      <div class="card-datatable table-responsive" data-report-table-wrapper>
        <table class="table legal-report-results-table w-100" data-report-table></table>
      </div>
    </div>

    <script type="application/json" data-report-dataset>
      @json($reportRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    </script>
  </div>
@endsection
