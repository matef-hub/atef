import arLocale from '@fullcalendar/core/locales/ar';
import { Arabic } from 'flatpickr/dist/l10n/ar.js';

/**
 * Legal hearings calendar
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.querySelector('[data-hearing-calendar]');

  if (!calendarEl || typeof window.Calendar === 'undefined') {
    return;
  }

  const direction = window.isRtl || document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr';
  const eventsUrl = calendarEl.dataset.eventsUrl;
  const appCalendarSidebar = document.querySelector('.app-calendar-sidebar');
  const appOverlay = document.querySelector('.app-overlay');
  const addEventSidebar = document.getElementById('addEventSidebar');
  const offcanvasTitle = document.querySelector('.offcanvas-title');
  const formPanel = addEventSidebar?.querySelector('[data-calendar-form-panel]');
  const viewPanel = addEventSidebar?.querySelector('[data-calendar-view-panel]');
  const form = document.getElementById('hearingCalendarForm');
  const btnSubmit = document.getElementById('addEventBtn');
  const selectAll = document.querySelector('.select-all');
  const filterInputs = Array.from(document.querySelectorAll('.input-filter'));
  const inlineCalendar = document.querySelector('[data-inline-calendar]');
  const alertBox = document.querySelector('[data-calendar-alert]');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const bsAddEventSidebar = window.bootstrap.Offcanvas.getOrCreateInstance(addEventSidebar);

  let inlineCalInstance = null;

  function selectedCalendars() {
    return filterInputs.filter(input => input.checked).map(input => input.dataset.value);
  }

  function eventSource(info, successCallback, failureCallback) {
    const url = new URL(eventsUrl, window.location.origin);
    url.searchParams.set('start', info.startStr);
    url.searchParams.set('end', info.endStr);

    selectedCalendars().forEach(type => {
      url.searchParams.append('types[]', type);
    });

    fetch(url, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Failed to load calendar events.');
        }

        return response.json();
      })
      .then(events => successCallback(Array.isArray(events) ? events : []))
      .catch(error => {
        showNotice('تعذر تحميل جلسات التقويم. حاول مرة أخرى.', 'danger');
        if (typeof failureCallback === 'function') {
          failureCallback(error);
        }
      });
  }

  function modifyToggler() {
    const fcSidebarToggleButton = document.querySelector('.fc-sidebarToggle-button');

    if (!fcSidebarToggleButton) {
      return;
    }

    fcSidebarToggleButton.classList.remove('fc-button-primary');
    fcSidebarToggleButton.classList.add('d-lg-none', 'd-inline-block', 'ps-0');

    while (fcSidebarToggleButton.firstChild) {
      fcSidebarToggleButton.firstChild.remove();
    }

    fcSidebarToggleButton.setAttribute('data-bs-toggle', 'sidebar');
    fcSidebarToggleButton.setAttribute('data-overlay', '');
    fcSidebarToggleButton.setAttribute('data-target', '#app-calendar-sidebar');
    fcSidebarToggleButton.insertAdjacentHTML(
      'beforeend',
      '<i class="icon-base ti tabler-menu-2 icon-lg text-heading"></i>'
    );
  }

  const calendar = new window.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    plugins: [window.dayGridPlugin, window.interactionPlugin, window.listPlugin, window.timegridPlugin],
    locales: [arLocale],
    locale: 'ar',
    direction,
    events: eventSource,
    editable: false,
    selectable: true,
    navLinks: true,
    dayMaxEvents: 3,
    customButtons: {
      sidebarToggle: {
        text: 'Sidebar'
      }
    },
    headerToolbar: {
      start: 'sidebarToggle, prev,next, title',
      end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    },
    buttonText: {
      today: 'اليوم',
      month: 'شهر',
      week: 'أسبوع',
      day: 'يوم',
      list: 'أجندة'
    },
    allDayText: 'اليوم كله',
    noEventsText: 'لا توجد جلسات',
    moreLinkText: count => `+${count} أخرى`,
    eventClassNames: function ({ event }) {
      return ['bg-label-' + (event.extendedProps.color || 'primary')];
    },
    dateClick: function (info) {
      openForm(info.dateStr);
    },
    eventClick: function (info) {
      info.jsEvent.preventDefault();
      openDetails(info.event);
    },
    datesSet: modifyToggler,
    viewDidMount: modifyToggler
  });

  calendar.render();
  modifyToggler();

  if (inlineCalendar && typeof window.flatpickr !== 'undefined') {
    inlineCalInstance = window.flatpickr(inlineCalendar, {
      inline: true,
      locale: Arabic,
      monthSelectorType: 'static',
      onChange(selectedDates) {
        const [selectedDate] = selectedDates;

        if (!selectedDate) {
          return;
        }

        calendar.changeView(calendar.view.type, formatDate(selectedDate));
        modifyToggler();
        appCalendarSidebar?.classList.remove('show');
        appOverlay?.classList.remove('show');
      }
    });
  }

  document.querySelectorAll('[data-calendar-add]').forEach(button => {
    button.addEventListener('click', () => openForm(formatDate(new Date())));
  });

  if (form) {
    form.addEventListener('submit', submitForm);
  }

  if (selectAll) {
    selectAll.addEventListener('change', event => {
      filterInputs.forEach(input => {
        input.checked = event.currentTarget.checked;
      });
      calendar.refetchEvents();
    });
  }

  filterInputs.forEach(input => {
    input.addEventListener('change', () => {
      if (selectAll) {
        selectAll.checked = filterInputs.every(filter => filter.checked);
      }
      calendar.refetchEvents();
    });
  });

  addEventSidebar.addEventListener('hidden.bs.offcanvas', function () {
    clearFormErrors();
    resetForm();
    formPanel?.classList.add('d-none');
    viewPanel?.classList.add('d-none');
  });

  function openForm(date) {
    clearFormErrors();
    resetForm();
    setOffcanvasTitle('إضافة جلسة');
    viewPanel?.classList.add('d-none');
    formPanel?.classList.remove('d-none');

    const selectedDate = date || formatDate(new Date());
    setFieldValue('hearing_date', selectedDate);
    setFieldValue('next_hearing_at', selectedDate);

    appCalendarSidebar?.classList.remove('show');
    appOverlay?.classList.remove('show');
    bsAddEventSidebar.show();
  }

  function openDetails(event) {
    const props = event.extendedProps || {};
    const legalCase = props.case || {};

    setOffcanvasTitle('تفاصيل الجلسة');
    formPanel?.classList.add('d-none');
    viewPanel?.classList.remove('d-none');

    setDetail('next_hearing_at', props.next_hearing_at || event.startStr);
    setDetail('case_number', legalCase.number);
    setDetail('case_type_label', legalCase.type_label);
    setDetail('parties', legalCase.parties);
    setDetail('court_name', legalCase.court_name);
    setDetail('circuit_number', legalCase.circuit_number);
    setDetail('hearing_date', props.hearing_date);
    setDetail('roll_number', props.roll_number);
    setDetail('court_decision', props.court_decision);
    setDetail('notes', props.notes);
    setDetailLink('edit', props.edit_url);
    setDetailLink('case', legalCase.edit_url);
    setDetailLink('list', props.list_url);

    bsAddEventSidebar.show();
  }

  function submitForm(event) {
    event.preventDefault();
    clearFormErrors();
    setSubmitLoading(true);

    fetch(form.action, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new FormData(form)
    })
      .then(async response => {
        const data = await response.json().catch(() => ({}));

        if (response.status === 422) {
          showFormErrors(data.errors || {});
          throw new Error(data.message || 'Validation failed.');
        }

        if (!response.ok) {
          throw new Error(data.message || 'Failed to save hearing.');
        }

        return data;
      })
      .then(data => {
        showNotice(data.message || 'تم تسجيل الجلسة بنجاح.', 'success');
        bsAddEventSidebar.hide();
        calendar.refetchEvents();
      })
      .catch(error => {
        if (!getCurrentErrors().length) {
          showNotice(error.message || 'تعذر حفظ الجلسة. حاول مرة أخرى.', 'danger');
        }
      })
      .finally(() => setSubmitLoading(false));
  }

  function setSubmitLoading(isLoading) {
    if (!btnSubmit) {
      return;
    }

    btnSubmit.disabled = isLoading;
    btnSubmit.textContent = isLoading ? 'جاري الحفظ...' : btnSubmit.dataset.defaultText || 'حفظ الجلسة';
  }

  function resetForm() {
    if (!form) {
      return;
    }

    form.reset();
    setFieldValue('legal_case_id', '');
    setFieldValue('hearing_date', '');
    setFieldValue('next_hearing_at', '');
  }

  function setFieldValue(name, value) {
    const field = form?.elements[name];

    if (!field) {
      return;
    }

    if (field._flatpickr) {
      if (value) {
        field._flatpickr.setDate(value, true);
      } else {
        field._flatpickr.clear();
      }
      return;
    }

    field.value = value || '';

    if (
      window.jQuery &&
      window.jQuery.fn.select2 &&
      window.jQuery(field).hasClass('select2-hidden-accessible')
    ) {
      window.jQuery(field).val(value || '').trigger('change');
    }
  }

  function showFormErrors(errors) {
    Object.entries(errors).forEach(([field, messages]) => {
      const input = form?.elements[field];
      const target = form?.querySelector(`[data-error-for="${field}"]`);
      const message = Array.isArray(messages) ? messages[0] : messages;

      input?.classList.add('is-invalid');

      if (target) {
        target.textContent = message || 'قيمة غير صحيحة.';
        target.classList.add('d-block');
      }
    });
  }

  function clearFormErrors() {
    form?.querySelectorAll('.is-invalid').forEach(element => element.classList.remove('is-invalid'));
    form?.querySelectorAll('[data-error-for]').forEach(element => {
      element.textContent = '';
      element.classList.remove('d-block');
    });
  }

  function getCurrentErrors() {
    return Array.from(form?.querySelectorAll('[data-error-for].d-block') || []);
  }

  function setOffcanvasTitle(title) {
    if (offcanvasTitle) {
      offcanvasTitle.textContent = title;
    }
  }

  function setDetail(key, value) {
    const element = viewPanel?.querySelector(`[data-detail="${key}"]`);

    if (element) {
      element.textContent = value || '-';
    }
  }

  function setDetailLink(key, url) {
    const link = viewPanel?.querySelector(`[data-detail-link="${key}"]`);

    if (!link) {
      return;
    }

    if (url) {
      link.href = url;
      link.classList.remove('disabled');
      link.removeAttribute('aria-disabled');
      return;
    }

    link.href = '#';
    link.classList.add('disabled');
    link.setAttribute('aria-disabled', 'true');
  }

  function showNotice(message, type = 'success') {
    if (!alertBox) {
      return;
    }

    alertBox.className = `legal-calendar-alert alert alert-${type} mb-0`;
    alertBox.textContent = message;
  }

  function formatDate(date) {
    const year = date.getFullYear();
    const month = `${date.getMonth() + 1}`.padStart(2, '0');
    const day = `${date.getDate()}`.padStart(2, '0');

    return `${year}-${month}-${day}`;
  }

  if (inlineCalInstance) {
    inlineCalInstance.jumpToDate(new Date());
  }
});
