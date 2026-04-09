@props([
    'name',
    'label',
    'accept' => '.pdf,.doc,.docx',
    'maxSize' => 10,
    'currentUrl' => null,
    'currentName' => null,
    'hint' => null,
    'previewTarget' => null,
    'previewUrl' => null,
    'previewEnabled' => true,
    'wrapperClass' => 'col-md-6 form-control-validation',
])

@php
  $baseId = $name;
  $inputId = $baseId . '-input';
  $hintId = $name . '-hint';
  $fileNameId = $baseId . '-file-name';
  $previewTriggerId = $baseId . '-preview-trigger';
  $modalId = $baseId . '-preview-modal';
  $modalStageId = $baseId . '-preview-stage';
  $modalTitleId = $baseId . '-preview-title';
  $resolvedCurrentUrl = $previewUrl ?: $currentUrl;
  $resolvedCurrentName = $currentName ?: ($resolvedCurrentUrl ? basename(parse_url($resolvedCurrentUrl, PHP_URL_PATH)) : null);
  $resolvedCurrentExtension = $resolvedCurrentName
      ? strtolower(pathinfo($resolvedCurrentName, PATHINFO_EXTENSION))
      : null;
  $acceptLabels = collect(explode(',', $accept))
      ->map(fn($value) => strtoupper(ltrim(trim($value), '.')))
      ->filter()
      ->unique()
      ->implode(' / ');
  $resolvedHint = 'الملفات المدعومة: ' . ($acceptLabels ?: 'PDF / DOC / DOCX') . " — الحد الأقصى: {$maxSize} ميجابايت";
  $hasCurrentFile = filled($resolvedCurrentName) || filled($resolvedCurrentUrl);
@endphp

<div class="{{ $wrapperClass }}">
  <x-label class="form-label" for="{{ $inputId }}" :value="$label" />
  <input id="{{ $inputId }}" type="file" name="{{ $name }}" class="d-none" accept="{{ $accept }}"
    aria-label="رفع الملف" aria-describedby="{{ $hintId }}">

  <div class="input-group legal-contract-file-group"
    data-contract-file-stage
    data-file-input="{{ $inputId }}"
    data-file-name-target="{{ $fileNameId }}"
    data-file-preview-trigger="{{ $previewTriggerId }}"
    data-file-modal="{{ $modalId }}"
    data-file-modal-stage="{{ $modalStageId }}"
    data-file-modal-title="{{ $modalTitleId }}"
    data-file-default-title="معاينة الملف"
    data-empty-name="لم يتم اختيار ملف بعد."
    @if ($resolvedCurrentUrl) data-preview-url="{{ $resolvedCurrentUrl }}" @endif
    @if ($resolvedCurrentName) data-current-name="{{ $resolvedCurrentName }}" @endif
    @if ($resolvedCurrentExtension) data-current-extension="{{ $resolvedCurrentExtension }}" @endif>
    <span id="{{ $fileNameId }}"
      class="form-control legal-contract-file-name @error($name) is-invalid @enderror"
      title="{{ $resolvedCurrentName ?: 'لم يتم اختيار ملف بعد.' }}" aria-live="polite">
      {{ $resolvedCurrentName ?: 'لم يتم اختيار ملف بعد.' }}
    </span>

    <button id="{{ $previewTriggerId }}" type="button"
      class="btn btn-outline-secondary {{ $hasCurrentFile ? '' : 'd-none' }}" aria-label="معاينة الملف"
      aria-controls="{{ $modalId }}">
      <i class="icon-base ti tabler-eye icon-16px" aria-hidden="true"></i>
      معاينة
    </button>

    <button type="button" class="btn btn-primary" data-contract-file-trigger="{{ $inputId }}"
      aria-label="{{ $hasCurrentFile ? 'استبدال الملف' : 'اختيار ملف' }}">
      <i class="icon-base ti tabler-upload icon-16px" aria-hidden="true"></i>
      {{ $hasCurrentFile ? 'استبدال' : 'اختيار' }}
    </button>
  </div>

  <small id="{{ $hintId }}" class="text-muted d-block mt-1">{{ $resolvedHint }}</small>
  <x-input-error for="{{ $name }}" class="d-block mt-2" />
</div>

@push('modals')
  <div class="modal fade legal-file-preview-modal" id="{{ $modalId }}" tabindex="-1"
    aria-labelledby="{{ $modalTitleId }}" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-truncate" id="{{ $modalTitleId }}">معاينة الملف</h5>
          <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body">
          <div id="{{ $modalStageId }}" class="legal-file-preview-stage" aria-live="polite">
            <div class="legal-pdf-placeholder" data-pdf-empty role="status">
              <i class="icon-base ti tabler-file-upload text-primary mb-2" aria-hidden="true"></i>
              <h6 class="mb-1">لم يتم اختيار ملف بعد</h6>
              <p class="mb-0 text-muted text-center">اختر ملفًا من النموذج لعرضه هنا</p>
            </div>
            <div class="legal-pdf-placeholder d-none" data-pdf-unsupported role="status">
              <i class="icon-base ti tabler-file-alert text-warning mb-2" aria-hidden="true"></i>
              <h6 class="mb-1">لا توجد معاينة مباشرة لهذا الملف</h6>
              <p class="mb-0 text-muted text-center">المعاينة متاحة لملفات PDF فقط</p>
            </div>
            <iframe class="legal-pdf-frame d-none" data-pdf-frame title="معاينة الملف"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
@endpush
