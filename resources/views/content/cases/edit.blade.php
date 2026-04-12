@extends('layouts/layoutMaster')

@section('title', 'تعديل قضية')

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
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">تعديل القضية {{ $case->case_number }}</h4>
        <p class="mb-0 text-muted">يمكنك تعديل البيانات أو إضافة مرفقات جديدة أو الانتقال مباشرة إلى سجل الجلسات المرتبط بهذه القضية.</p>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('hearings.create', ['case' => $case->id]) }}" class="btn btn-primary">
          <i class="icon-base ti tabler-calendar-plus me-1"></i>
          تسجيل جلسة
        </a>
        <a href="{{ route('hearings.index', ['case' => $case->id]) }}" class="btn btn-outline-secondary">
          <i class="icon-base ti tabler-list-details me-1"></i>
          عرض الجلسات
        </a>
      </div>
    </div>

    <x-page-alerts />

    @include('content.cases._form', [
        'case' => $case,
        'action' => route('cases.update', $case),
        'method' => 'PUT',
        'submitLabel' => 'حفظ التعديلات',
    ])
  </div>
@endsection
