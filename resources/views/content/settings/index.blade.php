@extends('layouts/layoutMaster')

@section('title', 'إعدادات النظام')

@section('content')
  @php
    /*
        $timezones, $localeOptions, $environmentOptions are now passed from the
        controller's index() method — removed from the Blade @php block.

    Only lightweight view-helpers that are purely presentational live here.
  */
$envValue = fn(string $key, mixed $default = '') => $environment[$key] ?? $default;
    $envBool = fn(string $key, bool $default = false) => filter_var(
        $environment[$key] ?? $default,
        FILTER_VALIDATE_BOOLEAN,
    );
  @endphp

  <div class="legal-resource-page d-flex flex-column gap-4" dir="rtl">
    <x-page-alerts />

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
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

    {{-- ── Status cards ─────────────────────────────────────────────────── --}}
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

    @unless ($database['ok'])
      <div class="alert alert-danger mb-0" role="alert">
        <div class="fw-semibold mb-1">تعذر الاتصال بقاعدة البيانات الحالية.</div>
        <div class="small">{{ $database['error'] }}</div>
      </div>
    @endunless

    {{-- ── General settings + Services ─────────────────────────────────── --}}
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
                  <input id="app_name" name="app_name" type="text" autocomplete="off"
                    class="form-control @error('app_name') is-invalid @enderror"
                    value="{{ old('app_name', $envValue('APP_NAME', config('app.name'))) }}" required>
                  @error('app_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="app_url">رابط التشغيل</label>
                  <input id="app_url" name="app_url" type="url" autocomplete="off"
                    class="form-control @error('app_url') is-invalid @enderror"
                    value="{{ old('app_url', $envValue('APP_URL', config('app.url'))) }}" required>
                  @error('app_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="app_env">بيئة التشغيل</label>
                  <select id="app_env" name="app_env" class="form-select @error('app_env') is-invalid @enderror">
                    @foreach ($environmentOptions as $value => $label)
                      <option value="{{ $value }}" @selected(old('app_env', $envValue('APP_ENV', config('app.env'))) === $value)>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                  @error('app_env')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="app_timezone">المنطقة الزمنية</label>
                  {{-- $timezones now passed from the controller, not computed in the blade --}}
                  <select id="app_timezone" name="app_timezone"
                    class="form-select @error('app_timezone') is-invalid @enderror">
                    @foreach ($timezones as $timezone)
                      <option value="{{ $timezone }}" @selected(old('app_timezone', $envValue('APP_TIMEZONE', config('app.timezone'))) === $timezone)>
                        {{ $timezone }}
                      </option>
                    @endforeach
                  </select>
                  @error('app_timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
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
                  <input id="app_faker_locale" name="app_faker_locale" type="text"
                    class="form-control @error('app_faker_locale') is-invalid @enderror"
                    value="{{ old('app_faker_locale', $envValue('APP_FAKER_LOCALE', 'en_US')) }}">
                  @error('app_faker_locale')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
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
                    class="form-control @error('session_lifetime') is-invalid @enderror"
                    value="{{ old('session_lifetime', $envValue('SESSION_LIFETIME', config('session.lifetime'))) }}">
                  @error('session_lifetime')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
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
                  <input id="mail_host" name="mail_host" type="text" autocomplete="off"
                    class="form-control @error('mail_host') is-invalid @enderror"
                    value="{{ old('mail_host', $envValue('MAIL_HOST', '127.0.0.1')) }}">
                  @error('mail_host')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="mail_port">SMTP Port</label>
                  <input id="mail_port" name="mail_port" type="number"
                    class="form-control @error('mail_port') is-invalid @enderror"
                    value="{{ old('mail_port', $envValue('MAIL_PORT', 2525)) }}">
                  @error('mail_port')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_username">اسم مستخدم البريد</label>
                  <input id="mail_username" name="mail_username" type="text" autocomplete="username"
                    class="form-control @error('mail_username') is-invalid @enderror"
                    value="{{ old('mail_username', $envValue('MAIL_USERNAME')) }}">
                  @error('mail_username')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_password">كلمة مرور البريد</label>
                  {{-- Never pre-fill password fields; use placeholder to signal saved state --}}
                  <input id="mail_password" name="mail_password" type="password" autocomplete="new-password"
                    class="form-control @error('mail_password') is-invalid @enderror"
                    placeholder="{{ $envValue('MAIL_PASSWORD') ? 'كلمة مرور محفوظة' : 'غير محددة' }}">
                  @error('mail_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_from_address">بريد الإرسال</label>
                  <input id="mail_from_address" name="mail_from_address" type="email" autocomplete="off"
                    class="form-control @error('mail_from_address') is-invalid @enderror"
                    value="{{ old('mail_from_address', $envValue('MAIL_FROM_ADDRESS', 'hello@example.com')) }}">
                  @error('mail_from_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mail_from_name">اسم المرسل</label>
                  <input id="mail_from_name" name="mail_from_name" type="text" autocomplete="off"
                    class="form-control @error('mail_from_name') is-invalid @enderror"
                    value="{{ old('mail_from_name', $envValue('MAIL_FROM_NAME', config('app.name'))) }}">
                  @error('mail_from_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
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

    {{-- ── Database connection + Create new database ─────────────────────── --}}
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
                  <input id="db_host" name="db_host" type="text" autocomplete="off"
                    class="form-control @error('db_host') is-invalid @enderror"
                    value="{{ old('db_host', $envValue('DB_HOST', '127.0.0.1')) }}">
                  @error('db_host')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="db_port">Port</label>
                  <input id="db_port" name="db_port" type="number"
                    class="form-control @error('db_port') is-invalid @enderror"
                    value="{{ old('db_port', $envValue('DB_PORT', 3306)) }}">
                  @error('db_port')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="db_database">اسم/مسار قاعدة البيانات</label>
                  {{--
                    BUG FIX: original blade had a deeply nested config() call:
                    config('database.connections.' . config('database.default') . '.database')
                    This is logic that belongs in the controller; it's now available via $environment.
                  --}}
                  <input id="db_database" name="db_database" type="text" autocomplete="off"
                    class="form-control @error('db_database') is-invalid @enderror"
                    value="{{ old('db_database', $envValue('DB_DATABASE')) }}" required>
                  @error('db_database')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="db_username">اسم المستخدم</label>
                  <input id="db_username" name="db_username" type="text" autocomplete="username"
                    class="form-control @error('db_username') is-invalid @enderror"
                    value="{{ old('db_username', $envValue('DB_USERNAME')) }}">
                  @error('db_username')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="db_password">كلمة المرور</label>
                  <input id="db_password" name="db_password" type="password" autocomplete="new-password"
                    class="form-control @error('db_password') is-invalid @enderror"
                    placeholder="{{ $envValue('DB_PASSWORD') ? 'كلمة مرور محفوظة' : 'بدون كلمة مرور' }}">
                  @error('db_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label class="form-label" for="db_charset">Charset</label>
                  <input id="db_charset" name="db_charset" type="text"
                    class="form-control @error('db_charset') is-invalid @enderror"
                    value="{{ old('db_charset', $envValue('DB_CHARSET', 'utf8mb4')) }}">
                  @error('db_charset')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label class="form-label" for="db_collation">Collation</label>
                  <input id="db_collation" name="db_collation" type="text"
                    class="form-control @error('db_collation') is-invalid @enderror"
                    value="{{ old('db_collation', $envValue('DB_COLLATION', 'utf8mb4_unicode_ci')) }}">
                  @error('db_collation')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mysql_binary_path">مسار mysql.exe</label>
                  <input id="mysql_binary_path" name="mysql_binary_path" type="text" autocomplete="off"
                    class="form-control @error('mysql_binary_path') is-invalid @enderror"
                    value="{{ old('mysql_binary_path', $envValue('SYSTEM_MYSQL_PATH')) }}">
                  @error('mysql_binary_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="mysqldump_binary_path">مسار mysqldump.exe</label>
                  <input id="mysqldump_binary_path" name="mysqldump_binary_path" type="text" autocomplete="off"
                    class="form-control @error('mysqldump_binary_path') is-invalid @enderror"
                    value="{{ old('mysqldump_binary_path', $envValue('SYSTEM_MYSQLDUMP_PATH')) }}">
                  @error('mysqldump_binary_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                @error('database')
                  <div class="col-12">
                    <div class="alert alert-danger py-2 mb-0">{{ $message }}</div>
                  </div>
                @enderror

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
                  <input id="server_host" name="server_host" type="text" autocomplete="off"
                    class="form-control @error('server_host') is-invalid @enderror"
                    value="{{ old('server_host', $envValue('DB_HOST', '127.0.0.1')) }}" required>
                  @error('server_host')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="server_port">Port</label>
                  <input id="server_port" name="server_port" type="number"
                    class="form-control @error('server_port') is-invalid @enderror"
                    value="{{ old('server_port', $envValue('DB_PORT', 3306)) }}" required>
                  @error('server_port')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="server_username">اسم المستخدم</label>
                  <input id="server_username" name="server_username" type="text" autocomplete="username"
                    class="form-control @error('server_username') is-invalid @enderror"
                    value="{{ old('server_username', $envValue('DB_USERNAME', 'root')) }}">
                  @error('server_username')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="server_password">كلمة المرور</label>
                  <input id="server_password" name="server_password" type="password" autocomplete="new-password"
                    class="form-control @error('server_password') is-invalid @enderror">
                  @error('server_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12">
                  <label class="form-label" for="database_name">اسم القاعدة الجديدة</label>
                  <input id="database_name" name="database_name" type="text" autocomplete="off"
                    class="form-control @error('database_name') is-invalid @enderror"
                    value="{{ old('database_name') }}" placeholder="legal_system" required>
                  @error('database_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="charset">Charset</label>
                  <input id="charset" name="charset" type="text"
                    class="form-control @error('charset') is-invalid @enderror"
                    value="{{ old('charset', 'utf8mb4') }}" required>
                  @error('charset')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="collation">Collation</label>
                  <input id="collation" name="collation" type="text"
                    class="form-control @error('collation') is-invalid @enderror"
                    value="{{ old('collation', 'utf8mb4_unicode_ci') }}" required>
                  @error('collation')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 d-flex flex-column gap-2">
                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="save_as_active" value="1" checked>
                    <span class="form-check-label">اعتمادها كقاعدة التطبيق الحالية</span>
                  </label>
                  @error('save_as_active')
                    <div class="text-danger small">{{ $message }}</div>
                  @enderror

                  <label class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="run_migrations" value="1" checked>
                    <span class="form-check-label">تشغيل جداول النظام بعد الإنشاء</span>
                  </label>
                  @error('run_migrations')
                    <div class="text-danger small">{{ $message }}</div>
                  @enderror

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

    {{-- ── Backups table + Restore from file ────────────────────────────── --}}
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
              <input type="text" name="backup_label" class="form-control form-control-sm"
                placeholder="اسم اختياري">
              <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                <i class="icon-base ti tabler-database-export me-1"></i>
                إنشاء نسخة
              </button>
            </form>
          </div>

          @error('backup')
            <div class="mx-3 mt-3">
              <div class="alert alert-danger py-2 mb-0">{{ $message }}</div>
            </div>
          @enderror

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
                          {{-- Pre-filled confirmation for one-click restore from the table --}}
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
            data-swal-text="سيتم أخذ نسخة احتياطية من قاعدة البيانات الحالية قبل الاسترجاع." data-swal-icon="warning"
            data-swal-confirm-button="نعم، استرجع" data-swal-cancel-button="إلغاء">
            @csrf

            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="uploaded_backup">ملف النسخة الاحتياطية</label>
                <input id="uploaded_backup" name="uploaded_backup" type="file"
                  class="form-control @error('uploaded_backup') is-invalid @enderror" accept=".sql,.sqlite,.db"
                  required>
                @error('uploaded_backup')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label class="form-label" for="confirm_restore_upload">كلمة التأكيد</label>
                <input id="confirm_restore_upload" name="confirm_restore" type="text"
                  class="form-control @error('confirm_restore') is-invalid @enderror" placeholder="اكتب: استرجاع"
                  required>
                @error('confirm_restore')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              @error('restore')
                <div class="alert alert-danger py-2 mt-3 mb-0">{{ $message }}</div>
              @enderror
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

    {{-- ── Fresh database + Maintenance actions ────────────────────────── --}}
    <div class="row g-4">
      <div class="col-xl-5">
        <div class="card h-100 border-danger">
          <div class="card-header">
            <h5 class="card-title mb-1 text-danger">تهيئة قاعدة البيانات الحالية</h5>
            <p class="card-subtitle mb-0">هذه العملية تحذف الجداول الحالية، وتنشئ نسخة احتياطية قبل الحذف.</p>
          </div>

          <form method="POST" action="{{ route('system-settings.database.fresh') }}" data-swal-confirm="true"
            data-swal-title="تأكيد تهيئة قاعدة البيانات"
            data-swal-text="سيتم حذف كل الجداول الحالية بعد أخذ نسخة احتياطية تلقائية." data-swal-icon="warning"
            data-swal-confirm-button="نعم، هيّئ القاعدة" data-swal-cancel-button="إلغاء">
            @csrf

            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="confirm_fresh">كلمة التأكيد</label>
                <input id="confirm_fresh" name="confirm_fresh" type="text"
                  class="form-control @error('confirm_fresh') is-invalid @enderror" placeholder="اكتب: تهيئة" required>
                @error('confirm_fresh')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <label class="form-check mb-0">
                <input type="checkbox" class="form-check-input" name="seed_after_fresh" value="1">
                <span class="form-check-label">إضافة البيانات التجريبية بعد التهيئة</span>
              </label>

              @error('database')
                <div class="alert alert-danger py-2 mt-3 mb-0">{{ $message }}</div>
              @enderror
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
            @error('maintenance')
              <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
            @enderror

            <div class="row g-3">
              @foreach ($maintenanceActions as $key => $action)
                <div class="col-md-6">
                  {{--
                    BUG FIX: 'requires_confirm' is now a data-driven flag on each
                    action definition in the controller, instead of a hardcoded
                    in_array() check duplicated in the blade.
                  --}}
                  <form method="POST" action="{{ route('system-settings.maintenance.run') }}"
                    @if ($action['requires_confirm']) data-swal-confirm="true"
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
