@extends('layouts/layoutMaster')

@section('title', 'المستندات')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

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
              <tr>
                <td>{{ $document->docu_name }}</td>
                <td>{{ $document->doc_numer }}</td>
                <td>{{ $document->docu_issu_from }}</td>
                <td>{{ optional($document->docu_iss_date)->format('Y-m-d') }}</td>
                <td>{{ optional($document->docu_expiry_date)->format('Y-m-d') }}</td>
                <td>
                  @if ($document->docu_pdf_url)
                    <a href="{{ $document->docu_pdf_url }}" target="_blank" class="btn btn-sm btn-icon btn-label-primary"
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

                        <form action="{{ route('documents.destroy', $document) }}" method="POST" data-swal-confirm="true"
                          data-swal-title="تأكيد الحذف"
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
