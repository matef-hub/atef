@extends('layouts/layoutMaster')

@section('title', 'إعدادات النظام')

@section('content')
  @php
    $envValue = fn (string $key, mixed $default = '') => $environment[$key] ?? $default;
    $envBool = fn (string $key, bool $default = false) => filter_var($environment[$key] ?? $default, FILTER_VALIDATE_BOOLEAN);
    $timezones = DateTimeZone::listIdentifiers();
    $localeOptions = [
        'ar' => 'العربية',
        'en' => 'English',
    ];
    $environmentOptions = [
        'local' => 'Local',
        'production' => 'Production',
        'staging' => 'Staging',
        'testing' => 'Testing',
    ];
  @endphp

  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <x-page-alerts />

    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <div>
        <span class="badge bg-label-primary rounded-pill mb-2">System Control Center</span>
        <h4 class="mb-1">إعدادات النظام والتثبيت</h4>
        <p class="mb-0 text-muted">إدارة إعدادات التشغيل، قاعدة البيانات، النسخ الاحتياطي، وأوامر الصيانة من مكان واحد.</p>
      </div>

      <form method="POST" action="{{ route('system-settings.backup.create') }}">
        @csrf
        <input type="hidden" name="backup_label" value="quick">
        <button type="submit" class="btn btn-primary">
          <i class="icon-base ti tabler-database-export me-1"></i>
          نسخة احتياطية سريعة
        </button>
      </form>
    </div>

    <div class="row g-4">
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-{{ $database['ok'] ? 'success' : 'danger' }}">
          <div class="card-body">
            <span class="badge bg-label-{{ $database['ok'] ? 'success' : 'danger' }} rounded-pill mb-2">
              {{ $database['ok'] ? 'متصل' : 'غير متصل' }}
            </span>
            <h5 class="mb-1">{{ $database['database'] ?: 'غير محدد' }}</h5>
            <p class="mb-0 text-muted">{{ $database['driver'] }} · {{ $database['host'] ?: 'local file' }}</p>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-primary">
          <div class="card-body">
            <span class="badge bg-label-primary rounded-pill mb-2">Laravel</span>
            <h5 class="mb-1">{{ $system['laravel'] }}</h5>
            <p class="mb-0 text-muted">PHP {{ $system['php'] }} · {{ $system['os'] }}</p>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-info">
          <div class="card-body">
            <span class="badge bg-label-info rounded-pill mb-2">التخزين</span>
            <h5 class="mb-1">{{ $system['storage_free'] }}</h5>
            <p class="mb-0 text-muted">متاح من {{ $system['storage_total'] }}</p>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 card-border-shadow-warning">
          <div class="card-body">
            <span class="badge bg-label-warning rounded-pill mb-2">النسخ</span>
            <h5 class="mb-1">{{ count($backups) }}</h5>
            <p class="mb-0 text-muted">نسخة محفوظة داخل storage</p>
          </div>
        </div>
      </div>
    </div>

    @if (!$database['ok'])
      <div class="alert alert-danger mb-0" role="alert">
        <div class="fw-semibold mb-1">تعذر الاتصال بقاعدة البيانات الحالية.</div>
        <div class="small">{{ $database['error'] }}</div>
      </div>
    @endif

    <div class="row g-4">
      <div class="col-xl-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">إعدادات التطبيق العامة</h5>
            <p class="card-subtitle mb-0">الاسم، الرابط، اللغة، المنطقة الزمنية، ووضع التشغيل.</p>
          </div>

          <form method="POST" action="{{ route('system-settings.general.update') }}">
            @csrf
            @method('PUT')

            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="app_name">اسم البرنامج</label>
                  <input id="app_name" name="app_name" type="text" class="form-control"
                    value="{{ old('app_name', $envValue('APP_NAME', config('app.name'))) }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="app_url">رابط التشغيل</label>
                  <input id="app_url" name="app_url" type="url" class="form-control"
                    value="{{ old('app_url', $envValue('APP_URL', config('app.url'))) }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="app_env">بيئة التشغيل</label>
                  <select id="app_env" name="app_env" class="form-select">
                    @foreach ($environmentOptions as $value => $label)
                      <option value="{{ $value }}" @selected(old('app_env', $envValue('APP_ENV', config('app.env'))) === $value)>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="app_timezone">المنطقة الزمنية</label>
                  <select id="app_timezone" name="app_timezone" class="form-select">
                    @foreach ($timezones as $timezone)
                      <option value="{{ $timezone }}" @selected(old('app_timezone', $envValue('APP_TIMEZONE', config('app.timezone'))) === $timezone)>
                        {{ $timezone }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="app_locale">لغة النظام</label>
                  <select id="app_locale" name="app_locale" class="form-select">
                    @foreach ($localeOptions as $value => $label)
                      <option value="{{ $value }}" @selected(old('app_locale', $envValue('APP_LOCALE', config('app.locale'))) === $value)>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="app_fallback_locale">لغة احتياطية</label>
                  <select id="app_fallback_locale" name="app_fallback_locale" class="form-select">
                    @foreach ($localeOptions as $value => $label)
                      <option value="{{ $value }}" @selected(old('app_fallback_locale', $envValue('APP_FALLBACK_LOCALE', config('app.fallback_locale'))) === $value)>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="app_faker_locale">لغة البيانات التجريبية</label>
                  <input id="app_faker_locale" name="app_faker_locale" type="text" class="form-control"
                    value="{{ old('app_faker_locale', $envValue('APP_FAKER_LOCALE', 'en_US')) }}">
                </div>

                <div class="col-12">
                  <label class="form-check form-switch mb-0">
                    <input type="checkbox" class="form-check-input" name="app_debug" value="1"
                      @checked(old('app_debug', $envBool('APP_DEBUG', config('app.debug'))))>
                    <span class="form-check-label">تفعيل وضع عرض الأخطاء التفصيلية</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ti tabler-device-floppy me-1"></i>
                حفظ الإعدادات
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">الخدمات والتشغيل</h5>
            <p class="card-subtitle mb-0">الجلسات، الكاش، الطوابير، التخزين، والبريد.</p>
          </div>

          <form method="POST" action="{{ route('system-settings.services.update') }}">
            @csrf
            @method('PUT')

            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="session_driver">حفظ الجلسات</label>
                  <select id="session_driver" name="session_driver" class="form-select">
                    @foreach (['file', 'database', 'redis', 'cookie', 'array'] as $driver)
                      <option value="{{ $driver }}" @selected(old('session_driver', $envValue('SESSION_DRIVER', config('session.driver'))) === $driver)>
                        {{ $driver }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="session_lifetime">مدة الجلسة بالدقائق</label>
                  <input id="session_lifetime" name="session_lifetime" type="number" min="5" max="43200"
                    class="form-control" value="{{ old('session_lifetime', $envValue('SESSION_LIFETIME', config('session.lifetime'))) }}">
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="cache_store">الكاش</label>
                  <select id="cache_store" name="cache_store" class="form-select">
                    @foreach (['file', 'database', 'redis', 'array'] as $store)
                      <option value="{{ $store }}" @selected(old('cache_store', $envValue('CACHE_STORE', config('cache.default'))) === $store)>
                        {{ $store }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="queue_connection">الطوابير</label>
                  <select id="queue_connection" name="queue_connection" class="form-select">
                    @foreach (['sync', 'database', 'redis'] as $queue)
                      <option value="{{ $queue }}" @selected(old('queue_connection', $envValue('QUEUE_CONNECTION', config('queue.default'))) === $queue)>
                        {{ $queue }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="filesystem_disk">التخزين</label>
                  <select id="filesystem_disk" name="filesystem_disk" class="form-select">
                    @foreach (['local', 'public'] as $disk)
                      <option value="{{ $disk }}" @selected(old('filesystem_disk', $envValue('FILESYSTEM_DISK', config('filesystems.default'))) === $disk)>
                        {{ $disk }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="mail_mailer">خدمة البريد</label>
                  <select id="mail_mailer" name="mail_mailer" class="form-select">
                    @foreach (['log', 'smtp', 'sendmail', 'array'] as $mailer)
                      <option value="{{ $mailer }}" @selected(old('mail_mailer', $envValue('MAIL_MAILER', config('mail.default'))) === $mailer)>
                        {{ $mailer }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="mail_host">SMTP Host</label>
                  <input id="mail_host" name="mail_host" type="text" class="form-control"
                    value="{{ old('mail_host', $envValue('MAIL_HOST', '127.0.0.1')) }}">
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="mail_port">SMTP Port</label>
                  <input id="mail_port" name="mail_port" type="number" class="form-control"
                    value="{{ old('mail_port', $envValue('MAIL_PORT', 2525)) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_username">اسم مستخدم البريد</label>
                  <input id="mail_username" name="mail_username" type="text" class="form-control"
                    value="{{ old('mail_username', $envValue('MAIL_USERNAME')) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_password">كلمة مرور البريد</label>
                  <input id="mail_password" name="mail_password" type="password" class="form-control"
                    placeholder="{{ $envValue('MAIL_PASSWORD') ? 'كلمة مرور محفوظة' : 'غير محددة' }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_from_address">بريد الإرسال</label>
                  <input id="mail_from_address" name="mail_from_address" type="email" class="form-control"
                    value="{{ old('mail_from_address', $envValue('MAIL_FROM_ADDRESS', 'hello@example.com')) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_from_name">اسم المرسل</label>
                  <input id="mail_from_name" name="mail_from_name" type="text" class="form-control"
                    value="{{ old('mail_from_name', $envValue('MAIL_FROM_NAME', config('app.name'))) }}">
                </div>

                <div class="col-12">
                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="clear_mail_password" value="1">
                    <span class="form-check-label">مسح كلمة مرور البريد المحفوظة</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ti tabler-device-floppy me-1"></i>
                حفظ الخدمات
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-7">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">اتصال قاعدة البيانات</h5>
            <p class="card-subtitle mb-0">تغيير الاتصال المستخدم، وتجهيز مسارات أدوات MySQL الخاصة بالنسخ والاسترجاع.</p>
          </div>

          <form method="POST" action="{{ route('system-settings.database.update') }}">
            @csrf
            @method('PUT')

            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label" for="db_connection">نوع الاتصال</label>
                  <select id="db_connection" name="db_connection" class="form-select">
                    @foreach (['mysql', 'mariadb', 'sqlite'] as $connection)
                      <option value="{{ $connection }}" @selected(old('db_connection', $envValue('DB_CONNECTION', config('database.default'))) === $connection)>
                        {{ $connection }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="db_host">Host</label>
                  <input id="db_host" name="db_host" type="text" class="form-control"
                    value="{{ old('db_host', $envValue('DB_HOST', '127.0.0.1')) }}">
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="db_port">Port</label>
                  <input id="db_port" name="db_port" type="number" class="form-control"
                    value="{{ old('db_port', $envValue('DB_PORT', 3306)) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="db_database">اسم/مسار قاعدة البيانات</label>
                  <input id="db_database" name="db_database" type="text" class="form-control"
                    value="{{ old('db_database', $envValue('DB_DATABASE', config('database.connections.' . config('database.default') . '.database'))) }}"
                    required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="db_username">اسم المستخدم</label>
                  <input id="db_username" name="db_username" type="text" class="form-control"
                    value="{{ old('db_username', $envValue('DB_USERNAME')) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="db_password">كلمة المرور</label>
                  <input id="db_password" name="db_password" type="password" class="form-control"
                    placeholder="{{ $envValue('DB_PASSWORD') ? 'كلمة مرور محفوظة' : 'بدون كلمة مرور' }}">
                </div>

                <div class="col-md-3">
                  <label class="form-label" for="db_charset">Charset</label>
                  <input id="db_charset" name="db_charset" type="text" class="form-control"
                    value="{{ old('db_charset', $envValue('DB_CHARSET', 'utf8mb4')) }}">
                </div>

                <div class="col-md-3">
                  <label class="form-label" for="db_collation">Collation</label>
                  <input id="db_collation" name="db_collation" type="text" class="form-control"
                    value="{{ old('db_collation', $envValue('DB_COLLATION', 'utf8mb4_unicode_ci')) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mysql_binary_path">مسار mysql.exe</label>
                  <input id="mysql_binary_path" name="mysql_binary_path" type="text" class="form-control"
                    value="{{ old('mysql_binary_path', $envValue('SYSTEM_MYSQL_PATH', 'C:\\xampp\\mysql\\bin\\mysql.exe')) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mysqldump_binary_path">مسار mysqldump.exe</label>
                  <input id="mysqldump_binary_path" name="mysqldump_binary_path" type="text" class="form-control"
                    value="{{ old('mysqldump_binary_path', $envValue('SYSTEM_MYSQLDUMP_PATH', 'C:\\xampp\\mysql\\bin\\mysqldump.exe')) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="test_connection" value="1">
                    <span class="form-check-label">اختبار الاتصال قبل الحفظ</span>
                  </label>
                </div>

                <div class="col-md-6">
                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="clear_db_password" value="1">
                    <span class="form-check-label">مسح كلمة مرور قاعدة البيانات</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ti tabler-database-cog me-1"></i>
                حفظ اتصال قاعدة البيانات
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-xl-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">إنشاء قاعدة بيانات جديدة</h5>
            <p class="card-subtitle mb-0">مناسب لأول تثبيت على جهاز جديد أو تجهيز بيئة عمل منفصلة.</p>
          </div>

          <form method="POST" action="{{ route('system-settings.database.create') }}" data-swal-confirm="true"
            data-swal-title="تأكيد إنشاء قاعدة بيانات"
            data-swal-text="سيتم إنشاء قاعدة بيانات على خادم MySQL المحدد. تأكد من صحة بيانات الخادم."
            data-swal-icon="question" data-swal-confirm-button="نعم، أنشئ القاعدة" data-swal-cancel-button="إلغاء">
            @csrf

            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="server_host">Host</label>
                  <input id="server_host" name="server_host" type="text" class="form-control"
                    value="{{ old('server_host', $envValue('DB_HOST', '127.0.0.1')) }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="server_port">Port</label>
                  <input id="server_port" name="server_port" type="number" class="form-control"
                    value="{{ old('server_port', $envValue('DB_PORT', 3306)) }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="server_username">اسم المستخدم</label>
                  <input id="server_username" name="server_username" type="text" class="form-control"
                    value="{{ old('server_username', $envValue('DB_USERNAME', 'root')) }}">
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="server_password">كلمة المرور</label>
                  <input id="server_password" name="server_password" type="password" class="form-control">
                </div>

                <div class="col-12">
                  <label class="form-label" for="database_name">اسم القاعدة الجديدة</label>
                  <input id="database_name" name="database_name" type="text" class="form-control"
                    value="{{ old('database_name') }}" placeholder="legal_system" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="charset">Charset</label>
                  <input id="charset" name="charset" type="text" class="form-control"
                    value="{{ old('charset', 'utf8mb4') }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="collation">Collation</label>
                  <input id="collation" name="collation" type="text" class="form-control"
                    value="{{ old('collation', 'utf8mb4_unicode_ci') }}" required>
                </div>

                <div class="col-12 d-flex flex-column gap-2">
                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="save_as_active" value="1" checked>
                    <span class="form-check-label">اعتمادها كقاعدة التطبيق الحالية</span>
                  </label>

                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="run_migrations" value="1" checked>
                    <span class="form-check-label">تشغيل جداول النظام بعد الإنشاء</span>
                  </label>

                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="run_seeders" value="1">
                    <span class="form-check-label">إضافة بيانات تجريبية ومستخدم test@example.com</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ti tabler-database-plus me-1"></i>
                إنشاء القاعدة
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-8">
        <div class="card h-100">
          <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
              <h5 class="card-title mb-1">النسخ الاحتياطي والاسترجاع</h5>
              <p class="card-subtitle mb-0">الاسترجاع ينشئ نسخة احتياطية تلقائيًا قبل تنفيذ العملية.</p>
            </div>

            <form method="POST" action="{{ route('system-settings.backup.create') }}" class="d-flex gap-2">
              @csrf
              <input type="text" name="backup_label" class="form-control form-control-sm" placeholder="اسم اختياري">
              <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                <i class="icon-base ti tabler-database-export me-1"></i>
                إنشاء نسخة
              </button>
            </form>
          </div>

          <div class="card-datatable table-responsive">
            <table class="table border-top legal-datatable mb-0">
              <thead>
                <tr>
                  <th>اسم الملف</th>
                  <th>الحجم</th>
                  <th>تاريخ الإنشاء</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($backups as $backup)
                  <tr>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="fw-semibold">{{ $backup['name'] }}</span>
                        <small class="text-muted">{{ strtoupper($backup['extension']) }}</small>
                      </div>
                    </td>
                    <td>{{ $backup['size'] }}</td>
                    <td>{{ $backup['created_at'] }}</td>
                    <td class="text-nowrap">
                      <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('system-settings.backup.download', $backup['name']) }}"
                          class="btn btn-sm btn-outline-primary">
                          <i class="icon-base ti tabler-download"></i>
                        </a>

                        <form method="POST" action="{{ route('system-settings.backup.restore') }}"
                          data-swal-confirm="true" data-swal-title="تأكيد استرجاع النسخة"
                          data-swal-text="سيتم أخذ نسخة احتياطية من الوضع الحالي أولًا ثم استرجاع هذا الملف."
                          data-swal-icon="warning" data-swal-confirm-button="نعم، استرجع"
                          data-swal-cancel-button="إلغاء">
                          @csrf
                          <input type="hidden" name="selected_backup" value="{{ $backup['name'] }}">
                          <input type="hidden" name="confirm_restore" value="استرجاع">
                          <button type="submit" class="btn btn-sm btn-outline-warning">
                            <i class="icon-base ti tabler-restore"></i>
                          </button>
                        </form>

                        <form method="POST" action="{{ route('system-settings.backup.delete', $backup['name']) }}"
                          data-swal-confirm="true" data-swal-title="حذف النسخة الاحتياطية"
                          data-swal-text="سيتم حذف هذا الملف من مجلد التخزين." data-swal-icon="warning"
                          data-swal-confirm-button="نعم، احذف" data-swal-cancel-button="إلغاء">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="icon-base ti tabler-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">لا توجد نسخ احتياطية محفوظة بعد.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-xl-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">استرجاع من ملف خارجي</h5>
            <p class="card-subtitle mb-0">ارفع ملف SQL أو SQLite، واكتب كلمة التأكيد.</p>
          </div>

          <form method="POST" action="{{ route('system-settings.backup.restore') }}" enctype="multipart/form-data"
            data-swal-confirm="true" data-swal-title="تأكيد استرجاع ملف خارجي"
            data-swal-text="سيتم أخذ نسخة احتياطية من قاعدة البيانات الحالية قبل الاسترجاع."
            data-swal-icon="warning" data-swal-confirm-button="نعم، استرجع" data-swal-cancel-button="إلغاء">
            @csrf

            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="uploaded_backup">ملف النسخة الاحتياطية</label>
                <input id="uploaded_backup" name="uploaded_backup" type="file" class="form-control" accept=".sql,.sqlite,.db"
                  required>
              </div>

              <div>
                <label class="form-label" for="confirm_restore">كلمة التأكيد</label>
                <input id="confirm_restore" name="confirm_restore" type="text" class="form-control"
                  placeholder="اكتب: استرجاع" required>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-warning">
                <i class="icon-base ti tabler-upload me-1"></i>
                استرجاع الملف
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-5">
        <div class="card h-100 border-danger">
          <div class="card-header">
            <h5 class="card-title mb-1 text-danger">تهيئة قاعدة البيانات الحالية</h5>
            <p class="card-subtitle mb-0">هذه العملية تحذف الجداول الحالية، وتنشئ نسخة احتياطية قبل الحذف.</p>
          </div>

          <form method="POST" action="{{ route('system-settings.database.fresh') }}" data-swal-confirm="true"
            data-swal-title="تأكيد تهيئة قاعدة البيانات"
            data-swal-text="سيتم حذف كل الجداول الحالية بعد أخذ نسخة احتياطية تلقائية."
            data-swal-icon="warning" data-swal-confirm-button="نعم، هيّئ القاعدة" data-swal-cancel-button="إلغاء">
            @csrf

            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="confirm_fresh">كلمة التأكيد</label>
                <input id="confirm_fresh" name="confirm_fresh" type="text" class="form-control"
                  placeholder="اكتب: تهيئة" required>
              </div>

              <label class="form-check mb-0">
                <input type="checkbox" class="form-check-input" name="seed_after_fresh" value="1">
                <span class="form-check-label">إضافة البيانات التجريبية بعد التهيئة</span>
              </label>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-danger">
                <i class="icon-base ti tabler-database-x me-1"></i>
                تهيئة القاعدة
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-xl-7">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-1">أوامر الصيانة السريعة</h5>
            <p class="card-subtitle mb-0">تنظيف الكاش، تشغيل migrations، توليد مفتاح التطبيق، وربط التخزين.</p>
          </div>

          <div class="card-body">
            <div class="row g-3">
              @foreach ($maintenanceActions as $key => $action)
                <div class="col-md-6">
                  <form method="POST" action="{{ route('system-settings.maintenance.run') }}"
                    @if (in_array($key, ['key_generate', 'migrate'], true)) data-swal-confirm="true"
                      data-swal-title="تأكيد تنفيذ الأمر"
                      data-swal-text="سيتم تشغيل أمر صيانة مؤثر على إعدادات أو قاعدة بيانات النظام."
                      data-swal-icon="question"
                      data-swal-confirm-button="نعم، نفذ"
                      data-swal-cancel-button="إلغاء" @endif>
                    @csrf
                    <input type="hidden" name="action" value="{{ $key }}">
                    <button type="submit" class="btn btn-outline-primary w-100 justify-content-start">
                      <i class="icon-base ti {{ $action['icon'] }} me-2"></i>
                      {{ $action['label'] }}
                    </button>
                  </form>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
