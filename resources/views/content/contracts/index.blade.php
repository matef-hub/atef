@extends('layouts/layoutMaster')

@section('title', 'العقود')

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
          <h4 class="card-title mb-1">العقود</h4>
          <p class="card-subtitle mb-0">متابعة عقود المقاولات والملفات المرتبطة بيهم من شاشة واحدة.</p>
        </div>
        <x-button type="button" onclick="window.location='{{ route('contracts.create') }}'">
          <i class="icon-base ti tabler-plus me-1"></i>
          إضافة عقد
        </x-button>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table border-top legal-datatable">
          <thead>
            <tr>
              <th>رقم العقد</th>
              <th>اسم المشروع</th>
              <th>اسم المقاول</th>
              <th>تاريخ الإسناد</th>
              <th>تاريخ العقد</th>
              <th>جهات التوقيع</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($contracts as $contract)
              <tr>
                <td>{{ $contract->contr_number }}</td>
                <td>{{ $contract->proje_name }}</td>
                <td>{{ $contract->suppli_name }}</td>
                <td>{{ optional($contract->Esnad_date)->format('Y-m-d') }}</td>
                <td>{{ optional($contract->Contar_date)->format('Y-m-d') }}</td>
                <td class="text-nowrap">
                  @if (!empty($contract->sign))
                    <div class="d-flex align-items-center">
                      {{-- عرض أول حالة فقط --}}
                      <span class="badge bg-label-primary text-truncate" style="max-width: 110px;">
                        {{ $contract->sign[0] }}
                      </span>

                      {{-- لو فيه حالات تانية، اعرض دائرة صغيرة فوقها التول تيب --}}
                      @if (count($contract->sign) > 1)
                        <span class="ms-1 cursor-pointer text-primary" data-bs-toggle="tooltip" data-bs-html="true"
                          data-bs-placement="top"
                          title="<div class='text-start py-1'>
                                <strong>باقي الحالات:</strong><br>
                                @foreach (array_slice($contract->sign, 1) as $other)
• {{ $other }} <br>
@endforeach
                             </div>">
                          <i class="ti tabler-circle-plus fs-5"></i>
                        </span>
                      @endif
                    </div>
                  @else
                    <span class="text-muted small">لا يوجد</span>
                  @endif
                </td>
                <td class="legal-table-actions">
                  <div class="d-flex justify-content-end">
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                      </button>

                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('contracts.edit', $contract) }}">
                          <i class="icon-base ti tabler-pencil me-1"></i> تعديل
                        </a>

                        <div class="dropdown-divider"></div>

                        <form action="{{ route('contracts.destroy', $contract) }}" method="POST" data-swal-confirm="true"
                          data-swal-title="تأكيد الحذف"
                          data-swal-text="سيتم حذف العقد نهائيًا ولا يمكن التراجع عن هذا الإجراء."
                          data-swal-icon="warning" data-swal-confirm-button="نعم، احذف العقد"
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
