@php
  $successMessage = session('success');
  $successDetail = session('success_detail');
  $successHtml = $successDetail
      ? '<div class="text-end">' .
          '<div class="fw-semibold mb-1">' .
          e($successMessage) .
          '</div>' .
          '<code class="d-block small text-break bg-label-success rounded px-2 py-1" dir="ltr">' .
          e($successDetail) .
          '</code>' .
          '</div>'
      : null;

  $errorListHtml = $errors->any()
      ? '<ul class="mb-0 ps-4 text-start">' .
          collect($errors->all())->map(fn($error) => '<li class="mb-1">' . e($error) . '</li>')->implode('') .
          '</ul>'
      : null;
@endphp

@if (session('success') || $errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof window.Swal === 'undefined') {
        return;
      }

      const alertDirection = document.documentElement.getAttribute('dir') || 'ltr';

      @if ($errors->any())
        window.Swal.fire({
          icon: 'error',
          title: @json(__('خلى بالك فى بيانات ناقصة أو فيها مشكلة')),
          html: @json($errorListHtml),
          confirmButtonText: @json(__('نحاول تانى')),
          didOpen: function(popup) {
            popup.setAttribute('dir', alertDirection);
          }
        });
      @endif

      @if (session('success'))
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          @if ($successHtml)
            html: @json($successHtml),
          @else
            title: @json($successMessage),
          @endif
          showConfirmButton: false,
          timer: @json($successDetail ? 5000 : 3000),
          timerProgressBar: true,
          didOpen: function(toast) {
            toast.setAttribute('dir', alertDirection);
            toast.style.maxWidth = 'min(92vw, 420px)';
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
        <div class="alert-body">
          {{ $successMessage }}
          @if ($successDetail)
            <code class="d-block small text-break mt-1" dir="ltr">{{ $successDetail }}</code>
          @endif
        </div>
      </div>
    @endif

    @if ($errors->any())
      <x-validation-errors class="mb-4" />
    @endif
  </noscript>
@endif
