@extends('layouts/layoutMaster')

@section('title', 'إضافة عقد')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-style')
  @vite(['resources/css/legal-resources.css'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">إضافة عقد جديد</h4>
        <p class="mb-0 text-muted">املأ بيانات العقد الأساسية وارفع النسخة الإلكترونية.</p>
      </div>
      <x-secondary-button type="button" onclick="window.location='{{ route('contracts.index') }}'">الرجوع
        للقائمة</x-secondary-button>
    </div>

    <x-page-alerts />

    @include('content.contracts._form', [
        'action' => route('contracts.store'),
        'submitLabel' => 'حفظ العقد',
        'generatedContractNumber' => $generatedContractNumber,
        'projectNames' => $projectNames,
        'supplierNames' => $supplierNames,
    ])
  </div>
@endsection
