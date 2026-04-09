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
  $inputId = $name . '-input';
  $hintId = $name . '-hint';
  $previewTriggerId = $name . '-preview-trigger';
  $modalId = $name . '-preview-modal';
  $modalStageId = $name . '-preview-stage';
  $modalTitleId = $name . '-preview-title';
  $resolvedCurrentUrl = $previewUrl ?: $currentUrl;
  $resolvedCurrentName = $currentName ?: ($resolvedCurrentUrl ? basename(parse_url($resolvedCurrentUrl, PHP_URL_PATH)) : null);
  $resolvedCurrentExtension = $resolvedCurrentName
      ? strtolower(pathinfo($resolvedCurrentName, PATHINFO_EXTENSION))
      : null;
  $resolvedHint = $hint ?? "مسموح PDF / DOC / DOCX بحد أقصى {$maxSize} ميجا.";
  $defaultPreviewTitle = 'معاينة ' . $label;
  $hasCurrentFile = filled($resolvedCurrentName) || filled($resolvedCurrentUrl);
  $shouldRenderPreview = (bool) $previewEnabled;
@endphp

<div class="{{ $wrapperClass }}">
  <x-label class="form-label" for="{{ $inputId }}" :value="$label" />
  <input id="{{ $inputId }}" type="file" name="{{ $name }}" class="d-none" accept="{{ $accept }}"
    aria-describedby="{{ $hintId }}">

  <div class="input-group legal-contract-file-group"
    data-contract-file-stage
    data-file-input="{{ $inputId }}"
    data-file-name-target="{{ $name }}-file-name"
    data-empty-name="لم يتم اختيار ملف بعد."
    @if ($shouldRenderPreview) data-file-preview-trigger="{{ $previewTriggerId }}" @endif
    @if ($shouldRenderPreview) data-file-modal="{{ $modalId }}" @endif
    @if ($shouldRenderPreview) data-file-modal-stage="{{ $modalStageId }}" @endif
    @if ($shouldRenderPreview) data-file-modal-title="{{ $modalTitleId }}" @endif
    @if ($shouldRenderPreview) data-file-default-title="{{ $defaultPreviewTitle }}" @endif
    @if ($resolvedCurrentUrl) data-preview-url="{{ $resolvedCurrentUrl }}" @endif
    @if ($resolvedCurrentName) data-current-name="{{ $resolvedCurrentName }}" @endif
    @if ($resolvedCurrentExtension) data-current-extension="{{ $resolvedCurrentExtension }}" @endif>
    <span id="{{ $name }}-file-name"
      class="form-control legal-contract-file-name {{ $errors->has($name) ? 'is-invalid' : '' }}"
      title="{{ $resolvedCurrentName ?: 'لم يتم اختيار ملف بعد.' }}">
      {{ $resolvedCurrentName ?: 'لم يتم اختيار ملف بعد.' }}
    </span>

    @if ($shouldRenderPreview)
      <button id="{{ $previewTriggerId }}" type="button"
        class="btn btn-outline-secondary {{ $hasCurrentFile ? '' : 'd-none' }}">
        <i class="icon-base ti tabler-eye icon-16px"></i>
        معاينة
      </button>
    @endif

    <button type="button" class="btn btn-primary" data-contract-file-trigger="{{ $inputId }}">
      <i class="icon-base ti tabler-upload icon-16px"></i>
      {{ $hasCurrentFile ? 'استبدال' : 'اختيار' }}
    </button>
  </div>

  <small id="{{ $hintId }}" class="text-muted d-block mt-1">{{ $resolvedHint }}</small>
  <x-input-error for="{{ $name }}" class="d-block mt-2" />

  @if ($shouldRenderPreview)
    <div class="modal fade legal-file-preview-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-truncate" id="{{ $modalTitleId }}">{{ $defaultPreviewTitle }}</h5>
            <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div id="{{ $modalStageId }}" class="legal-file-preview-stage">
              <div class="legal-pdf-placeholder" data-pdf-empty>
                <i class="icon-base ti tabler-file-upload text-primary mb-2"></i>
                <h6 class="mb-1">لم يتم اختيار ملف بعد</h6>
                <p class="mb-0 text-muted text-center">اختر ملفًا من النموذج لعرضه هنا</p>
              </div>

              <div class="legal-pdf-placeholder d-none" data-pdf-unsupported>
                <i class="icon-base ti tabler-file-alert text-warning mb-2"></i>
                <h6 class="mb-1">لا توجد معاينة مباشرة لهذا الملف</h6>
                <p class="mb-0 text-muted text-center">المعاينة داخل المودال متاحة لملفات PDF فقط</p>
              </div>

              <iframe class="legal-pdf-frame d-none" data-pdf-frame title="{{ $defaultPreviewTitle }}"></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
