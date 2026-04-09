@php
  $contract = $contract ?? null;
  $projectNames = $projectNames ?? collect();
  $supplierNames = $supplierNames ?? collect();
  $signOptions = collect($signOptions ?? []);
  $selectedProjectName = old('proje_name', $contract?->proje_name);
  $selectedSupplierName = old('suppli_name', $contract?->suppli_name);
  $currentContractFileName = $contract?->Pdf_image ? basename($contract->Pdf_image) : null;
  $currentContractFileExtension = $currentContractFileName
      ? strtolower(pathinfo($currentContractFileName, PATHINFO_EXTENSION))
      : null;

  $rawSign = old('sign', $contract?->sign ?? []);
  $selectedSigns = collect(is_array($rawSign) ? $rawSign : json_decode($rawSign, true) ?? [])
      ->filter(fn($value) => filled($value))
      ->values();

  $availableSignOptions = $signOptions->merge($selectedSigns)->unique()->values();

  $displayContractNumber = old('generated_contract_number', $generatedContractNumber ?? $contract?->contr_number);
  $isUpdate = isset($method) && strtoupper($method) === 'PUT';
@endphp

<div class="card">
  <div class="card-header">
    <h4 class="card-title mb-1">{{ $contract ? 'تعديل بيانات العقد' : 'إضافة عقد جديد' }}</h4>
    <p class="card-subtitle mb-0">سجل بيانات العقد وارفع النسخة الإلكترونية بصيغة PDF أو Word.</p>
  </div>

  <div class="card-body">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data"
      @if ($isUpdate) data-swal-confirm="true"
        data-swal-title="تأكيد التعديل"
        data-swal-text="سيتم حفظ التعديلات على بيانات العقد الحالي."
        data-swal-icon="question"
        data-swal-confirm-button="نعم، حفظ التعديلات"
        data-swal-cancel-button="إلغاء" @endif>
      @csrf
      @isset($method)
        @method($method)
      @endisset

      @unless ($isUpdate)
        <input type="hidden" name="generated_contract_number" value="{{ $displayContractNumber }}">
      @endunless

      <div class="row g-4">
        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="contr_number" value="رقم العقد" />
          <x-input id="contr_number" value="{{ $displayContractNumber }}" disabled readonly />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="proje_name" value="اسم المشروع" />
          <select id="proje_name" name="proje_name"
            class="form-select legal-project-combobox {{ $errors->has('proje_name') ? 'is-invalid' : '' }}"
            data-placeholder="اختر أو اكتب اسم المشروع">
            <option value=""></option>
            @if ($selectedProjectName)
              <option value="{{ $selectedProjectName }}" selected>{{ $selectedProjectName }}</option>
            @endif
            @foreach ($projectNames as $projectName)
              @if ($projectName !== $selectedProjectName)
                <option value="{{ $projectName }}">{{ $projectName }}</option>
              @endif
            @endforeach
          </select>
          <x-input-error for="proje_name" class="d-block" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="suppli_name" value="اسم المقاول" />
          <x-input id="suppli_name" name="suppli_name" class="{{ $errors->has('suppli_name') ? 'is-invalid' : '' }}"
            value="{{ $selectedSupplierName }}" list="suppli_name_suggestions" autocomplete="off" />
          <datalist id="suppli_name_suggestions">
            @if ($selectedSupplierName && !$supplierNames->contains($selectedSupplierName))
              <option value="{{ $selectedSupplierName }}"></option>
            @endif
            @foreach ($supplierNames as $supplierName)
              <option value="{{ $supplierName }}"></option>
            @endforeach
          </datalist>
          <x-input-error for="suppli_name" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="sign" value="جهات التوقيع" />
          <select id="sign" name="sign[]"
            class="form-select legal-multi-select {{ $errors->has('sign') ? 'is-invalid' : '' }}" multiple
            data-placeholder="اختر جهة توقيع أو أكثر">
            @foreach ($availableSignOptions as $signOption)
              <option value="{{ $signOption }}" @selected($selectedSigns->contains($signOption))>{{ $signOption }}</option>
            @endforeach
          </select>
          <x-input-error for="sign" class="d-block" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="Esnad_date" value="تاريخ الإسناد" />
          <x-input id="Esnad_date" name="Esnad_date" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('Esnad_date') ? 'is-invalid' : '' }}"
            value="{{ old('Esnad_date', $contract?->Esnad_date?->format('Y-m-d')) }}" autocomplete="off" />
          <x-input-error for="Esnad_date" />
        </div>

        <div class="col-md-4 form-control-validation">
          <x-label class="form-label" for="Contar_date" value="تاريخ العقد" />
          <x-input id="Contar_date" name="Contar_date" placeholder="YYYY-MM-DD"
            class="flatpickr-date {{ $errors->has('Contar_date') ? 'is-invalid' : '' }}"
            value="{{ old('Contar_date', $contract?->Contar_date?->format('Y-m-d')) }}" autocomplete="off" />
          <x-input-error for="Contar_date" />
        </div>

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="proje_data" value="بيانات المشروع" />
          <textarea id="proje_data" name="proje_data" class="form-control {{ $errors->has('proje_data') ? 'is-invalid' : '' }}"
            rows="1">{{ old('proje_data', $contract?->proje_data) }}</textarea>
          <x-input-error for="proje_data" class="d-block" />
        </div>

        <div class="col-md-6 form-control-validation">
          <x-label class="form-label" for="Pdf_image-input" value="ملف العقد" />
          <input id="Pdf_image-input" type="file" name="Pdf_image" class="d-none" accept=".pdf,.doc,.docx">

          <div class="input-group legal-contract-file-group" data-contract-file-stage data-file-input="Pdf_image-input"
            data-file-name-target="contract-file-name" data-file-preview-trigger="contract-file-preview-trigger"
            data-file-modal="contract-file-preview-modal" data-file-modal-stage="contract-file-preview-stage"
            data-file-modal-title="contract-file-preview-title" data-file-default-title="معاينة ملف العقد"
            data-empty-name="لم يتم اختيار ملف بعد."
            @if ($contract?->pdf_image_url) data-preview-url="{{ $contract->pdf_image_url }}" @endif
            @if ($currentContractFileName) data-current-name="{{ $currentContractFileName }}" @endif
            @if ($currentContractFileExtension) data-current-extension="{{ $currentContractFileExtension }}" @endif>
            <span id="contract-file-name"
              class="form-control legal-contract-file-name {{ $errors->has('Pdf_image') ? 'is-invalid' : '' }}"
              title="{{ $currentContractFileName ?: 'لم يتم اختيار ملف بعد.' }}">
              {{ $currentContractFileName ?: 'لم يتم اختيار ملف بعد.' }}
            </span>

            <button id="contract-file-preview-trigger" type="button"
              class="btn btn-outline-secondary {{ $currentContractFileName ? '' : 'd-none' }}">
              <i class="icon-base ti tabler-eye icon-16px"></i>
              معاينة
            </button>

            <button type="button" class="btn btn-primary" data-contract-file-trigger="Pdf_image-input">
              <i class="icon-base ti tabler-upload icon-16px"></i>
              {{ $currentContractFileName ? 'استبدال' : 'اختيار' }}
            </button>
          </div>

          <small class="text-muted d-block mt-1">الملفات المدعومة: PDF / DOC / DOCX</small>
          <x-input-error for="Pdf_image" class="d-block mt-2" />
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('contracts.index') }}" class="btn btn-secondary">رجوع</a>
          <x-button>{{ $submitLabel }}</x-button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade legal-file-preview-modal" id="contract-file-preview-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-truncate" id="contract-file-preview-title">معاينة ملف العقد</h5>
        <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="contract-file-preview-stage" class="legal-file-preview-stage">
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

          <iframe class="legal-pdf-frame d-none" data-pdf-frame title="معاينة ملف العقد"></iframe>
        </div>
      </div>
    </div>
  </div>
</div>
