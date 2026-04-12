@extends('layouts/layoutMaster')

@section('title', 'القضايا')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
  @php
    $today = now()->startOfDay();
    $civilCasesCount = $cases->where('case_type', \App\Models\LegalCase::TYPE_CIVIL)->count();
    $criminalCasesCount = $cases->where('case_type', \App\Models\LegalCase::TYPE_CRIMINAL)->count();
    $activeAppealWindows = $cases->filter(fn($case) => $case->appeal_deadline_at && !$case->has_appeal && $case->appeal_deadline_at->greaterThanOrEqualTo($today));
  @endphp

  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <x-page-alerts />

    <div class="row g-4">
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-primary">
          <div class="card-body">
            <span class="badge bg-label-primary rounded-pill mb-2">إجمالي</span>
            <h3 class="mb-1">{{ number_format($cases->count()) }}</h3>
            <p class="mb-0 text-muted">عدد القضايا المسجلة بالنظام.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-success">
          <div class="card-body">
            <span class="badge bg-label-success rounded-pill mb-2">مدنية</span>
            <h3 class="mb-1">{{ number_format($civilCasesCount) }}</h3>
            <p class="mb-0 text-muted">القضايا المدنية والمنظورة أمام المحاكم.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-warning">
          <div class="card-body">
            <span class="badge bg-label-warning rounded-pill mb-2">جنائية</span>
            <h3 class="mb-1">{{ number_format($criminalCasesCount) }}</h3>
            <p class="mb-0 text-muted">القضايا الجنائية والجنح المفتوحة.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-info">
          <div class="card-body">
            <span class="badge bg-label-info rounded-pill mb-2">مهلة استئناف</span>
            <h3 class="mb-1">{{ number_format($activeAppealWindows->count()) }}</h3>
            <p class="mb-0 text-muted">قضايا مدنية ما زالت داخل مهلة الأربعين يومًا.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
        <div>
          <h4 class="card-title mb-1">سجل القضايا</h4>
          <p class="card-subtitle mb-0">عرض موحد للقضايا المدنية والجنائية والاستئنافات والمرفقات والجلسات.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <a href="{{ route('hearings.index') }}" class="btn btn-outline-secondary">
            <i class="icon-base ti tabler-calendar-event me-1"></i>
            الجلسات
          </a>
          <x-button type="button" onclick="window.location='{{ route('cases.create') }}'">
            <i class="icon-base ti tabler-plus me-1"></i>
            إضافة قضية
          </x-button>
        </div>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table border-top legal-datatable">
          <thead>
            <tr>
              <th>النوع</th>
              <th>رقم القضية</th>
              <th>الخصوم</th>
              <th>بيانات الجهة</th>
              <th>أول جلسة</th>
              <th>الاستئناف</th>
              <th>المرفقات</th>
              <th>الجلسات</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($cases as $case)
              <tr>
                <td>
                  <span class="badge bg-label-{{ $case->isCivil() ? 'primary' : 'warning' }}">{{ $case->case_type_label }}</span>
                </td>
                <td>
                  <a href="{{ route('cases.edit', $case) }}" class="fw-semibold text-primary">{{ $case->case_number }}</a>
                </td>
                <td>
                  <div class="d-flex flex-column">
                    <span class="fw-medium">{{ $case->primary_party_name }}</span>
                    <small class="text-muted">{{ $case->opponent_party_name }}</small>
                  </div>
                </td>
                <td>
                  @if ($case->isCivil())
                    <div class="d-flex flex-column">
                      <span>{{ $case->primary_court_name ?: 'غير محددة' }}</span>
                      <small class="text-muted">الدائرة {{ $case->circuit_number ?: '—' }}</small>
                    </div>
                  @else
                    <div class="d-flex flex-column">
                      <span>تحرير المحضر {{ optional($case->report_date)->format('Y-m-d') ?: '—' }}</span>
                      <small class="text-muted">حصر الحبس {{ $case->detention_order_number ?: 'غير مسجل' }}</small>
                    </div>
                  @endif
                </td>
                <td>{{ optional($case->first_session_at)->format('Y-m-d') }}</td>
                <td>
                  @if ($case->isCriminal())
                    <span class="text-muted small">غير مطبق</span>
                  @elseif ($case->has_appeal)
                    <div class="d-flex flex-column">
                      <span class="badge bg-label-success mb-1">تم تسجيل الاستئناف</span>
                      <small class="text-muted">{{ $case->appeal_number ?: 'بدون رقم' }}</small>
                    </div>
                  @elseif ($case->appeal_deadline_at)
                    <div class="d-flex flex-column">
                      <span class="badge bg-label-{{ $case->appeal_deadline_at->greaterThanOrEqualTo($today) ? 'info' : 'danger' }} mb-1">
                        {{ $case->appeal_deadline_at->greaterThanOrEqualTo($today) ? 'المهلة مفتوحة' : 'انتهت المهلة' }}
                      </span>
                      <small class="text-muted">حتى {{ $case->appeal_deadline_at->format('Y-m-d') }}</small>
                    </div>
                  @else
                    <span class="text-muted small">لا يوجد حكم مسجل</span>
                  @endif
                </td>
                <td><span class="badge bg-label-secondary">{{ $case->attachments_count }}</span></td>
                <td><span class="badge bg-label-secondary">{{ $case->hearings_count }}</span></td>
                <td class="legal-table-actions">
                  <div class="d-flex justify-content-end">
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                      </button>

                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('cases.edit', $case) }}">
                          <i class="icon-base ti tabler-pencil me-1"></i> تعديل
                        </a>
                        <a class="dropdown-item" href="{{ route('hearings.create', ['case' => $case->id]) }}">
                          <i class="icon-base ti tabler-calendar-plus me-1"></i> تسجيل جلسة
                        </a>
                        <a class="dropdown-item" href="{{ route('hearings.index', ['case' => $case->id]) }}">
                          <i class="icon-base ti tabler-list-details me-1"></i> عرض الجلسات
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('cases.destroy', $case) }}" method="POST" data-swal-confirm="true"
                          data-swal-title="تأكيد حذف القضية"
                          data-swal-text="سيتم حذف القضية وكل المرفقات والجلسات المرتبطة بها نهائيًا."
                          data-swal-icon="warning" data-swal-confirm-button="نعم، احذف القضية"
                          data-swal-cancel-button="إلغاء">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger">
                            <i class="icon-base ti tabler-trash me-1"></i> حذف
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
