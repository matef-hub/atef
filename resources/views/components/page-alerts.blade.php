@php
  $errorListHtml = $errors->any()
      ? '<ul class="mb-0 ps-4 text-start">' .
          collect($errors->all())
              ->map(fn($error) => '<li class="mb-1">' . e($error) . '</li>')
              ->implode('') .
          '</ul>'
      : null;
@endphp

@if (session('success') || $errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof window.Swal === 'undefined') {
        return;
      }

      const alertDirection = document.documentElement.getAttribute('dir') || 'ltr';

      @if ($errors->any())
        window.Swal.fire({
          icon: 'error',
          title: @json(__('Whoops! Something went wrong.')),
          html: @json($errorListHtml),
          confirmButtonText: @json(__('OK')),
          didOpen: function (popup) {
            popup.setAttribute('dir', alertDirection);
          }
        });
      @endif

      @if (session('success'))
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: @json(session('success')),
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: function (toast) {
            toast.setAttribute('dir', alertDirection);
            toast.addEventListener('mouseenter', window.Swal.stopTimer);
            toast.addEventListener('mouseleave', window.Swal.resumeTimer);
          }
        });
      @endif
    });
  </script>

  <noscript>
    @if (session('success'))
      <div class="alert alert-success" role="alert">
        <div class="alert-body">{{ session('success') }}</div>
      </div>
    @endif

    @if ($errors->any())
      <x-validation-errors class="mb-4" />
    @endif
  </noscript>
@endif
