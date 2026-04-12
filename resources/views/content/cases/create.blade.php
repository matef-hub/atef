@extends('layouts/layoutMaster')

@section('title', 'إضافة قضية')

@section('vendor-style')
  @vite([
      'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
      'resources/assets/vendor/libs/select2/select2.scss',
      'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
  ])
@endsection

@section('page-style')
  @vite(['resources/css/legal-resources.css'])
@endsection

@section('vendor-script')
  @vite([
      'resources/assets/vendor/libs/flatpickr/flatpickr.js',
      'resources/assets/vendor/libs/select2/select2.js',
      'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
  ])
@endsection

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">إضافة قضية جديدة</h4>
        <p class="mb-0 text-muted">مسار إدخال ذكي يبدأ بنوع القضية ثم يفتح الحقول المناسبة فقط لتسهيل العمل على الفريق.</p>
      </div>
      <x-secondary-button type="button" onclick="window.location='{{ route('cases.index') }}'">الرجوع للقائمة</x-secondary-button>
    </div>

    <x-page-alerts />

    @include('content.cases._form', [
        'action' => route('cases.store'),
        'submitLabel' => 'حفظ القضية',
    ])
  </div>
@endsection
