@extends('layouts/layoutMaster')

@section('title', 'الإيجارات')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@push('modals')
  <div class="modal fade legal-file-preview-modal" id="documents-preview-modal" tabindex="-1"
    aria-labelledby="documents-preview-title" aria-hidden="true" role="dialog" data-document-preview-modal
    data-preview-default-title="معاينة الملف">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-truncate" id="documents-preview-title" data-document-preview-title>معاينة الملف</h5>
          <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body">
          <div id="documents-preview-stage" class="legal-file-preview-stage" data-document-preview-stage
            aria-live="polite">
            <div class="legal-pdf-placeholder" data-pdf-empty role="status">
              <i class="icon-base ti tabler-file-search text-primary mb-2" aria-hidden="true"></i>
              <h6 class="mb-1">لا يوجد ملف متاح للمعاينة</h6>
              <p class="mb-0 text-muted text-center">اختر مستندًا من الجدول لعرضه هنا</p>
            </div>
            <div class="legal-pdf-placeholder d-none" data-pdf-unsupported role="status">
              <i class="icon-base ti tabler-file-alert text-warning mb-2" aria-hidden="true"></i>
              <h6 class="mb-1">لا توجد معاينة مباشرة لهذا الملف</h6>
              <p class="mb-0 text-muted text-center">المعاينة متاحة لملفات PDF فقط</p>
            </div>
            <iframe class="legal-pdf-frame d-none" data-pdf-frame title="معاينة الملف"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
@endpush

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <x-page-alerts />

    <div class="card">
      <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
        <div>
          <h4 class="card-title mb-1">الإيجارات</h4>
          <p class="card-subtitle mb-0">إدارة عقود الإيجار وبيانات الوحدات والقيم المالية والملفات المرفقة.</p>
        </div>
        <x-button type="button" onclick="window.location='{{ route('rents.create') }}'">
          <i class="icon-base ti tabler-plus me-1"></i>
          إضافة عقد إيجار
        </x-button>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table border-top legal-datatable">
          <thead>
            <tr>
              <th>اسم المستأجر</th>
              <th>اسم المؤجر</th>
              <th>بيانات الوحدة</th>
              <th>تاريخ التوقيع</th>
              <th>مدة العقد</th>
              <th>الإيجار الشهري</th>
              <th>الملفات</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rents as $rent)
              <tr>
                <td>{{ $rent->tenant_name }}</td>
                <td>{{ $rent->renter_name }}</td>
                <td>{{ $rent->home_data }}</td>
                <td>{{ optional($rent->date_sign)->format('Y-m-d') }}</td>
                <td>{{ $rent->date_duration }}</td>
                <td>{{ $rent->Monthly_rent }}</td>
                <td>
                  <div class="d-flex gap-1">
                    @if ($rent->con_pdf_url)
                      {{-- زر معاينة الـ PDF --}}
                      <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-label-danger"
                        data-document-preview-trigger data-preview-url="{{ $rent->con_pdf_url }}"
                        data-preview-title="معاينة عقد الإيجار" data-preview-extension="pdf" data-bs-toggle="tooltip"
                        title="عرض الملف">
                        <i class="ti tabler-file-type-pdf"></i>
                      </a>
                    @endif

                    @if ($rent->con_word_url)
                      {{-- زر الـ Word يبقى كما هو للتحميل لأن المتصفح لا يعرضه مباشرة --}}
                      <a href="{{ $rent->con_word_url }}" target="_blank" class="btn btn-sm btn-icon btn-label-info"
                        data-bs-toggle="tooltip" title="تحميل Word">
                        <i class="ti tabler-file-word"></i>
                      </a>
                    @endif

                    @if (!$rent->con_pdf_url && !$rent->con_word_url)
                      <span class="text-muted small">لا يوجد</span>
                    @endif
                  </div>
                </td>
                <td class="legal-table-actions">
                  <div class="d-flex justify-content-end">
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                      </button>

                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('rents.edit', $rent) }}">
                          <i class="icon-base ti tabler-pencil me-1"></i> تعديل
                        </a>
                        <div class="dropdown-divider"></div>

                        <form action="{{ route('rents.destroy', $rent) }}" method="POST" data-swal-confirm="true"
                          data-swal-title="تأكيد الحذف"
                          data-swal-text="سيتم حذف عقد الإيجار نهائيًا ولا يمكن التراجع عن هذا الإجراء."
                          data-swal-icon="warning" data-swal-confirm-button="نعم، احذف عقد الإيجار"
                          data-swal-cancel-button="إلغاء" id="delete-form-{{ $rent->id }}">
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
