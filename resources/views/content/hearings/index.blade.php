@extends('layouts/layoutMaster')

@section('title', 'الجلسات')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <x-page-alerts />

    @if ($selectedCase)
      <div class="alert alert-primary mb-0" role="alert">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
          <div>
            <strong>عرض جلسات القضية:</strong> {{ $selectedCase->case_number }} - {{ $selectedCase->parties_summary }}
          </div>
          <a href="{{ route('cases.edit', $selectedCase) }}" class="btn btn-sm btn-primary">فتح القضية</a>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
        <div>
          <h4 class="card-title mb-1">سجل الجلسات</h4>
          <p class="card-subtitle mb-0">كل الجلسات وقرارات المحكمة والجلسات القادمة في جدول واحد.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
          @if ($selectedCase)
            <a href="{{ route('hearings.index') }}" class="btn btn-outline-secondary">عرض جميع الجلسات</a>
          @endif
          <a href="{{ route('hearing-calendar.index') }}" class="btn btn-outline-primary">
            <i class="icon-base ti tabler-calendar-event me-1"></i>
            تقويم الجلسات
          </a>
          <x-button type="button" onclick="window.location='{{ route('hearings.create', $selectedCase ? ['case' => $selectedCase->id] : []) }}'">
            <i class="icon-base ti tabler-plus me-1"></i>
            تسجيل جلسة
          </x-button>
        </div>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table border-top legal-datatable">
          <thead>
            <tr>
              <th>رقم الدعوى</th>
              <th>النوع</th>
              <th>تاريخ الجلسة</th>
              <th>رقم الرول</th>
              <th>قرار المحكمة</th>
              <th>الجلسة القادمة</th>
              <th>ملاحظات</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($hearings as $hearing)
              <tr>
                <td>
                  <div class="d-flex flex-column">
                    <a href="{{ route('cases.edit', $hearing->legalCase) }}" class="fw-semibold text-primary">
                      {{ $hearing->legalCase->case_number }}
                    </a>
                    <small class="text-muted">{{ $hearing->legalCase->parties_summary }}</small>
                  </div>
                </td>
                <td>
                  <span class="badge bg-label-{{ $hearing->legalCase->isCivil() ? 'primary' : 'warning' }}">
                    {{ $hearing->legalCase->case_type_label }}
                  </span>
                </td>
                <td>{{ optional($hearing->hearing_date)->format('Y-m-d') }}</td>
                <td>{{ $hearing->roll_number ?: '—' }}</td>
                <td class="text-wrap" style="max-width: 22rem;">{{ $hearing->court_decision }}</td>
                <td>{{ optional($hearing->next_hearing_at)->format('Y-m-d') ?: '—' }}</td>
                <td class="text-wrap" style="max-width: 18rem;">{{ $hearing->notes ?: '—' }}</td>
                <td class="legal-table-actions">
                  <div class="d-flex justify-content-end">
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                      </button>

                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('hearings.edit', $hearing) }}">
                          <i class="icon-base ti tabler-pencil me-1"></i> تعديل
                        </a>
                        <a class="dropdown-item" href="{{ route('cases.edit', $hearing->legalCase) }}">
                          <i class="icon-base ti tabler-scale me-1"></i> فتح القضية
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('hearings.destroy', $hearing) }}" method="POST" data-swal-confirm="true"
                          data-swal-title="تأكيد حذف الجلسة"
                          data-swal-text="سيتم حذف سجل هذه الجلسة نهائيًا."
                          data-swal-icon="warning" data-swal-confirm-button="نعم، احذف الجلسة"
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
