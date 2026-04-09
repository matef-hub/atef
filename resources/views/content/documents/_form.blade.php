@php
  $document = $document ?? null;
  $isUpdate = isset($method) && strtoupper($method) === 'PUT';
@endphp

<div class="card">
  <div class="card-header">
    <h4 class="card-title mb-1">{{ $document ? 'تعديل بيانات المستند' : 'إضافة مستند جديد' }}</h4>
    <p class="card-subtitle mb-0">احتفظ ببيانات الإصدار والانتهاء مع النسخة الإلكترونية للمستند.</p>
  </div>
  <div class="card-body">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" novalidate
      @if ($isUpdate) data-swal-confirm="true"
        data-swal-title="تأكيد التعديل"
        data-swal-text="سيتم حفظ التعديلات على بيانات المستند الحالي."
        data-swal-icon="question"
        data-swal-confirm-button="نعم، حفظ التعديلات"
        data-swal-cancel-button="إلغاء" @endif>
      @csrf
      @isset($method)
        @method($method)
      @endisset

      <div class="row g-4">
        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="docu_name" value="اسم المستند" />
          <x-input id="docu_name" name="docu_name" class="{{ $errors->has('docu_name') ? 'is-invalid' : '' }}"
            value="{{ old('docu_name', $document?->docu_name) }}" />
          <x-input-error for="docu_name" />
        </div>

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="doc_numer" value="رقم المستند" />
          <x-input id="doc_numer" name="doc_numer" class="{{ $errors->has('doc_numer') ? 'is-invalid' : '' }}"
            value="{{ old('doc_numer', $document?->doc_numer) }}" />
          <x-input-error for="doc_numer" />
        </div>

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="docu_issu_from" value="جهة الإصدار" />
          <x-input id="docu_issu_from" name="docu_issu_from"
            class="{{ $errors->has('docu_issu_from') ? 'is-invalid' : '' }}"
            value="{{ old('docu_issu_from', $document?->docu_issu_from) }}" />
          <x-input-error for="docu_issu_from" />
        </div>

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="docu_iss_date" value="تاريخ الإصدار" />
          <x-input id="docu_iss_date" name="docu_iss_date" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('docu_iss_date') ? 'is-invalid' : '' }}"
            value="{{ old('docu_iss_date', optional($document?->docu_iss_date)->format('Y-m-d')) }}"
            autocomplete="off" />
          <x-input-error for="docu_iss_date" />
        </div>

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="docu_expiry_date" value="تاريخ الانتهاء" />
          <x-input id="docu_expiry_date" name="docu_expiry_date" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('docu_expiry_date') ? 'is-invalid' : '' }}"
            value="{{ old('docu_expiry_date', optional($document?->docu_expiry_date)->format('Y-m-d')) }}"
            autocomplete="off" />
          <x-input-error for="docu_expiry_date" />
        </div>

        <x-storage-uploader name="docu_pdf" label="ملف المستند" :current-url="$document?->docu_pdf_url" :current-name="$document?->docu_pdf ? basename($document->docu_pdf) : null" />

        <div class="col-12 d-flex justify-content-end gap-2">
          <x-secondary-button type="button"
            onclick="window.location='{{ route('documents.index') }}'">رجوع</x-secondary-button>
          <x-button>{{ $submitLabel }}</x-button>
        </div>
      </div>
    </form>
  </div>
</div>
