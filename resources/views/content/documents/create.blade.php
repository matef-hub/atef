@extends('layouts/layoutMaster')

@section('title', 'إضافة مستند')

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
        <h4 class="mb-1">إضافة مستند جديد</h4>
        <p class="mb-0 text-muted">أدخل بيانات المستند الرسمية وارفع النسخة الإلكترونية المناسبة.</p>
      </div>
      <x-secondary-button type="button" onclick="window.location='{{ route('documents.index') }}'">الرجوع
        للقائمة</x-secondary-button>
    </div>

    <x-page-alerts />

    @include('content.documents._form', [
        'action' => route('documents.store'),
        'submitLabel' => 'حفظ المستند',
    ])
  </div>
@endsection
