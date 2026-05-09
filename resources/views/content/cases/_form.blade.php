@php
  $legalCase = $case ?? null;
  $selectedType = old('case_type', $legalCase?->case_type ?? \App\Models\LegalCase::TYPE_CIVIL);
  $selectedPrimaryCourt = old('primary_court_name', $legalCase?->primary_court_name);
  $selectedAppealCourt = old('appeal_court_name', $legalCase?->appeal_court_name ?? 'استئناف الإسكندرية');
  $selectedAppealPeriod = old('appeal_session_period', $legalCase?->appeal_session_period);
  $hasAppeal = old('has_appeal', $legalCase?->has_appeal ? '1' : '0');
  $appealEnabled = (string) $hasAppeal === '1';
  $judgmentIssuedAt = old('judgment_issued_at', $legalCase?->judgment_issued_at?->format('Y-m-d'));
  $appealDeadline = $judgmentIssuedAt ? \Illuminate\Support\Carbon::parse($judgmentIssuedAt)->addDays(40) : null;
  $isUpdate = isset($method) && strtoupper($method) === 'PUT';
@endphp

<div class="card legal-case-wizard-card">
  <div class="card-header border-bottom">
    <h4 class="card-title mb-1">{{ $legalCase ? 'تعديل بيانات القضية' : 'إضافة قضية جديدة' }}</h4>
    <p class="card-subtitle mb-0">ابدأ باختيار نوع القضية، وسيقوم النموذج بعرض الحقول المناسبة فقط لتقليل الإدخال وتسريع
      التسجيل.</p>
  </div>

  <div class="card-body">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" novalidate data-legal-case-form
      @if ($isUpdate) data-swal-confirm="true"
        data-swal-title="تأكيد تعديل القضية"
        data-swal-text="سيتم حفظ التعديلات الجديدة على بيانات القضية والمرفقات."
        data-swal-icon="question"
        data-swal-confirm-button="نعم، احفظ التعديلات"
        data-swal-cancel-button="إلغاء" @endif>
      @csrf
      @isset($method)
        @method($method)
      @endisset

      <div class="bs-stepper wizard-numbered legal-case-wizard" data-legal-case-wizard>
        <div class="bs-stepper-header border-bottom-0">
          <div class="step" data-target="#case-type-step">
            <button type="button" class="step-trigger">
              <span class="bs-stepper-circle">1</span>
              <span class="bs-stepper-label">
                <span class="bs-stepper-title">نوع القضية</span>
                <span class="bs-stepper-subtitle">مدنية أم جنائية</span>
              </span>
            </button>
          </div>
          <div class="line">
            <i class="icon-base ti tabler-chevron-right"></i>
          </div>
          <div class="step" data-target="#case-details-step">
            <button type="button" class="step-trigger">
              <span class="bs-stepper-circle">2</span>
              <span class="bs-stepper-label">
                <span class="bs-stepper-title">البيانات الأساسية</span>
                <span class="bs-stepper-subtitle">الخصوم وأرقام القضية</span>
              </span>
            </button>
          </div>
          <div class="line">
            <i class="icon-base ti tabler-chevron-right"></i>
          </div>
          <div class="step" data-target="#case-appeal-step">
            <button type="button" class="step-trigger">
              <span class="bs-stepper-circle">3</span>
              <span class="bs-stepper-label">
                <span class="bs-stepper-title">الاستئناف والمتابعة</span>
                <span class="bs-stepper-subtitle">حساب مهلة الأربعين يومًا</span>
              </span>
            </button>
          </div>
          <div class="line">
            <i class="icon-base ti tabler-chevron-right"></i>
          </div>
          <div class="step" data-target="#case-documents-step">
            <button type="button" class="step-trigger">
              <span class="bs-stepper-circle">4</span>
              <span class="bs-stepper-label">
                <span class="bs-stepper-title">المرفقات والمراجعة</span>
                <span class="bs-stepper-subtitle">ملخص سريع قبل الحفظ</span>
              </span>
            </button>
          </div>
        </div>

        <div class="bs-stepper-content pt-4">
          <div id="case-type-step" class="content">
            <div class="content-header mb-4">
              <h5 class="mb-1">ما نوع القضية؟</h5>
              <p class="text-muted mb-0">اختيار النوع أولًا يجعل باقي النموذج ذكيًا ويعرض لك البيانات المطلوبة فقط.</p>
            </div>

            <div class="row g-4">
              @foreach ($caseTypeOptions as $typeValue => $typeLabel)
                <div class="col-md-6">
                  <label class="legal-case-type-option {{ $selectedType === $typeValue ? 'is-active' : '' }}"
                    data-case-type-card="{{ $typeValue }}">
                    <input type="radio" name="case_type" value="{{ $typeValue }}" class="form-check-input"
                      @checked($selectedType === $typeValue) data-case-type-input>

                    <span class="legal-case-type-option__icon">
                      <i
                        class="icon-base ti {{ $typeValue === \App\Models\LegalCase::TYPE_CIVIL ? 'tabler-scale' : 'tabler-shield-half-filled' }}"></i>
                    </span>

                    <span class="legal-case-type-option__content">
                      <span class="legal-case-type-option__title">{{ $typeLabel }}</span>
                      <span class="legal-case-type-option__subtitle">
                        {{ $typeValue === \App\Models\LegalCase::TYPE_CIVIL ? 'دعوى مدعي ومدعى عليه مع بيانات المحكمة والاستئناف.' : 'جنحة أو قضية جنائية مع بيانات المحضر والمتهم.' }}
                      </span>
                    </span>
                  </label>
                </div>
              @endforeach
            </div>

            <div class="alert alert-primary mt-4 mb-0" role="alert">
              <div class="d-flex align-items-start gap-2">
                <i class="icon-base ti tabler-bulb icon-18px mt-1"></i>
                <div>
                  <strong>اقتراح عملي:</strong>
                  بعد حفظ القضية يمكنك الانتقال مباشرة إلى شاشة الجلسات لتسجيل كل القرارات القادمة وربطها بنفس رقم
                  الدعوى.
                </div>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-between mt-4">
              <a href="{{ route('cases.index') }}" class="btn btn-label-secondary">رجوع</a>
              <button type="button" class="btn btn-primary btn-next">
                التالي
                <i class="icon-base ti tabler-arrow-right ms-1"></i>
              </button>
            </div>
          </div>

          <div id="case-details-step" class="content">
            <div class="content-header mb-4">
              <h5 class="mb-1">سجل بيانات القضية</h5>
              <p class="text-muted mb-0">الحقول ستتغير تلقائيًا حسب نوع القضية المختار.</p>
            </div>

            <div class="row g-4">
              <div class="col-12">
                <div class="alert alert-label-secondary mb-0">
                  <div class="small mb-1">رقم القضية الحالي في الملخص</div>
                  <strong
                    data-case-summary-preview="case_number">{{ old('case_number', $legalCase?->case_number) ?: 'لم يتم إدخال رقم الدعوى بعد' }}</strong>
                </div>
              </div>

              <div
                class="col-12 legal-case-panel {{ $selectedType === \App\Models\LegalCase::TYPE_CIVIL ? '' : 'd-none' }}"
                data-case-panel="civil">
                <div class="row g-4">
                  <div class="col-md-6">
                    <x-label class="form-label" for="primary_party_name" value="اسم المدعي" />
                    <x-input id="primary_party_name" name="primary_party_name"
                      class="{{ $errors->has('primary_party_name') ? 'is-invalid' : '' }}" data-case-field="civil"
                      value="{{ old('primary_party_name', $legalCase?->primary_party_name) }}" />
                    <x-input-error for="primary_party_name" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="opponent_party_name" value="اسم المدعى عليه" />
                    <x-input id="opponent_party_name" name="opponent_party_name"
                      class="{{ $errors->has('opponent_party_name') ? 'is-invalid' : '' }}" data-case-field="civil"
                      value="{{ old('opponent_party_name', $legalCase?->opponent_party_name) }}" />
                    <x-input-error for="opponent_party_name" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="case_number" value="رقم الدعوى" />
                    <x-input id="case_number" name="case_number" placeholder="1112 لسنة 2024"
                      class="{{ $errors->has('case_number') ? 'is-invalid' : '' }}" data-case-field="civil"
                      value="{{ old('case_number', $legalCase?->case_number) }}" data-summary-source="case_number" />
                    <x-input-error for="case_number" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="case_filed_at" value="تاريخ رفع الدعوى" />
                    <x-input id="case_filed_at" name="case_filed_at" placeholder="YYYY-MM-DD"
                      class="flatpickr-date {{ $errors->has('case_filed_at') ? 'is-invalid' : '' }}"
                      data-case-field="civil"
                      value="{{ old('case_filed_at', $legalCase?->case_filed_at?->format('Y-m-d')) }}"
                      autocomplete="off" />
                    <x-input-error for="case_filed_at" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="first_session_at" value="تاريخ أول جلسة" />
                    <x-input id="first_session_at" name="first_session_at" placeholder="YYYY-MM-DD"
                      class="flatpickr-date {{ $errors->has('first_session_at') ? 'is-invalid' : '' }}"
                      data-case-field="civil"
                      value="{{ old('first_session_at', $legalCase?->first_session_at?->format('Y-m-d')) }}"
                      autocomplete="off" />
                    <x-input-error for="first_session_at" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="primary_court_name" value="منظورة أمام محكمة" />
                    <select id="primary_court_name" name="primary_court_name" data-no-results="لا توجد محكمة مطابقة"
                      class="form-select legal-tag-select {{ $errors->has('primary_court_name') ? 'is-invalid' : '' }}"
                      data-case-field="civil" data-tags="true" data-placeholder="اختر المحكمة أو أضف اسمًا جديدًا">
                      <option value=""></option>
                      @if ($selectedPrimaryCourt)
                        <option value="{{ $selectedPrimaryCourt }}" selected>{{ $selectedPrimaryCourt }}</option>
                      @endif
                      @foreach ($primaryCourtOptions as $courtName)
                        @if ($courtName !== $selectedPrimaryCourt)
                          <option value="{{ $courtName }}">{{ $courtName }}</option>
                        @endif
                      @endforeach
                    </select>
                    <x-input-error for="primary_court_name" class="d-block" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="circuit_number" value="رقم الدائرة" />
                    <x-input id="circuit_number" name="circuit_number"
                      class="{{ $errors->has('circuit_number') ? 'is-invalid' : '' }}" data-case-field="civil"
                      value="{{ old('circuit_number', $legalCase?->circuit_number) }}" />
                    <x-input-error for="circuit_number" />
                  </div>

                  <div class="col-12">
                    <x-label class="form-label" for="subject" value="موضوع الدعوى" />
                    <textarea id="subject" name="subject" rows="3"
                      class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}" data-case-field="civil">{{ old('subject', $legalCase?->subject) }}</textarea>
                    <x-input-error for="subject" class="d-block" />
                  </div>
                </div>
              </div>

              <div
                class="col-12 legal-case-panel {{ $selectedType === \App\Models\LegalCase::TYPE_CRIMINAL ? '' : 'd-none' }}"
                data-case-panel="criminal">
                <div class="row g-4">
                  <div class="col-md-6">
                    <x-label class="form-label" for="criminal_primary_party_name" value="اسم الشاكي" />
                    <x-input id="criminal_primary_party_name" name="primary_party_name"
                      class="{{ $errors->has('primary_party_name') ? 'is-invalid' : '' }}" data-case-field="criminal"
                      value="{{ old('primary_party_name', $legalCase?->primary_party_name) }}" />
                    <x-input-error for="primary_party_name" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="criminal_opponent_party_name" value="اسم المتهم" />
                    <x-input id="criminal_opponent_party_name" name="opponent_party_name"
                      class="{{ $errors->has('opponent_party_name') ? 'is-invalid' : '' }}"
                      data-case-field="criminal"
                      value="{{ old('opponent_party_name', $legalCase?->opponent_party_name) }}" />
                    <x-input-error for="opponent_party_name" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="criminal_case_number" value="رقم الجنحة" />
                    <x-input id="criminal_case_number" name="case_number" placeholder="رقم الجنحة"
                      class="{{ $errors->has('case_number') ? 'is-invalid' : '' }}" data-case-field="criminal"
                      value="{{ old('case_number', $legalCase?->case_number) }}" data-summary-source="case_number" />
                    <x-input-error for="case_number" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="report_date" value="تاريخ تحرير المحضر" />
                    <x-input id="report_date" name="report_date" placeholder="YYYY-MM-DD"
                      class="flatpickr-date {{ $errors->has('report_date') ? 'is-invalid' : '' }}"
                      data-case-field="criminal"
                      value="{{ old('report_date', $legalCase?->report_date?->format('Y-m-d')) }}"
                      autocomplete="off" />
                    <x-input-error for="report_date" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="criminal_first_session_at" value="تاريخ أول جلسة" />
                    <x-input id="criminal_first_session_at" name="first_session_at" placeholder="YYYY-MM-DD"
                      class="flatpickr-date {{ $errors->has('first_session_at') ? 'is-invalid' : '' }}"
                      data-case-field="criminal"
                      value="{{ old('first_session_at', $legalCase?->first_session_at?->format('Y-m-d')) }}"
                      autocomplete="off" />
                    <x-input-error for="first_session_at" />
                  </div>

                  <div class="col-md-6">
                    <x-label class="form-label" for="detention_order_number" value="رقم حصر الحبس إن وجد" />
                    <x-input id="detention_order_number" name="detention_order_number"
                      class="{{ $errors->has('detention_order_number') ? 'is-invalid' : '' }}"
                      data-case-field="criminal"
                      value="{{ old('detention_order_number', $legalCase?->detention_order_number) }}" />
                    <x-input-error for="detention_order_number" />
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-between mt-4">
              <button type="button" class="btn btn-label-secondary btn-prev">
                <i class="icon-base ti tabler-arrow-left me-1"></i>
                السابق
              </button>
              <button type="button" class="btn btn-primary btn-next">
                التالي
                <i class="icon-base ti tabler-arrow-right ms-1"></i>
              </button>
            </div>
          </div>

          <div id="case-appeal-step" class="content">
            <div class="content-header mb-4">
              <h5 class="mb-1">متابعة الحكم والاستئناف</h5>
              <p class="text-muted mb-0">الخطوة التالية مفيدة خصوصًا في القضايا المدنية لحساب مهلة الأربعين يومًا
                وتسجيل بيانات الاستئناف.</p>
            </div>

            <div class="row g-4">
              <div
                class="col-12 legal-case-panel {{ $selectedType === \App\Models\LegalCase::TYPE_CIVIL ? '' : 'd-none' }}"
                data-case-panel="civil">
                <div class="row g-4">
                  <div class="col-md-6">
                    <x-label class="form-label" for="judgment_issued_at" value="تاريخ صدور الحكم إن وجد" />
                    <x-input id="judgment_issued_at" name="judgment_issued_at" placeholder="YYYY-MM-DD"
                      class="flatpickr-date {{ $errors->has('judgment_issued_at') ? 'is-invalid' : '' }}"
                      data-case-field="civil" value="{{ $judgmentIssuedAt }}" autocomplete="off"
                      data-judgment-date-input />
                    <x-input-error for="judgment_issued_at" />
                  </div>

                  <div class="col-md-6">
                    <div class="legal-case-insight h-100" data-appeal-deadline-card>
                      <div class="small text-muted mb-2">مهلة الاستئناف المتوقعة</div>
                      <h5 class="mb-1" data-appeal-deadline-date>
                        {{ $appealDeadline?->format('Y-m-d') ?: 'لم يتم تحديد تاريخ الحكم' }}</h5>
                      <p class="mb-0 text-muted" data-appeal-deadline-note>
                        @if ($appealDeadline)
                          تنتهي المهلة بعد 40 يومًا من تاريخ الحكم.
                        @else
                          أدخل تاريخ الحكم ليتم حساب المهلة تلقائيًا.
                        @endif
                      </p>
                    </div>
                  </div>

                  <div class="col-12">
                    <label class="form-check legal-inline-check">
                      <input type="hidden" name="has_appeal" value="0">
                      <input type="checkbox" class="form-check-input" name="has_appeal" value="1"
                        @checked($appealEnabled) data-has-appeal-toggle>
                      <span class="form-check-label">تم رفع استئناف على الحكم</span>
                    </label>
                    <x-input-error for="has_appeal" class="d-block" />
                  </div>

                  <div class="col-12 legal-appeal-panel {{ $appealEnabled ? '' : 'd-none' }}" data-appeal-panel>
                    <div class="row g-4">
                      <div class="col-md-6">
                        <x-label class="form-label" for="appeal_appellant_name" value="اسم المستأنف" />
                        <x-input id="appeal_appellant_name" name="appeal_appellant_name"
                          class="{{ $errors->has('appeal_appellant_name') ? 'is-invalid' : '' }}" data-appeal-field
                          value="{{ old('appeal_appellant_name', $legalCase?->appeal_appellant_name) }}" />
                        <x-input-error for="appeal_appellant_name" />
                      </div>

                      <div class="col-md-6">
                        <x-label class="form-label" for="appeal_respondent_name" value="اسم المستأنف ضده" />
                        <x-input id="appeal_respondent_name" name="appeal_respondent_name"
                          class="{{ $errors->has('appeal_respondent_name') ? 'is-invalid' : '' }}" data-appeal-field
                          value="{{ old('appeal_respondent_name', $legalCase?->appeal_respondent_name) }}" />
                        <x-input-error for="appeal_respondent_name" />
                      </div>

                      <div class="col-md-6">
                        <x-label class="form-label" for="appeal_number" value="رقم الاستئناف" />
                        <x-input id="appeal_number" name="appeal_number" placeholder="125 لسنة 82 ق"
                          class="{{ $errors->has('appeal_number') ? 'is-invalid' : '' }}" data-appeal-field
                          value="{{ old('appeal_number', $legalCase?->appeal_number) }}" />
                        <x-input-error for="appeal_number" />
                      </div>

                      <div class="col-md-6">
                        <x-label class="form-label" for="appeal_court_name" value="محكمة الاستئناف" />
                        <select id="appeal_court_name" name="appeal_court_name"
                          class="form-select legal-tag-select {{ $errors->has('appeal_court_name') ? 'is-invalid' : '' }}"
                          data-tags="true" data-placeholder="اختر المحكمة أو أضف اسمًا جديدًا" data-appeal-field>
                          <option value=""></option>
                          @if ($selectedAppealCourt)
                            <option value="{{ $selectedAppealCourt }}" selected>{{ $selectedAppealCourt }}</option>
                          @endif
                          @foreach ($appealCourtOptions as $courtName)
                            @if ($courtName !== $selectedAppealCourt)
                              <option value="{{ $courtName }}">{{ $courtName }}</option>
                            @endif
                          @endforeach
                        </select>
                        <x-input-error for="appeal_court_name" class="d-block" />
                      </div>

                      <div class="col-md-4">
                        <x-label class="form-label" for="appeal_circuit_number" value="رقم الدائرة" />
                        <x-input id="appeal_circuit_number" name="appeal_circuit_number"
                          class="{{ $errors->has('appeal_circuit_number') ? 'is-invalid' : '' }}" data-appeal-field
                          value="{{ old('appeal_circuit_number', $legalCase?->appeal_circuit_number) }}" />
                        <x-input-error for="appeal_circuit_number" />
                      </div>

                      <div class="col-md-4">
                        <x-label class="form-label" for="appeal_session_period" value="الفترة" />
                        <select id="appeal_session_period" name="appeal_session_period"
                          class="form-select {{ $errors->has('appeal_session_period') ? 'is-invalid' : '' }}"
                          data-appeal-field>
                          <option value="">اختر الفترة</option>
                          @foreach ($appealSessionPeriods as $periodValue => $periodLabel)
                            <option value="{{ $periodValue }}" @selected($selectedAppealPeriod === $periodValue)>{{ $periodLabel }}
                            </option>
                          @endforeach
                        </select>
                        <x-input-error for="appeal_session_period" class="d-block" />
                      </div>

                      <div class="col-md-4">
                        <x-label class="form-label" for="appeal_first_session_at" value="أول جلسة لنظر الاستئناف" />
                        <x-input id="appeal_first_session_at" name="appeal_first_session_at" placeholder="YYYY-MM-DD"
                          class="flatpickr-date {{ $errors->has('appeal_first_session_at') ? 'is-invalid' : '' }}"
                          data-appeal-field
                          value="{{ old('appeal_first_session_at', $legalCase?->appeal_first_session_at?->format('Y-m-d')) }}"
                          autocomplete="off" />
                        <x-input-error for="appeal_first_session_at" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="col-12 legal-case-panel {{ $selectedType === \App\Models\LegalCase::TYPE_CRIMINAL ? '' : 'd-none' }}"
                data-case-panel="criminal">
                <div class="alert alert-warning mb-0">
                  <div class="d-flex align-items-start gap-2">
                    <i class="icon-base ti tabler-info-circle icon-18px mt-1"></i>
                    <div>
                      لا توجد بيانات استئناف مطلوبة في هذا المسار حاليًا. يمكنك الانتقال للخطوة الأخيرة لإرفاق المستندات
                      ومراجعة البيانات.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-between mt-4">
              <button type="button" class="btn btn-label-secondary btn-prev">
                <i class="icon-base ti tabler-arrow-left me-1"></i>
                السابق
              </button>
              <button type="button" class="btn btn-primary btn-next">
                التالي
                <i class="icon-base ti tabler-arrow-right ms-1"></i>
              </button>
            </div>
          </div>

          <div id="case-documents-step" class="content">
            <div class="content-header mb-4">
              <h5 class="mb-1">المرفقات والمراجعة النهائية</h5>
              <p class="text-muted mb-0">أرفق المستندات المتاحة ثم راجع الملخص قبل الحفظ.</p>
            </div>

            <div class="row g-4">
              @if ($legalCase?->attachments?->isNotEmpty())
                <div class="col-12">
                  <div class="card border shadow-none">
                    <div class="card-body">
                      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <h6 class="mb-0">المرفقات الحالية</h6>
                        <span class="badge bg-label-secondary">{{ $legalCase->attachments->count() }} ملف</span>
                      </div>

                      <div class="row g-3">
                        @foreach ($legalCase->attachments as $attachment)
                          <div class="col-md-6">
                            <label class="legal-attachment-row">
                              <span class="legal-attachment-row__icon">
                                <i
                                  class="icon-base ti {{ in_array($attachment->file_extension, ['pdf']) ? 'tabler-file-type-pdf text-danger' : 'tabler-file-text text-primary' }}"></i>
                              </span>

                              <span class="legal-attachment-row__content">
                                <a href="{{ $attachment->file_url }}" target="_blank"
                                  class="fw-semibold text-primary">
                                  {{ $attachment->original_name }}
                                </a>
                                <span class="text-muted small">يمكنك تعليم الملف للحذف وسيتم إزالته عند الحفظ.</span>
                              </span>

                              <span class="form-check">
                                <input type="checkbox" class="form-check-input" name="remove_attachment_ids[]"
                                  value="{{ $attachment->id }}">
                              </span>
                            </label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              @endif

              <div class="col-12">
                <x-label class="form-label" for="attachments" value="إرفاق مستندات" />
                <input id="attachments" name="attachments[]" type="file" multiple
                  accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                  class="form-control {{ $errors->has('attachments') || $errors->has('attachments.*') ? 'is-invalid' : '' }}">
                <small class="text-muted d-block mt-2">يمكنك اختيار أكثر من ملف مرة واحدة. الأنواع المدعومة: PDF / DOC
                  / DOCX / JPG / PNG</small>
                <x-input-error for="attachments" class="d-block mt-2" />
                <x-input-error for="attachments.*" class="d-block mt-2" />
              </div>

              <div class="col-12">
                <div class="row g-3">
                  <div class="col-md-4">
                    <div class="legal-case-summary-card">
                      <span class="legal-case-summary-card__label">نوع القضية</span>
                      <strong data-case-summary="case_type">{{ $caseTypeOptions[$selectedType] ?? '—' }}</strong>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="legal-case-summary-card">
                      <span class="legal-case-summary-card__label">رقم الدعوى</span>
                      <strong
                        data-case-summary="case_number">{{ old('case_number', $legalCase?->case_number) ?: '—' }}</strong>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="legal-case-summary-card">
                      <span class="legal-case-summary-card__label">الخصوم</span>
                      <strong
                        data-case-summary="parties">{{ old('primary_party_name', $legalCase?->primary_party_name) && old('opponent_party_name', $legalCase?->opponent_party_name) ? old('primary_party_name', $legalCase?->primary_party_name) . ' / ' . old('opponent_party_name', $legalCase?->opponent_party_name) : '—' }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-between mt-4">
              <button type="button" class="btn btn-label-secondary btn-prev">
                <i class="icon-base ti tabler-arrow-left me-1"></i>
                السابق
              </button>
              <x-button>{{ $submitLabel }}</x-button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
