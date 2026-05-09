<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\Process\Process;
use Throwable;

class SystemSettingsController extends Controller
{
    private const BACKUP_DIRECTORY = 'app/private/database-backups';

    // -------------------------------------------------------------------------
    // Public Actions
    // -------------------------------------------------------------------------

    public function index(): View
    {
        return view('content.settings.index', [
            'environment'        => $this->readEnvironmentFile(),
            'database'           => $this->databaseSnapshot(),
            'backups'            => $this->backupFiles(),
            'system'             => $this->systemSnapshot(),
            'maintenanceActions' => $this->maintenanceActions(),
            // Moved out of the Blade @php block — belongs in the controller
            'timezones'          => \DateTimeZone::listIdentifiers(),
            'localeOptions'      => ['ar' => 'العربية', 'en' => 'English'],
            'environmentOptions' => [
                'local'      => 'Local',
                'production' => 'Production',
                'staging'    => 'Staging',
                'testing'    => 'Testing',
            ],
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name'             => ['required', 'string', 'max:120'],
            'app_url'              => ['required', 'url', 'max:255'],
            'app_env'              => ['required', Rule::in(['local', 'production', 'staging', 'testing'])],
            'app_locale'           => ['required', 'string', 'max:10'],
            'app_fallback_locale'  => ['required', 'string', 'max:10'],
            'app_faker_locale'     => ['nullable', 'string', 'max:20'],
            'app_timezone'         => ['required', 'timezone'],
            'app_debug'            => ['nullable', 'boolean'],
        ]);

        $this->writeEnvironmentValues([
            'APP_NAME'            => $validated['app_name'],
            'VITE_APP_NAME'       => $validated['app_name'],
            'APP_URL'             => $validated['app_url'],
            'APP_ENV'             => $validated['app_env'],
            'APP_DEBUG'           => $request->boolean('app_debug'),
            'APP_LOCALE'          => $validated['app_locale'],
            'APP_FALLBACK_LOCALE' => $validated['app_fallback_locale'],
            'APP_FAKER_LOCALE'    => $validated['app_faker_locale'] ?: 'en_US',
            'APP_TIMEZONE'        => $validated['app_timezone'],
        ]);

        $this->clearConfigurationCache();

        return back()->with('success', 'تم حفظ إعدادات التطبيق العامة.');
    }

    public function updateServices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_driver'     => ['required', Rule::in(['file', 'database', 'redis', 'cookie', 'array'])],
            'session_lifetime'   => ['required', 'integer', 'min:5', 'max:43200'],
            'cache_store'        => ['required', Rule::in(['file', 'database', 'redis', 'array'])],
            'queue_connection'   => ['required', Rule::in(['sync', 'database', 'redis'])],
            'filesystem_disk'    => ['required', Rule::in(['local', 'public'])],
            'mail_mailer'        => ['required', Rule::in(['log', 'smtp', 'sendmail', 'array'])],
            'mail_host'          => ['nullable', 'string', 'max:255'],
            'mail_port'          => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username'      => ['nullable', 'string', 'max:255'],
            'mail_password'      => ['nullable', 'string', 'max:255'],
            'mail_from_address'  => ['required', 'email', 'max:255'],
            'mail_from_name'     => ['required', 'string', 'max:120'],
            'clear_mail_password'=> ['nullable', 'boolean'],
        ]);

        $values = [
            'SESSION_DRIVER'    => $validated['session_driver'],
            'SESSION_LIFETIME'  => $validated['session_lifetime'],
            'CACHE_STORE'       => $validated['cache_store'],
            'QUEUE_CONNECTION'  => $validated['queue_connection'],
            'FILESYSTEM_DISK'   => $validated['filesystem_disk'],
            'MAIL_MAILER'       => $validated['mail_mailer'],
            'MAIL_HOST'         => $validated['mail_host'] ?: '127.0.0.1',
            'MAIL_PORT'         => $validated['mail_port'] ?: 2525,
            'MAIL_USERNAME'     => $validated['mail_username'] ?: null,
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
            'MAIL_FROM_NAME'    => $validated['mail_from_name'],
        ];

        if ($request->boolean('clear_mail_password')) {
            $values['MAIL_PASSWORD'] = null;
        } elseif ($request->filled('mail_password')) {
            $values['MAIL_PASSWORD'] = $validated['mail_password'];
        }

        $this->writeEnvironmentValues($values);
        $this->clearConfigurationCache();

        return back()->with('success', 'تم حفظ إعدادات الخدمات.');
    }

    public function updateDatabase(Request $request): RedirectResponse
    {
        $environment = $this->readEnvironmentFile();

        $validated = $request->validate([
            'db_connection'          => ['required', Rule::in(['mysql', 'mariadb', 'sqlite'])],
            'db_host'                => ['nullable', 'string', 'max:255'],
            'db_port'                => ['nullable', 'integer', 'min:1', 'max:65535'],
            'db_database'            => ['required', 'string', 'max:255'],
            'db_username'            => ['nullable', 'string', 'max:255'],
            'db_password'            => ['nullable', 'string', 'max:255'],
            'db_charset'             => ['nullable', 'string', 'max:40'],
            'db_collation'           => ['nullable', 'string', 'max:80'],
            'mysql_binary_path'      => ['nullable', 'string', 'max:500'],
            'mysqldump_binary_path'  => ['nullable', 'string', 'max:500'],
            'clear_db_password'      => ['nullable', 'boolean'],
            'test_connection'        => ['nullable', 'boolean'],
        ]);

        $dbPassword = $environment['DB_PASSWORD'] ?? null;

        if ($request->boolean('clear_db_password')) {
            $dbPassword = '';
        } elseif ($request->filled('db_password')) {
            $dbPassword = $validated['db_password'];
        }

        try {
            if ($request->boolean('test_connection')) {
                $this->testDatabaseConnection([
                    'connection' => $validated['db_connection'],
                    'host'       => $validated['db_host'] ?: '127.0.0.1',
                    'port'       => (int) ($validated['db_port'] ?: 3306),
                    'database'   => $validated['db_database'],
                    'username'   => $validated['db_username'] ?? '',
                    'password'   => $dbPassword ?? '',
                    'charset'    => $validated['db_charset'] ?: 'utf8mb4',
                ]);
            }

            $this->writeEnvironmentValues([
                'DB_CONNECTION'          => $validated['db_connection'],
                'DB_HOST'                => $validated['db_host'] ?: '127.0.0.1',
                'DB_PORT'                => $validated['db_port'] ?: 3306,
                'DB_DATABASE'            => $validated['db_database'],
                'DB_USERNAME'            => $validated['db_username'] ?: null,
                'DB_PASSWORD'            => $dbPassword ?? '',
                'DB_CHARSET'             => $validated['db_charset'] ?: 'utf8mb4',
                'DB_COLLATION'           => $validated['db_collation'] ?: 'utf8mb4_unicode_ci',
                'SYSTEM_MYSQL_PATH'      => $validated['mysql_binary_path'] ?: null,
                'SYSTEM_MYSQLDUMP_PATH'  => $validated['mysqldump_binary_path'] ?: null,
            ]);

            $this->clearConfigurationCache();
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['database' => $exception->getMessage()]);
        }

        return back()->with('success', 'تم حفظ إعدادات الاتصال بقاعدة البيانات.');
    }

    public function createDatabase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_host'     => ['required', 'string', 'max:255'],
            'server_port'     => ['required', 'integer', 'min:1', 'max:65535'],
            'server_username' => ['nullable', 'string', 'max:255'],
            'server_password' => ['nullable', 'string', 'max:255'],
            'database_name'   => ['required', 'regex:/^[A-Za-z0-9_]+$/', 'max:64'],
            'charset'         => ['required', 'regex:/^[A-Za-z0-9_]+$/', 'max:40'],
            'collation'       => ['required', 'regex:/^[A-Za-z0-9_]+$/', 'max:80'],
            'save_as_active'  => ['nullable', 'boolean'],
            'run_migrations'  => ['nullable', 'boolean'],
            'run_seeders'     => ['nullable', 'boolean'],
        ], [
            'database_name.regex' => 'اسم قاعدة البيانات يجب أن يحتوي على حروف وأرقام وشرطة سفلية فقط.',
        ]);

        if (($request->boolean('run_migrations') || $request->boolean('run_seeders'))
            && ! $request->boolean('save_as_active')) {
            return back()->withErrors([
                'save_as_active' => 'تشغيل الجداول أو البيانات التجريبية يحتاج اعتماد القاعدة الجديدة كقاعدة التطبيق الحالية.',
            ]);
        }

        if ($request->boolean('run_seeders') && ! $request->boolean('run_migrations')) {
            return back()->withErrors([
                'run_migrations' => 'إضافة البيانات التجريبية تحتاج تشغيل جداول النظام أولًا.',
            ]);
        }

        try {
            $pdo = $this->makeServerPdo(
                host:     $validated['server_host'],
                port:     (int) $validated['server_port'],
                username: $validated['server_username'] ?? '',
                password: $validated['server_password'] ?? '',
                charset:  $validated['charset'],
            );

            $databaseName = $validated['database_name'];
            $charset      = $validated['charset'];
            $collation    = $validated['collation'];

            // Use backtick-doubling instead of raw interpolation to prevent injection
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                str_replace('`', '``', $databaseName),
                $charset,
                $collation,
            ));

            if ($request->boolean('save_as_active')) {
                $this->writeEnvironmentValues([
                    'DB_CONNECTION' => 'mysql',
                    'DB_HOST'       => $validated['server_host'],
                    'DB_PORT'       => $validated['server_port'],
                    'DB_DATABASE'   => $databaseName,
                    'DB_USERNAME'   => $validated['server_username'] ?: null,
                    'DB_PASSWORD'   => $validated['server_password'] ?? '',
                    'DB_CHARSET'    => $charset,
                    'DB_COLLATION'  => $collation,
                ]);

                $this->applyRuntimeDatabaseConfig([
                    'driver'    => 'mysql',
                    'host'      => $validated['server_host'],
                    'port'      => (int) $validated['server_port'],
                    'database'  => $databaseName,
                    'username'  => $validated['server_username'] ?? '',
                    'password'  => $validated['server_password'] ?? '',
                    'charset'   => $charset,
                    'collation' => $collation,
                ]);
            }

            if ($request->boolean('run_migrations')) {
                Artisan::call('migrate', ['--force' => true]);
            }

            if ($request->boolean('run_seeders')) {
                Artisan::call('db:seed', ['--force' => true]);
            }

            $this->clearConfigurationCache();

            Log::info('Database created', ['database' => $databaseName]);
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['database_name' => $exception->getMessage()]);
        }

        return back()->with('success', 'تم إنشاء قاعدة البيانات الجديدة بنجاح.');
    }

    public function backup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_label' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $path = $this->createDatabaseBackup($validated['backup_label'] ?? 'manual');
        } catch (Throwable $exception) {
            return back()->withErrors(['backup' => $exception->getMessage()]);
        }

        return back()
            ->with('success', 'تم إنشاء نسخة احتياطية بنجاح.')
            ->with('success_detail', basename($path));
    }

    public function restore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected_backup' => ['nullable', 'string', 'max:255'],
            'uploaded_backup' => ['nullable', 'file', 'max:512000'],
            'confirm_restore' => ['required', 'in:استرجاع'],
        ], [
            'confirm_restore.in' => 'اكتب كلمة "استرجاع" لتأكيد العملية.',
        ]);

        $restorePath = null;

        if ($request->hasFile('uploaded_backup')) {
            $uploadedFile = $request->file('uploaded_backup');
            $extension    = strtolower($uploadedFile->getClientOriginalExtension());

            if (! in_array($extension, ['sql', 'sqlite', 'db'], true)) {
                return back()->withErrors(['uploaded_backup' => 'صيغة ملف النسخة الاحتياطية يجب أن تكون sql أو sqlite أو db.']);
            }

            $filename = 'uploaded-'.now()->format('Ymd-His').'-'.$this->sanitizeFilename($uploadedFile->getClientOriginalName());
            $uploadedFile->move($this->backupDirectory(), $filename);
            $restorePath = $this->backupDirectory().DIRECTORY_SEPARATOR.$filename;
        } elseif (! empty($validated['selected_backup'])) {
            $restorePath = $this->resolveBackupPath($validated['selected_backup']);
        }

        if (! $restorePath || ! File::exists($restorePath)) {
            return back()->withErrors(['selected_backup' => 'اختر نسخة احتياطية صالحة للاسترجاع.']);
        }

        try {
            $this->createDatabaseBackup('before-restore');
            $this->restoreDatabaseBackup($restorePath);
            $this->clearConfigurationCache();

            Log::warning('Database restored from backup', ['file' => basename($restorePath)]);
        } catch (Throwable $exception) {
            return back()->withErrors(['restore' => $exception->getMessage()]);
        }

        return back()->with('success', 'تم استرجاع النسخة الاحتياطية بنجاح.');
    }

    public function freshDatabase(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_fresh'  => ['required', 'in:تهيئة'],
            'seed_after_fresh'=> ['nullable', 'boolean'],
        ], [
            'confirm_fresh.in' => 'اكتب كلمة "تهيئة" لتأكيد إنشاء قاعدة فارغة.',
        ]);

        try {
            $this->createDatabaseBackup('before-fresh');

            Artisan::call('migrate:fresh', ['--force' => true]);

            if ($request->boolean('seed_after_fresh')) {
                Artisan::call('db:seed', ['--force' => true]);
            }

            $this->clearConfigurationCache();

            Log::warning('Database wiped with migrate:fresh', ['seeded' => $request->boolean('seed_after_fresh')]);
        } catch (Throwable $exception) {
            return back()->withErrors(['database' => $exception->getMessage()]);
        }

        return back()->with('success', 'تم تهيئة قاعدة البيانات من جديد.');
    }

    public function downloadBackup(string $file): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = $this->resolveBackupPath($file);

        return Response::download($path);
    }

    public function deleteBackup(string $file): RedirectResponse
    {
        $path = $this->resolveBackupPath($file);
        File::delete($path);

        Log::info('Backup deleted', ['file' => $file]);

        return back()->with('success', 'تم حذف النسخة الاحتياطية.');
    }

    public function runMaintenance(Request $request): RedirectResponse
    {
        // Cache the actions array — avoid building it twice (validation + retrieval)
        $actions = $this->maintenanceActions();

        $validated = $request->validate([
            'action' => ['required', Rule::in(array_keys($actions))],
        ]);

        $action = $actions[$validated['action']];

        try {
            Artisan::call($action['command'], $action['parameters']);

            Log::info('Maintenance action executed', ['action' => $validated['action']]);
        } catch (Throwable $exception) {
            return back()->withErrors(['maintenance' => $exception->getMessage()]);
        }

        return back()->with('success', $action['success']);
    }

    // -------------------------------------------------------------------------
    // Data Snapshot Methods
    // -------------------------------------------------------------------------

    protected function databaseSnapshot(): array
    {
        $connectionName = config('database.default');
        $driver         = config("database.connections.{$connectionName}.driver");

        $snapshot = [
            'ok'         => false,
            'connection' => $connectionName,
            'driver'     => $driver,
            'database'   => config("database.connections.{$connectionName}.database"),
            'host'       => config("database.connections.{$connectionName}.host"),
            'port'       => config("database.connections.{$connectionName}.port"),
            'version'    => null,
            'tables'     => null,
            'size'       => null,
            'error'      => null,
        ];

        try {
            DB::connection()->getPdo();
            $snapshot['ok'] = true;

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                // BUG FIX: use nullsafe ?-> instead of ->prop ?? null
                // The old pattern `DB::selectOne(...)->version ?? null` throws
                // a fatal Error if selectOne returns null, because ?? does not
                // guard property access on a null object — only nullsafe ?-> does.
                $snapshot['version'] = DB::selectOne('select version() as version')?->version;
                $snapshot['tables']  = count(DB::select('show tables'));

                $size = DB::selectOne(
                    'select round(sum(data_length + index_length) / 1024 / 1024, 2) as size_mb
                     from information_schema.tables
                     where table_schema = ?',
                    [$snapshot['database']],
                );

                $snapshot['size'] = $size?->size_mb ? $size->size_mb.' MB' : null;

            } elseif ($driver === 'sqlite') {
                // BUG FIX: same nullsafe fix applied here
                $snapshot['version'] = DB::selectOne('select sqlite_version() as version')?->version;
                $snapshot['tables']  = count(DB::select("select name from sqlite_master where type = 'table'"));
                $snapshot['size']    = File::exists($snapshot['database'])
                    ? $this->formatBytes(File::size($snapshot['database']))
                    : null;
            }
        } catch (Throwable $exception) {
            $snapshot['error'] = $exception->getMessage();
        }

        return $snapshot;
    }

    protected function systemSnapshot(): array
    {
        $root = base_path();

        // BUG FIX: removed @ error-suppression operator; instead check for false
        // explicitly. The @ operator hides legitimate errors and makes debugging
        // impossible without changing the return value behavior.
        $freeBytes  = disk_free_space($root);
        $totalBytes = disk_total_space($root);

        return [
            'app_name'     => config('app.name'),
            'environment'  => config('app.env'),
            'debug'        => config('app.debug'),
            'url'          => config('app.url'),
            'locale'       => config('app.locale'),
            'timezone'     => config('app.timezone'),
            'laravel'      => app()->version(),
            'php'          => PHP_VERSION,
            'os'           => PHP_OS_FAMILY,
            'storage_free' => $freeBytes !== false ? $this->formatBytes($freeBytes) : 'غير متاح',
            'storage_total'=> $totalBytes !== false ? $this->formatBytes($totalBytes) : 'غير متاح',
        ];
    }

    protected function backupFiles(): array
    {
        File::ensureDirectoryExists($this->backupDirectory());

        return collect(File::files($this->backupDirectory()))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['sql', 'sqlite', 'db'], true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name'       => $file->getFilename(),
                'size'       => $this->formatBytes($file->getSize()),
                'created_at' => date('Y-m-d H:i', $file->getMTime()),
                'extension'  => strtolower($file->getExtension()),
            ])
            ->values()
            ->all();
    }

    protected function maintenanceActions(): array
    {
        return [
            'optimize_clear' => [
                'label'           => 'تنظيف الكاش',
                'icon'            => 'tabler-refresh',
                'command'         => 'optimize:clear',
                'parameters'      => [],
                'success'         => 'تم تنظيف كاش التطبيق.',
                'requires_confirm'=> false,
            ],
            'config_cache' => [
                'label'           => 'إعادة بناء إعدادات Laravel',
                'icon'            => 'tabler-adjustments-cog',
                'command'         => 'config:cache',
                'parameters'      => [],
                'success'         => 'تم إعادة بناء كاش الإعدادات.',
                'requires_confirm'=> false,
            ],
            'storage_link' => [
                'label'           => 'ربط مجلد التخزين',
                'icon'            => 'tabler-link',
                'command'         => 'storage:link',
                'parameters'      => ['--force' => true],
                'success'         => 'تم إنشاء رابط التخزين.',
                'requires_confirm'=> false,
            ],
            'migrate' => [
                'label'           => 'تشغيل التحديثات',
                'icon'            => 'tabler-database-up',
                'command'         => 'migrate',
                'parameters'      => ['--force' => true],
                'success'         => 'تم تشغيل تحديثات قاعدة البيانات.',
                'requires_confirm'=> true,  // moved from hardcoded blade in_array check
            ],
            'key_generate' => [
                'label'           => 'توليد مفتاح التطبيق',
                'icon'            => 'tabler-key',
                'command'         => 'key:generate',
                'parameters'      => ['--force' => true],
                'success'         => 'تم توليد مفتاح التطبيق.',
                'requires_confirm'=> true,  // moved from hardcoded blade in_array check
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Backup & Restore
    // -------------------------------------------------------------------------

    protected function createDatabaseBackup(?string $label = null): string
    {
        File::ensureDirectoryExists($this->backupDirectory());

        $connectionName = config('database.default');
        $connection     = config("database.connections.{$connectionName}");
        $driver         = $connection['driver'] ?? $connectionName;
        $timestamp      = now()->format('Ymd-His');
        $safeLabel      = $this->sanitizeFilename($label ?: 'backup');

        if ($driver === 'sqlite') {
            $databasePath = $connection['database'] ?? null;

            if (! $databasePath || ! File::exists($databasePath)) {
                throw new \RuntimeException('ملف قاعدة بيانات SQLite غير موجود.');
            }

            $path = $this->backupDirectory().DIRECTORY_SEPARATOR."{$timestamp}-{$safeLabel}.sqlite";
            File::copy($databasePath, $path);

            return $path;
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('النسخ الاحتياطي متاح حاليًا لقواعد MySQL/MariaDB وSQLite فقط.');
        }

        $database = $connection['database'] ?? null;

        if (! $database) {
            throw new \RuntimeException('اسم قاعدة البيانات غير محدد.');
        }

        $path   = $this->backupDirectory().DIRECTORY_SEPARATOR."{$timestamp}-{$safeLabel}-{$database}.sql";
        $handle = fopen($path, 'wb');

        if (! $handle) {
            throw new \RuntimeException('تعذر إنشاء ملف النسخة الاحتياطية.');
        }

        // Read env once here and pass it down to avoid redundant I/O in databaseBinary()
        $environment = $this->readEnvironmentFile();

        $command = array_merge([
            $this->databaseBinary('dump', $environment),
            '--user='.($connection['username'] ?? ''),
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
            '--single-transaction',
            '--routines',
            '--triggers',
            '--add-drop-table',
            '--no-tablespaces',
        ], $this->mysqlConnectionArguments($connection), [
            $database,
        ]);

        $errors  = '';
        $process = new Process(
            $command,
            base_path(),
            $this->mysqlProcessEnvironment($connection),
            null,
            600,
        );

        $process->run(function (string $type, string $buffer) use ($handle, &$errors): void {
            if ($type === Process::OUT) {
                fwrite($handle, $buffer);

                return;
            }

            $errors .= $buffer;
        });

        fclose($handle);

        if (! $process->isSuccessful()) {
            File::delete($path);
            throw new \RuntimeException(trim($errors) ?: 'فشل إنشاء النسخة الاحتياطية.');
        }

        return $path;
    }

    protected function restoreDatabaseBackup(string $path): void
    {
        $connectionName = config('database.default');
        $connection     = config("database.connections.{$connectionName}");
        $driver         = $connection['driver'] ?? $connectionName;
        $extension      = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($driver === 'sqlite') {
            if (! in_array($extension, ['sqlite', 'db'], true)) {
                throw new \RuntimeException('استرجاع SQLite يحتاج ملف sqlite أو db.');
            }

            $databasePath = $connection['database'] ?? null;

            if (! $databasePath) {
                throw new \RuntimeException('مسار قاعدة بيانات SQLite غير محدد.');
            }

            File::copy($path, $databasePath);

            return;
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('الاسترجاع متاح حاليًا لقواعد MySQL/MariaDB وSQLite فقط.');
        }

        if ($extension !== 'sql') {
            throw new \RuntimeException('استرجاع MySQL/MariaDB يحتاج ملف SQL.');
        }

        $input = fopen($path, 'rb');

        if (! $input) {
            throw new \RuntimeException('تعذر قراءة ملف النسخة الاحتياطية.');
        }

        // Read env once and pass down to databaseBinary() to avoid redundant reads
        $environment = $this->readEnvironmentFile();

        $command = array_merge([
            $this->databaseBinary('mysql', $environment),
            '--user='.($connection['username'] ?? ''),
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
        ], $this->mysqlConnectionArguments($connection), [
            $connection['database'] ?? '',
        ]);

        $process = new Process(
            $command,
            base_path(),
            $this->mysqlProcessEnvironment($connection),
            $input,
            600,
        );

        $process->run();
        fclose($input);

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: 'فشل استرجاع قاعدة البيانات.');
        }
    }

    // -------------------------------------------------------------------------
    // Database Helpers
    // -------------------------------------------------------------------------

    protected function testDatabaseConnection(array $settings): void
    {
        if ($settings['connection'] === 'sqlite') {
            $databasePath = $settings['database'];

            if (! File::exists($databasePath)) {
                throw new \RuntimeException('ملف SQLite غير موجود.');
            }

            new \PDO('sqlite:'.$databasePath);

            return;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $settings['host'],
            $settings['port'],
            $settings['database'],
            $settings['charset'],
        );

        new \PDO($dsn, $settings['username'], $settings['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }

    protected function makeServerPdo(
        string $host,
        int $port,
        string $username,
        string $password,
        string $charset,
    ): \PDO {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $host, $port, $charset);

        return new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }

    protected function applyRuntimeDatabaseConfig(array $settings): void
    {
        config([
            'database.default'                          => 'mysql',
            'database.connections.mysql.driver'         => 'mysql',
            'database.connections.mysql.host'           => $settings['host'],
            'database.connections.mysql.port'           => $settings['port'],
            'database.connections.mysql.database'       => $settings['database'],
            'database.connections.mysql.username'       => $settings['username'],
            'database.connections.mysql.password'       => $settings['password'],
            'database.connections.mysql.charset'        => $settings['charset'],
            'database.connections.mysql.collation'      => $settings['collation'],
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    /**
     * Resolve the path to the mysql or mysqldump binary.
     *
     * Accepts an already-loaded environment array to avoid re-reading the file
     * on every call (the original always called readEnvironmentFile() internally,
     * causing redundant disk I/O when called from within backup/restore methods
     * that had already loaded the environment).
     */
    protected function databaseBinary(string $type, array $environment = []): string
    {
        if (empty($environment)) {
            $environment = $this->readEnvironmentFile();
        }

        $configuredPath = $type === 'dump'
            ? ($environment['SYSTEM_MYSQLDUMP_PATH'] ?? null)
            : ($environment['SYSTEM_MYSQL_PATH'] ?? null);

        $defaultName = $type === 'dump' ? 'mysqldump' : 'mysql';

        $candidates = array_filter([
            $configuredPath,
            'C:\\xampp\\mysql\\bin\\'.$defaultName.'.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0\\bin\\'.$defaultName.'.exe',
            'C:\\laragon\\bin\\mysql\\mysql-5.7\\bin\\'.$defaultName.'.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $defaultName;
    }

    protected function mysqlProcessEnvironment(array $connection): array
    {
        $environment = $this->windowsProcessEnvironment();
        $password = $connection['password'] ?? null;

        if ($password !== null && $password !== '') {
            $environment['MYSQL_PWD'] = $password;
        }

        return $environment;
    }

    protected function windowsProcessEnvironment(): array
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return [];
        }

        $systemRoot = $this->firstEnvironmentValue(['SystemRoot', 'SYSTEMROOT', 'windir', 'WINDIR']) ?: 'C:\\Windows';

        return array_filter([
            'SystemRoot'  => $systemRoot,
            'windir'      => $this->firstEnvironmentValue(['windir', 'WINDIR']) ?: $systemRoot,
            'SystemDrive' => $this->firstEnvironmentValue(['SystemDrive', 'SYSTEMDRIVE']) ?: substr($systemRoot, 0, 2),
            'ComSpec'     => $this->firstEnvironmentValue(['ComSpec', 'COMSPEC']) ?: $systemRoot.'\\System32\\cmd.exe',
            'PATH'        => $this->firstEnvironmentValue(['PATH', 'Path']),
            'PATHEXT'     => $this->firstEnvironmentValue(['PATHEXT', 'PathExt']),
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function firstEnvironmentValue(array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = getenv($key);

            if ($value !== false && $value !== '') {
                return $value;
            }

            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
                return (string) $_SERVER[$key];
            }

            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return (string) $_ENV[$key];
            }
        }

        return null;
    }

    protected function mysqlConnectionArguments(array $connection): array
    {
        if (! empty($connection['unix_socket'])) {
            return ['--socket='.$connection['unix_socket']];
        }

        $arguments = ['--host='.($connection['host'] ?? '127.0.0.1')];

        if (! empty($connection['port'])) {
            $arguments[] = '--port='.$connection['port'];
        }

        return $arguments;
    }

    // -------------------------------------------------------------------------
    // .env File Management
    // -------------------------------------------------------------------------

    protected function readEnvironmentFile(): array
    {
        $path = base_path('.env');

        if (! File::exists($path)) {
            return [];
        }

        $values = [];

        foreach (preg_split('/\r\n|\r|\n/', File::get($path)) as $line) {
            if (! preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*?)\s*$/', $line, $matches)) {
                continue;
            }

            $values[$matches[1]] = $this->decodeEnvironmentValue($matches[2]);
        }

        return $values;
    }

    /**
     * Write key-value pairs to the .env file.
     *
     * BUG FIX: the original used File::put() directly, which is neither atomic
     * nor concurrency-safe. A concurrent request could read a half-written file
     * or two writers could interleave their output, corrupting .env.
     *
     * This version uses:
     *   1. An exclusive advisory flock() on a separate lock file so that only
     *      one writer proceeds at a time.
     *   2. A temp-file + rename() to make the final write atomic — readers
     *      always see either the complete old file or the complete new one,
     *      never a partial write.
     */
    protected function writeEnvironmentValues(array $values): void
    {
        $path     = base_path('.env');
        $lockPath = $path.'.lock';

        $lock = fopen($lockPath, 'c');

        if (! $lock) {
            throw new \RuntimeException('تعذر الحصول على قفل ملف الإعدادات.');
        }

        try {
            flock($lock, LOCK_EX);

            $content = File::exists($path) ? File::get($path) : '';

            foreach ($values as $key => $value) {
                $encodedValue = $this->encodeEnvironmentValue($value);
                $line         = $key.'='.$encodedValue;

                if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $content)) {
                    $content = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $content);

                    continue;
                }

                $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            }

            // Atomic write: write to a temp file first, then rename (POSIX-atomic)
            $tmpPath = $path.'.'.uniqid('env_', true).'.tmp';
            File::put($tmpPath, $content);
            rename($tmpPath, $path);

        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    protected function decodeEnvironmentValue(string $value): ?string
    {
        $value = trim($value);

        if ($value === 'null') {
            return null;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return stripcslashes(substr($value, 1, -1));
        }

        return $value;
    }

    protected function encodeEnvironmentValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'|\$/', $value)) {
            return '"'.addcslashes($value, "\\\"\r\n").'"';
        }

        return $value;
    }

    // -------------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------------

    protected function clearConfigurationCache(): void
    {
        Artisan::call('config:clear');
    }

    protected function backupDirectory(): string
    {
        return storage_path(self::BACKUP_DIRECTORY);
    }

    protected function resolveBackupPath(string $file): string
    {
        $basename     = basename($file);
        $path         = $this->backupDirectory().DIRECTORY_SEPARATOR.$basename;
        $realPath     = realpath($path);
        $realDirectory= realpath($this->backupDirectory());

        if (! $realPath || ! $realDirectory || ! str_starts_with($realPath, $realDirectory) || ! File::exists($realPath)) {
            abort(404);
        }

        return $realPath;
    }

    protected function sanitizeFilename(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_.-]+/', '-', $value);
        $value = trim((string) $value, '.-');

        return $value !== '' ? $value : 'backup';
    }

    protected function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, $index === 0 ? 0 : 2).' '.$units[$index];
    }
}
