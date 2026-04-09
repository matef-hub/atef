@php
  $rent = $rent ?? null;
  $isUpdate = isset($method) && strtoupper($method) === 'PUT';
@endphp

<div class="card">
  <div class="card-header">
    <h4 class="card-title mb-1">{{ $rent ? 'تعديل عقد الإيجار' : 'إضافة عقد إيجار جديد' }}</h4>
    <p class="card-subtitle mb-0">سجل بيانات الوحدة والقيم المالية واربط ملفات العقد المطلوبة.</p>
  </div>
  <div class="card-body">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" novalidate
      @if ($isUpdate) data-swal-confirm="true"
        data-swal-title="تأكيد التعديل"
        data-swal-text="سيتم حفظ التعديلات على بيانات عقد الإيجار الحالي."
        data-swal-icon="question"
        data-swal-confirm-button="نعم، حفظ التعديلات"
        data-swal-cancel-button="إلغاء" @endif>
      @csrf
      @isset($method)
        @method($method)
      @endisset

      <div class="row g-4">
        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="tenant_name" value="اسم المستأجر" />
          <x-input id="tenant_name" name="tenant_name" class="{{ $errors->has('tenant_name') ? 'is-invalid' : '' }}"
            value="{{ old('tenant_name', $rent?->tenant_name) }}" />
          <x-input-error for="tenant_name" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="renter_name" value="اسم المؤجر" />
          <x-input id="renter_name" name="renter_name" class="{{ $errors->has('renter_name') ? 'is-invalid' : '' }}"
            value="{{ old('renter_name', $rent?->renter_name) }}" />
          <x-input-error for="renter_name" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="date_sign" value="تاريخ التوقيع" />
          <x-input id="date_sign" name="date_sign" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('date_sign') ? 'is-invalid' : '' }}"
            value="{{ old('date_sign', optional($rent?->date_sign)->format('Y-m-d')) }}" autocomplete="off" />
          <x-input-error for="date_sign" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="date_end" value="تاريخ النهاية" />
          <x-input id="date_end" name="date_end" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('date_end') ? 'is-invalid' : '' }}"
            value="{{ old('date_end', optional($rent?->date_end)->format('Y-m-d')) }}" autocomplete="off" />
          <x-input-error for="date_end" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="date_duration" value="مدة العقد" />
          <x-input id="date_duration" name="date_duration"
            class="{{ $errors->has('date_duration') ? 'is-invalid' : '' }}"
            value="{{ old('date_duration', $rent?->date_duration) }}" placeholder="مثال: 12 شهر" />
          <x-input-error for="date_duration" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="Monthly_rent" value="الإيجار الشهري" />
          <x-input id="Monthly_rent" name="Monthly_rent" type="number" step="0.01"
            class="{{ $errors->has('Monthly_rent') ? 'is-invalid' : '' }}"
            value="{{ old('Monthly_rent', $rent?->Monthly_rent) }}" />
          <x-input-error for="Monthly_rent" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="insurance_mon" value="قيمة التأمين" />
          <x-input id="insurance_mon" name="insurance_mon" type="number" step="0.01"
            class="{{ $errors->has('insurance_mon') ? 'is-invalid' : '' }}"
            value="{{ old('insurance_mon', $rent?->insurance_mon) }}" />
          <x-input-error for="insurance_mon" />
        </div>

        <x-storage-uploader name="con_pdf" label="ملف العقد PDF" accept=".pdf"
          wrapper-class="col-md-4 form-control-validation" :current-url="$rent?->con_pdf_url" :current-name="$rent?->con_pdf ? basename($rent->con_pdf) : null" />

        <x-storage-uploader name="con_word" label="ملف العقد Word" accept=".doc,.docx"
          wrapper-class="col-md-4 form-control-validation"
          :current-url="$rent?->con_word_url" :current-name="$rent?->con_word ? basename($rent->con_word) : null" />

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="home_data" value="بيانات الوحدة" />
          <textarea id="home_data" name="home_data" class="form-control {{ $errors->has('home_data') ? 'is-invalid' : '' }}"
            rows="1">{{ old('home_data', $rent?->home_data) }}</textarea>
          <x-input-error for="home_data" class="d-block" />
        </div>

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="add_notes" value="ملاحظات إضافية" />
          <textarea id="add_notes" name="add_notes" class="form-control {{ $errors->has('add_notes') ? 'is-invalid' : '' }}"
            rows="1">{{ old('add_notes', $rent?->add_notes) }}</textarea>
          <x-input-error for="add_notes" class="d-block" />
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <x-secondary-button type="button"
            onclick="window.location='{{ route('rents.index') }}'">رجوع</x-secondary-button>
          <x-button>{{ $submitLabel }}</x-button>
        </div>
      </div>
    </form>
  </div>
</div>
