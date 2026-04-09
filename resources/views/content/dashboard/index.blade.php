@extends('layouts/layoutMaster')

@section('title', 'لوحة التحكم القانونية')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
  @php
    $inflowSeries = collect($contractInflow['series']);
    $latestMonthContracts = $inflowSeries->last() ?? 0;
    $averageInflow = $inflowSeries->avg();
  @endphp

  <div class="legal-dashboard-page d-flex flex-column gap-4" dir="rtl">
    <x-page-alerts />

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <span class="badge bg-label-primary rounded-pill mb-2">Legal Operations Hub</span>
        <h4 class="mb-1">لوحة التحكم القانونية</h4>
        <p class="mb-0 text-muted">متابعة العقود والملفات القانونية والتنبيهات العاجلة من شاشة واحدة.</p>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('contracts.create') }}" class="btn btn-primary">
          <i class="icon-base ti tabler-plus me-1"></i>
          إضافة عقد
        </a>
        <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
          <i class="icon-base ti tabler-layout-grid me-1"></i>
          عرض العقود
        </a>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-primary legal-stat-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div>
                <h6 class="mb-1">إجمالي العقود</h6>
                <p class="text-muted mb-3">نظرة سريعة على حجم التعاقدات المسجلة.</p>
                <h3 class="mb-1">{{ number_format($totalContracts) }}</h3>
                <small class="text-primary">تمت إضافة {{ number_format($latestMonthContracts) }} هذا الشهر</small>
              </div>
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="icon-base ti tabler-file-text icon-28px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-warning legal-stat-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div>
                <h6 class="mb-1">القضايا / الملفات النشطة</h6>
                <p class="text-muted mb-3">الملفات القانونية الجارية الصالحة للمتابعة.</p>
                <h3 class="mb-1">{{ number_format($activeCasesCount) }}</h3>
                <small class="text-warning">تحت إشراف ومراجعة الإدارة القانونية</small>
              </div>
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="icon-base ti tabler-gavel icon-28px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-info legal-stat-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div>
                <h6 class="mb-1">التوقيعات المعلقة</h6>
                <p class="text-muted mb-3">عقود تحتاج استكمال دورة الاعتماد والتوقيع.</p>
                <h3 class="mb-1">{{ number_format($pendingSignaturesCount) }}</h3>
                <small class="text-info">يجب مراجعتها قبل الإقفال النهائي</small>
              </div>
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="icon-base ti tabler-signature icon-28px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-danger legal-stat-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div>
                <h6 class="mb-1">التنبيهات الحرجة</h6>
                <p class="text-muted mb-3">عناصر تنتهي قريبًا وتحتاج تدخلًا مباشرًا.</p>
                <h3 class="mb-1">{{ number_format($urgentAlertsCount) }}</h3>
                <small class="text-danger">
                  {{ $expiringRentContracts->count() }} عقود + {{ $expiringDocuments->count() }} مستندات
                </small>
              </div>
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="icon-base ti tabler-alert-triangle icon-28px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-8">
        <div class="card h-100">
          <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
              <h5 class="card-title mb-1">Contract Inflow</h5>
              <p class="card-subtitle mb-0">حجم العقود الجديدة خلال آخر 6 أشهر.</p>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <span class="badge bg-label-primary">الإجمالي: {{ number_format($inflowSeries->sum()) }}</span>
              <span class="badge bg-label-secondary">المتوسط الشهري: {{ number_format($averageInflow, 1) }}</span>
            </div>
          </div>

          <div class="card-body">
            <div class="legal-metric-strip mb-4">
              <div class="legal-metric-chip">
                <span class="legal-metric-chip__label">ذروة التدفق</span>
                <strong>{{ number_format($inflowSeries->max() ?? 0) }} عقد</strong>
              </div>
              <div class="legal-metric-chip">
                <span class="legal-metric-chip__label">أحدث شهر</span>
                <strong>{{ number_format($latestMonthContracts) }} عقد</strong>
              </div>
            </div>

            <div id="legalContractInflowChart" data-legal-contract-inflow-chart
              data-categories='@json($contractInflow['categories'])'
              data-series='@json($contractInflow['series'])'></div>
          </div>
        </div>
      </div>

      <div class="col-xl-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">Action Required</h5>
            <p class="card-subtitle mb-0">تنبيهات تحتاج متابعة خلال الأيام السبعة القادمة.</p>
          </div>

          <div class="card-body legal-alert-stack">
            @if ($expiringRentContracts->isEmpty() && $expiringDocuments->isEmpty())
              <div class="alert alert-success mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                  <i class="icon-base ti tabler-circle-check icon-18px"></i>
                  <strong>لا توجد عناصر عاجلة حاليًا.</strong>
                </div>
                <div class="small mt-2">كل العقود والمستندات ضمن النطاق الآمن في الوقت الحالي.</div>
              </div>
            @endif

            @if ($expiringRentContracts->isNotEmpty())
              <div>
                <h6 class="legal-section-label">عقود تستحق التجديد قريبًا</h6>

                @foreach ($expiringRentContracts as $rentContract)
                  <div class="alert alert-warning legal-action-alert" role="alert">
                    <div class="d-flex align-items-start gap-3">
                      <span class="badge bg-label-warning rounded p-2">
                        <i class="icon-base ti tabler-calendar-time icon-18px"></i>
                      </span>

                      <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ $rentContract['title'] }}</div>
                        @if ($rentContract['subtitle'])
                          <div class="small text-muted mb-2">{{ $rentContract['subtitle'] }}</div>
                        @endif
                        <div class="small">
                          ينتهي في {{ $rentContract['expires_at'] }}
                          @if (!is_null($rentContract['days_remaining']))
                            <span class="text-warning-emphasis">• متبقٍ {{ $rentContract['days_remaining'] }} أيام</span>
                          @endif
                        </div>
                      </div>
                    </div>

                    <a href="{{ $rentContract['renew_url'] }}" class="btn btn-sm btn-warning">
                      <i class="icon-base ti tabler-refresh me-1"></i>
                      Renew
                    </a>
                  </div>
                @endforeach
              </div>
            @endif

            @if ($expiringDocuments->isNotEmpty())
              <div>
                <h6 class="legal-section-label">مستندات تحتاج مراجعة عاجلة</h6>

                @foreach ($expiringDocuments as $document)
                  <div class="alert alert-info legal-action-alert" role="alert">
                    <div class="d-flex align-items-start gap-3">
                      <span class="badge bg-label-info rounded p-2">
                        <i class="icon-base ti tabler-file-alert icon-18px"></i>
                      </span>

                      <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ $document['title'] }}</div>
                        <div class="small text-muted mb-2">رقم المستند: {{ $document['number'] }}</div>
                        <div class="small">ينتهي في {{ $document['expires_at'] }}</div>
                      </div>
                    </div>

                    <a href="{{ $document['review_url'] }}" class="btn btn-sm btn-info">
                      <i class="icon-base ti tabler-eye me-1"></i>
                      مراجعة
                    </a>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">Latest Legal Activities</h5>
            <p class="card-subtitle mb-0">آخر التحديثات على العقود والملفات والإيجارات.</p>
          </div>

          <div class="card-body">
            @if ($latestActivities->isEmpty())
              <div class="alert alert-secondary mb-0" role="alert">
                لا توجد أنشطة حديثة لعرضها بعد.
              </div>
            @else
              <ul class="timeline mb-0">
                @foreach ($latestActivities as $activity)
                  <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point timeline-point-{{ $activity['badge_class'] }}"></span>
                    <div class="timeline-event">
                      <div class="timeline-header mb-2">
                        <h6 class="mb-0">{{ $activity['title'] }}</h6>
                        <small class="text-muted">{{ $activity['time'] }}</small>
                      </div>

                      <p class="mb-2">{{ $activity['description'] }}</p>

                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-label-{{ $activity['badge_class'] }}">
                          <i class="icon-base ti {{ $activity['icon'] }} me-1"></i>
                          {{ $activity['badge'] }}
                        </span>

                        <a href="{{ $activity['url'] }}" class="small text-primary">
                          فتح السجل
                        </a>
                      </div>
                    </div>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        </div>
      </div>

      <div class="col-xl-7">
        <div class="card h-100">
          <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
              <h5 class="card-title mb-1">Recently Added Contracts</h5>
              <p class="card-subtitle mb-0">آخر العقود المضافة مع حالة الاستكمال والتوقيع.</p>
            </div>

            <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-outline-primary">
              <i class="icon-base ti tabler-arrow-up-right me-1"></i>
              جميع العقود
            </a>
          </div>

          <div class="card-datatable table-responsive">
            <table class="table border-top legal-datatable">
              <thead>
                <tr>
                  <th>Contract #</th>
                  <th>Project</th>
                  <th>Supplier</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($recentContracts as $contract)
                  <tr>
                    <td>
                      <a href="{{ $contract['edit_url'] }}" class="fw-semibold text-primary">
                        {{ $contract['number'] }}
                      </a>
                    </td>
                    <td>{{ $contract['project'] }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                          <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ $contract['supplier_initials'] }}
                          </span>
                        </div>
                        <div class="d-flex flex-column">
                          <span class="fw-medium">{{ $contract['supplier'] }}</span>
                          <small class="text-muted">Supplier</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-label-{{ $contract['status_class'] }}">
                        {{ $contract['status_label'] }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-4 text-muted">لا توجد عقود حديثة لعرضها.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
