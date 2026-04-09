@extends('layouts/layoutMaster')

@section('title', 'إضافة إيجار')

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
        <h4 class="mb-1">إضافة عقد إيجار جديد</h4>
        <p class="mb-0 text-muted">أدخل البيانات وارفع النسخ المطلوبة بصيغة PDF وWord لو متاحة.</p>
      </div>
      <x-secondary-button type="button" onclick="window.location='{{ route('rents.index') }}'">الرجوع
        للقائمة</x-secondary-button>
    </div>

    <x-page-alerts />

    @include('content.rents._form', [
        'action' => route('rents.store'),
        'submitLabel' => 'حفظ عقد الإيجار',
    ])
  </div>
@endsection
