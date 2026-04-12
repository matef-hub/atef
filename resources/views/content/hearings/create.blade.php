@extends('layouts/layoutMaster')

@section('title', 'تسجيل جلسة')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-style')
  @vite(['resources/css/legal-resources.css'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">تسجيل جلسة جديدة</h4>
        <p class="mb-0 text-muted">اختر القضية وسجل قرار المحكمة والجلسة القادمة في مكان واحد.</p>
      </div>
      <x-secondary-button type="button" onclick="window.location='{{ route('hearings.index', $selectedCaseId ? ['case' => $selectedCaseId] : []) }}'">الرجوع للقائمة</x-secondary-button>
    </div>

    <x-page-alerts />

    @include('content.hearings._form', [
        'action' => route('hearings.store'),
        'submitLabel' => 'حفظ الجلسة',
        'selectedCaseId' => $selectedCaseId,
    ])
  </div>
@endsection
