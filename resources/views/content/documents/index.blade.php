@extends('layouts/layoutMaster')

@section('title', 'المستندات')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
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

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <x-page-alerts />

    <div class="card">
      <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
        <div>
          <h4 class="card-title mb-1">المستندات</h4>
          <p class="card-subtitle mb-0">إدارة كل المستندات الرسمية وتجديدتها ونسخها الإلكترونية.</p>
        </div>
        <x-button type="button" onclick="window.location='{{ route('documents.create') }}'">
          <i class="icon-base ti tabler-plus me-1"></i>
          إضافة مستند
        </x-button>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table border-top legal-datatable">
          <thead>
            <tr>
              <th>اسم المستند</th>
              <th>رقم المستند</th>
              <th>جهة الإصدار</th>
              <th>تاريخ الإصدار</th>
              <th>تاريخ الانتهاء</th>
              <th>الملف</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($documents as $document)
              @php
                $documentPreviewTitle = $document->docu_name ?: 'معاينة الملف';
                $documentFileName = $document->docu_pdf
                    ? basename($document->docu_pdf)
                    : basename(parse_url($document->docu_pdf_url ?? '', PHP_URL_PATH) ?: '');
                $documentFileExtension = strtolower(pathinfo($documentFileName, PATHINFO_EXTENSION));
              @endphp
              <tr>
                <td>{{ $document->docu_name }}</td>
                <td>{{ $document->doc_numer }}</td>
                <td>{{ $document->docu_issu_from }}</td>
                <td>{{ optional($document->docu_iss_date)->format('Y-m-d') }}</td>
                <td>{{ optional($document->docu_expiry_date)->format('Y-m-d') }}</td>
                <td>
                  @if ($document->docu_pdf_url)
                    <a href=""javascript:void(0); rel="noopener noreferrer"
                      class="btn btn-sm btn-icon btn-label-primary" data-document-preview-trigger
                      data-preview-url="{{ $document->docu_pdf_url }}" data-preview-title="{{ $documentPreviewTitle }}"
                      @if ($documentFileExtension) data-preview-extension="{{ $documentFileExtension }}" @endif
                      data-bs-toggle="tooltip" title="عرض الملف">
                      <i class="ti tabler-file-search"></i>
                    </a>
                  @else
                    <span class="badge badge-dot bg-secondary" data-bs-toggle="tooltip" title="بدون ملف"></span>
                  @endif
                </td>
                <td class="legal-table-actions">
                  <div class="d-flex justify-content-end">
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                      </button>

                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('documents.edit', $document) }}">
                          <i class="icon-base ti tabler-pencil me-1"></i> تعديل
                        </a>
                        <div class="dropdown-divider"></div>

                        <form action="{{ route('documents.destroy', $document) }}" method="POST"
                          data-swal-confirm="true" data-swal-title="تأكيد الحذف"
                          data-swal-text="سيتم حذف المستند نهائيًا ولا يمكن التراجع عن هذا الإجراء."
                          data-swal-icon="warning" data-swal-confirm-button="نعم، احذف المستند"
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
