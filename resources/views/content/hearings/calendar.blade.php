@extends('layouts/layoutMaster')

@section('title', 'تقويم الجلسات')

@section('vendor-style')
  @vite([
      'resources/assets/vendor/libs/fullcalendar/fullcalendar.scss',
      'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
      'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/app-calendar.scss', 'resources/css/legal-resources.css'])
@endsection

@section('vendor-script')
  @vite([
      'resources/assets/vendor/libs/fullcalendar/fullcalendar.js',
      'resources/assets/vendor/libs/flatpickr/flatpickr.js',
      'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/legal-hearings-calendar.js'])
@endsection

@section('content')
  <div class="legal-resource-page legal-hearings-calendar-page d-flex flex-column gap-4" dir="rtl">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">تقويم الجلسات</h4>
        <p class="mb-0 text-muted">عرض أجندة الجلسات القادمة اعتماداً على تاريخ الجلسة القادمة داخل سجل الجلسات.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('hearings.index') }}" class="btn btn-outline-secondary">
          <i class="icon-base ti tabler-list-details me-1"></i>
          سجل الجلسات
        </a>
        <button type="button" class="btn btn-primary" data-calendar-add>
          <i class="icon-base ti tabler-plus me-1"></i>
          إضافة جلسة
        </button>
      </div>
    </div>

    <x-page-alerts />
    <div class="legal-calendar-alert d-none" data-calendar-alert></div>

    <div class="card app-calendar-wrapper legal-hearings-calendar">
      <div class="row g-0">
        <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
          <div class="border-bottom p-6 my-sm-0 mb-4">
            <button class="btn btn-primary btn-toggle-sidebar w-100" type="button" data-calendar-add>
              <i class="icon-base ti tabler-plus icon-16px me-2"></i>
              <span class="align-middle">إضافة جلسة</span>
            </button>
          </div>

          <div class="px-3 pt-2">
            <div class="inline-calendar" data-inline-calendar></div>
          </div>

          <hr class="mb-6 mx-n4 mt-3" />

          <div class="px-6 pb-2">
            <div>
              <h5>تصفية الجلسات</h5>
            </div>

            <div class="form-check form-check-secondary mb-5 ms-2">
              <input class="form-check-input select-all" type="checkbox" id="selectAll" data-value="all" checked />
              <label class="form-check-label" for="selectAll">عرض الكل</label>
            </div>

            <div class="app-calendar-events-filter text-heading">
              <div class="form-check form-check-primary mb-5 ms-2">
                <input class="form-check-input input-filter" type="checkbox" id="select-civil" data-value="civil"
                  checked />
                <label class="form-check-label" for="select-civil">قضايا مدنية</label>
              </div>
              <div class="form-check form-check-warning ms-2">
                <input class="form-check-input input-filter" type="checkbox" id="select-criminal" data-value="criminal"
                  checked />
                <label class="form-check-label" for="select-criminal">قضايا جنائية</label>
              </div>
            </div>
          </div>
        </div>

        <div class="col app-calendar-content">
          <div class="card shadow-none border-0">
            <div class="card-body pb-0">
              <div id="calendar" data-hearing-calendar data-events-url="{{ route('hearing-calendar.events') }}"></div>
            </div>
          </div>

          <div class="app-overlay"></div>

          <div class="offcanvas offcanvas-end event-sidebar legal-hearing-calendar-sidebar" tabindex="-1"
            id="addEventSidebar" aria-labelledby="addEventSidebarLabel">
            <div class="offcanvas-header border-bottom">
              <h5 class="offcanvas-title" id="addEventSidebarLabel">تفاصيل الجلسة</h5>
              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
            </div>

            <div class="offcanvas-body">
              <div data-calendar-view-panel class="d-none">
                <div class="alert alert-primary d-flex align-items-start gap-3 mb-5" role="alert">
                  <i class="icon-base ti tabler-calendar-event mt-1"></i>
                  <div>
                    <div class="fw-semibold">الجلسة القادمة</div>
                    <div data-detail="next_hearing_at">-</div>
                  </div>
                </div>

                <dl class="row gy-3 mb-0">
                  <dt class="col-5 text-muted">رقم الدعوى</dt>
                  <dd class="col-7 mb-0 fw-semibold" data-detail="case_number">-</dd>

                  <dt class="col-5 text-muted">نوع القضية</dt>
                  <dd class="col-7 mb-0" data-detail="case_type_label">-</dd>

                  <dt class="col-5 text-muted">الأطراف</dt>
                  <dd class="col-7 mb-0" data-detail="parties">-</dd>

                  <dt class="col-5 text-muted">المحكمة</dt>
                  <dd class="col-7 mb-0" data-detail="court_name">-</dd>

                  <dt class="col-5 text-muted">الدائرة</dt>
                  <dd class="col-7 mb-0" data-detail="circuit_number">-</dd>

                  <dt class="col-5 text-muted">تاريخ الجلسة</dt>
                  <dd class="col-7 mb-0" data-detail="hearing_date">-</dd>

                  <dt class="col-5 text-muted">رقم الرول</dt>
                  <dd class="col-7 mb-0" data-detail="roll_number">-</dd>
                </dl>

                <div class="border-top pt-4 mt-4">
                  <div class="fw-semibold mb-2">قرار المحكمة</div>
                  <p class="legal-hearing-detail-text text-body mb-0" data-detail="court_decision">-</p>
                </div>

                <div class="border-top pt-4 mt-4">
                  <div class="fw-semibold mb-2">ملاحظات</div>
                  <p class="legal-hearing-detail-text text-body mb-0" data-detail="notes">-</p>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-6">
                  <a href="#" class="btn btn-primary" data-detail-link="edit">
                    <i class="icon-base ti tabler-pencil me-1"></i>
                    تعديل الجلسة
                  </a>
                  <a href="#" class="btn btn-label-secondary" data-detail-link="case">
                    <i class="icon-base ti tabler-scale me-1"></i>
                    فتح القضية
                  </a>
                  <a href="#" class="btn btn-label-secondary" data-detail-link="list">
                    <i class="icon-base ti tabler-list-details me-1"></i>
                    سجل القضية
                  </a>
                </div>
              </div>

              <div data-calendar-form-panel class="d-none">
                <form class="event-form pt-0" id="hearingCalendarForm" action="{{ route('hearing-calendar.store') }}"
                  method="POST" novalidate>
                  @csrf

                  <div class="mb-5 form-control-validation">
                    <label class="form-label" for="calendar_legal_case_id">رقم الدعوى</label>
                    <select id="calendar_legal_case_id" name="legal_case_id"
                      class="form-select legal-single-select" data-placeholder="اختر القضية" required>
                      <option value=""></option>
                      @foreach ($cases as $caseOption)
                        <option value="{{ $caseOption['id'] }}">
                          {{ $caseOption['label'] }} ({{ $caseOption['type_label'] }})
                        </option>
                      @endforeach
                    </select>
                    <div class="invalid-feedback" data-error-for="legal_case_id"></div>
                  </div>

                  <div class="mb-5 form-control-validation">
                    <label class="form-label" for="calendar_hearing_date">تاريخ الجلسة</label>
                    <input type="text" class="form-control flatpickr-date" id="calendar_hearing_date"
                      name="hearing_date" placeholder="YYYY-MM-DD" autocomplete="off" required />
                    <div class="invalid-feedback" data-error-for="hearing_date"></div>
                  </div>

                  <div class="mb-5 form-control-validation">
                    <label class="form-label" for="calendar_next_hearing_at">الجلسة القادمة</label>
                    <input type="text" class="form-control flatpickr-date" id="calendar_next_hearing_at"
                      name="next_hearing_at" placeholder="YYYY-MM-DD" autocomplete="off" required />
                    <div class="invalid-feedback" data-error-for="next_hearing_at"></div>
                  </div>

                  <div class="mb-5 form-control-validation">
                    <label class="form-label" for="calendar_roll_number">رقم الرول</label>
                    <input type="text" class="form-control" id="calendar_roll_number" name="roll_number" />
                    <div class="invalid-feedback" data-error-for="roll_number"></div>
                  </div>

                  <div class="mb-5 form-control-validation">
                    <label class="form-label" for="calendar_court_decision">قرار المحكمة</label>
                    <textarea class="form-control" id="calendar_court_decision" name="court_decision" rows="4" required></textarea>
                    <div class="invalid-feedback" data-error-for="court_decision"></div>
                  </div>

                  <div class="mb-5 form-control-validation">
                    <label class="form-label" for="calendar_notes">ملاحظات</label>
                    <textarea class="form-control" id="calendar_notes" name="notes" rows="3"></textarea>
                    <div class="invalid-feedback" data-error-for="notes"></div>
                  </div>

                  <div class="d-flex justify-content-start gap-2 mt-6">
                    <button type="submit" id="addEventBtn" class="btn btn-primary" data-default-text="حفظ الجلسة">
                      حفظ الجلسة
                    </button>
                    <button type="reset" class="btn btn-label-secondary btn-cancel" data-bs-dismiss="offcanvas">
                      إلغاء
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
