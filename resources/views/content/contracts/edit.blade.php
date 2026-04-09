@extends('layouts/layoutMaster')

@section('title', 'تعديل عقد')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">تعديل العقد</h4>
        <p class="mb-0 text-muted">حدّث البيانات أو استبدل ملف العقد الحالي وقت ما تحتاج.</p>
      </div>
      <x-secondary-button type="button" onclick="window.location='{{ route('contracts.index') }}'">الرجوع
        للقائمة</x-secondary-button>
    </div>

    <x-page-alerts />

    @include('content.contracts._form', [
        'contract' => $contract,
        'action' => route('contracts.update', $contract),
        'method' => 'PUT',
        'submitLabel' => 'تحديث العقد',
        'projectNames' => $projectNames,
        'supplierNames' => $supplierNames,
    ])
  </div>
@endsection
