@php
  $caseHearing = $hearing ?? null;
@endphp

<div class="card">
  <div class="card-header">
    <h4 class="card-title mb-1">{{ $caseHearing ? 'تعديل بيانات الجلسة' : 'تسجيل جلسة جديدة' }}</h4>
    <p class="card-subtitle mb-0">سجل تاريخ الجلسة وقرار المحكمة والجلسة القادمة حتى يظل ملف القضية محدثًا دائمًا.</p>
  </div>

  <div class="card-body">
    <form action="{{ $action }}" method="POST" novalidate
      @if ($caseHearing) data-swal-confirm="true"
        data-swal-title="تأكيد تعديل الجلسة"
        data-swal-text="سيتم حفظ التعديلات الجديدة على هذه الجلسة."
        data-swal-icon="question"
        data-swal-confirm-button="نعم، احفظ التعديلات"
        data-swal-cancel-button="إلغاء" @endif>
      @csrf
      @isset($method)
        @method($method)
      @endisset

      <div class="row g-4">
        <div class="col-md-6">
          <x-label class="form-label" for="legal_case_id" value="رقم الدعوى" />
          <select id="legal_case_id" name="legal_case_id"
            class="form-select legal-single-select {{ $errors->has('legal_case_id') ? 'is-invalid' : '' }}"
            data-placeholder="اختر القضية">
            <option value=""></option>
            @foreach ($cases as $caseOption)
              <option value="{{ $caseOption['id'] }}"
                @selected((string) old('legal_case_id', $selectedCaseId) === (string) $caseOption['id'])>
                {{ $caseOption['label'] }} ({{ $caseOption['type_label'] }})
              </option>
            @endforeach
          </select>
          <x-input-error for="legal_case_id" class="d-block" />
        </div>

        <div class="col-md-6">
          <x-label class="form-label" for="hearing_date" value="تاريخ الجلسة" />
          <x-input id="hearing_date" name="hearing_date" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('hearing_date') ? 'is-invalid' : '' }}"
            value="{{ old('hearing_date', $caseHearing?->hearing_date?->format('Y-m-d')) }}" autocomplete="off" />
          <x-input-error for="hearing_date" />
        </div>

        <div class="col-md-6">
          <x-label class="form-label" for="roll_number" value="رقم الرول" />
          <x-input id="roll_number" name="roll_number"
            class="{{ $errors->has('roll_number') ? 'is-invalid' : '' }}"
            value="{{ old('roll_number', $caseHearing?->roll_number) }}" />
          <x-input-error for="roll_number" />
        </div>

        <div class="col-md-6">
          <x-label class="form-label" for="next_hearing_at" value="الجلسة القادمة" />
          <x-input id="next_hearing_at" name="next_hearing_at" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('next_hearing_at') ? 'is-invalid' : '' }}"
            value="{{ old('next_hearing_at', $caseHearing?->next_hearing_at?->format('Y-m-d')) }}" autocomplete="off" />
          <x-input-error for="next_hearing_at" />
        </div>

        <div class="col-12">
          <x-label class="form-label" for="court_decision" value="قرار المحكمة" />
          <textarea id="court_decision" name="court_decision" rows="3"
            class="form-control {{ $errors->has('court_decision') ? 'is-invalid' : '' }}">{{ old('court_decision', $caseHearing?->court_decision) }}</textarea>
          <x-input-error for="court_decision" class="d-block" />
        </div>

        <div class="col-12">
          <x-label class="form-label" for="notes" value="ملاحظات" />
          <textarea id="notes" name="notes" rows="3"
            class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}">{{ old('notes', $caseHearing?->notes) }}</textarea>
          <x-input-error for="notes" class="d-block" />
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('hearings.index', $selectedCaseId ? ['case' => $selectedCaseId] : []) }}" class="btn btn-secondary">رجوع</a>
          <x-button>{{ $submitLabel }}</x-button>
        </div>
      </div>
    </form>
  </div>
</div>
