export const CONFIG = {
  hasDataTable: typeof window.DataTable !== 'undefined',
  hasFlatpickr: typeof window.flatpickr !== 'undefined',
  hasjQuery: typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined',
  hasSwal: typeof window.Swal !== 'undefined',
  hasDropzone: typeof window.Dropzone !== 'undefined',
  hasApexCharts: typeof window.ApexCharts !== 'undefined',
  hasStepper: typeof window.Stepper !== 'undefined',
  hasBootstrap: typeof window.bootstrap !== 'undefined',
  dataTransferSupported: typeof window !== 'undefined' && typeof window.DataTransfer !== 'undefined'
};

export const LANG = {
  search: '',
  searchPlaceholder: 'ابحث...',
  lengthMenu: 'اعرض _MENU_',
  info: 'عرض _START_ إلى _END_ من _TOTAL_ عنصر',
  infoEmpty: 'لا توجد بيانات لعرضها',
  infoFiltered: '(تمت التصفية من إجمالي _MAX_ عنصر)',
  zeroRecords: 'مفيش نتائج مطابقة',
  emptyTable: 'لا توجد بيانات حالياً',
  paginate: {
    next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
    previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
    first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
    last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
  }
};

export const PREVIEW_IMAGE_EXTENSIONS = new Set(['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);

export const dropzonePreviewTemplate = `
  <div class="dz-preview dz-file-preview">
    <div class="dz-details">
      <div class="dz-file-icon">
        <i class="icon-base ti tabler-file-text legal-dropzone-file-icon"></i>
      </div>
      <div class="flex-grow-1 min-w-0">
        <div class="dz-filename"><span data-dz-name></span></div>
        <div class="dz-size" data-dz-size></div>
        <div class="dz-error-message"><span data-dz-errormessage></span></div>
      </div>
    </div>
    <a class="dz-remove" href="javascript:void(0);" data-dz-remove>حذف الملف</a>
  </div>
`;

export default CONFIG;
