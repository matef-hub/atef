@extends('layouts/layoutMaster')

@section('title', 'تعديل إيجار')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('content')
  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">تعديل عقد الإيجار</h4>
        <p class="mb-0 text-muted">حدّث البيانات الحالية أو استبدل الملفات عند الحاجة.</p>
      </div>
      <x-secondary-button type="button" onclick="window.location='{{ route('rents.index') }}'">الرجوع
        للقائمة</x-secondary-button>
    </div>

    <x-page-alerts />

    @include('content.rents._form', [
        'rent' => $rent,
        'action' => route('rents.update', $rent),
        'method' => 'PUT',
        'submitLabel' => 'تحديث عقد الإيجار',
    ])
  </div>
@endsection
