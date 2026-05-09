<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\Process\Process;
use Throwable;

class SystemSettingsController extends Controller
{
    private const BACKUP_DIRECTORY = 'app/private/database-backups';

    public function index()
    {
        $environment = $this->readEnvironmentFile();
        $database = $this->databaseSnapshot();
        $backups = $this->backupFiles();
        $system = $this->systemSnapshot();
        $maintenanceActions = $this->maintenanceActions();

        return view('content.settings.index', compact(
            'environment',
            'database',
            'backups',
            'system',
            'maintenanceActions'
        ));
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:120'],
            'app_url' => ['required', 'url', 'max:255'],
            'app_env' => ['required', Rule::in(['local', 'production', 'staging', 'testing'])],
            'app_locale' => ['required', 'string', 'max:10'],
            'app_fallback_locale' => ['required', 'string', 'max:10'],
            'app_faker_locale' => ['nullable', 'string', 'max:20'],
            'app_timezone' => ['required', 'timezone'],
            'app_debug' => ['nullable', 'boolean'],
        ]);

        $this->writeEnvironmentValues([
            'APP_NAME' => $validated['app_name'],
            'VITE_APP_NAME' => $validated['app_name'],
            'APP_URL' => $validated['app_url'],
            'APP_ENV' => $validated['app_env'],
            'APP_DEBUG' => $request->boolean('app_debug'),
            'APP_LOCALE' => $validated['app_locale'],
            'APP_FALLBACK_LOCALE' => $validated['app_fallback_locale'],
            'APP_FAKER_LOCALE' => $validated['app_faker_locale'] ?: 'en_US',
            'APP_TIMEZONE' => $validated['app_timezone'],
        ]);

        $this->clearConfigurationCache();

        return back()->with('success', 'تم حفظ إعدادات التطبيق العامة.');
    }

    public function updateServices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_driver' => ['required', Rule::in(['file', 'database', 'redis', 'cookie', 'array'])],
            'session_lifetime' => ['required', 'integer', 'min:5', 'max:43200'],
            'cache_store' => ['required', Rule::in(['file', 'database', 'redis', 'array'])],
            'queue_connection' => ['required', Rule::in(['sync', 'database', 'redis'])],
            'filesystem_disk' => ['required', Rule::in(['local', 'public'])],
            'mail_mailer' => ['required', Rule::in(['log', 'smtp', 'sendmail', 'array'])],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:120'],
            'clear_mail_password' => ['nullable', 'boolean'],
        ]);

        $values = [
            'SESSION_DRIVER' => $validated['session_driver'],
            'SESSION_LIFETIME' => $validated['session_lifetime'],
            'CACHE_STORE' => $validated['cache_store'],
            'QUEUE_CONNECTION' => $validated['queue_connection'],
            'FILESYSTEM_DISK' => $validated['filesystem_disk'],
            'MAIL_MAILER' => $validated['mail_mailer'],
            'MAIL_HOST' => $validated['mail_host'] ?: '127.0.0.1',
            'MAIL_PORT' => $validated['mail_port'] ?: 2525,
            'MAIL_USERNAME' => $validated['mail_username'] ?: null,
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
            'MAIL_FROM_NAME' => $validated['mail_from_name'],
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
            'db_connection' => ['required', Rule::in(['mysql', 'mariadb', 'sqlite'])],
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'db_charset' => ['nullable', 'string', 'max:40'],
            'db_collation' => ['nullable', 'string', 'max:80'],
            'mysql_binary_path' => ['nullable', 'string', 'max:500'],
            'mysqldump_binary_path' => ['nullable', 'string', 'max:500'],
            'clear_db_password' => ['nullable', 'boolean'],
            'test_connection' => ['nullable', 'boolean'],
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
                    'host' => $validated['db_host'] ?: '127.0.0.1',
                    'port' => (int) ($validated['db_port'] ?: 3306),
                    'database' => $validated['db_database'],
                    'username' => $validated['db_username'] ?? '',
                    'password' => $dbPassword ?? '',
                    'charset' => $validated['db_charset'] ?: 'utf8mb4',
                ]);
            }

            $this->writeEnvironmentValues([
                'DB_CONNECTION' => $validated['db_connection'],
                'DB_HOST' => $validated['db_host'] ?: '127.0.0.1',
                'DB_PORT' => $validated['db_port'] ?: 3306,
                'DB_DATABASE' => $validated['db_database'],
                'DB_USERNAME' => $validated['db_username'] ?: null,
                'DB_PASSWORD' => $dbPassword ?? '',
                'DB_CHARSET' => $validated['db_charset'] ?: 'utf8mb4',
                'DB_COLLATION' => $validated['db_collation'] ?: 'utf8mb4_unicode_ci',
                'SYSTEM_MYSQL_PATH' => $validated['mysql_binary_path'] ?: null,
                'SYSTEM_MYSQLDUMP_PATH' => $validated['mysqldump_binary_path'] ?: null,
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
            'server_host' => ['required', 'string', 'max:255'],
            'server_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'server_username' => ['nullable', 'string', 'max:255'],
            'server_password' => ['nullable', 'string', 'max:255'],
            'database_name' => ['required', 'regex:/^[A-Za-z0-9_]+$/', 'max:64'],
            'charset' => ['required', 'regex:/^[A-Za-z0-9_]+$/', 'max:40'],
            'collation' => ['required', 'regex:/^[A-Za-z0-9_]+$/', 'max:80'],
            'save_as_active' => ['nullable', 'boolean'],
            'run_migrations' => ['nullable', 'boolean'],
            'run_seeders' => ['nullable', 'boolean'],
        ], [
            'database_name.regex' => 'اسم قاعدة البيانات يجب أن يحتوي على حروف وأرقام وشرطة سفلية فقط.',
        ]);

        if (($request->boolean('run_migrations') || $request->boolean('run_seeders')) && ! $request->boolean('save_as_active')) {
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
                host: $validated['server_host'],
                port: (int) $validated['server_port'],
                username: $validated['server_username'] ?? '',
                password: $validated['server_password'] ?? '',
                charset: $validated['charset']
            );

            $databaseName = $validated['database_name'];
            $charset = $validated['charset'];
            $collation = $validated['collation'];

            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                str_replace('`', '``', $databaseName),
                $charset,
                $collation
            ));

            if ($request->boolean('save_as_active')) {
                $this->writeEnvironmentValues([
                    'DB_CONNECTION' => 'mysql',
                    'DB_HOST' => $validated['server_host'],
                    'DB_PORT' => $validated['server_port'],
                    'DB_DATABASE' => $databaseName,
                    'DB_USERNAME' => $validated['server_username'] ?: null,
                    'DB_PASSWORD' => $validated['server_password'] ?? '',
                    'DB_CHARSET' => $charset,
                    'DB_COLLATION' => $collation,
                ]);

                $this->applyRuntimeDatabaseConfig([
                    'driver' => 'mysql',
                    'host' => $validated['server_host'],
                    'port' => (int) $validated['server_port'],
                    'database' => $databaseName,
                    'username' => $validated['server_username'] ?? '',
                    'password' => $validated['server_password'] ?? '',
                    'charset' => $charset,
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

        return back()->with('success', 'تم إنشاء نسخة احتياطية: '.basename($path));
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
            $extension = strtolower($uploadedFile->getClientOriginalExtension());

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
        } catch (Throwable $exception) {
            return back()->withErrors(['restore' => $exception->getMessage()]);
        }

        return back()->with('success', 'تم استرجاع النسخة الاحتياطية بنجاح.');
    }

    public function freshDatabase(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_fresh' => ['required', 'in:تهيئة'],
            'seed_after_fresh' => ['nullable', 'boolean'],
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
        } catch (Throwable $exception) {
            return back()->withErrors(['database' => $exception->getMessage()]);
        }

        return back()->with('success', 'تم تهيئة قاعدة البيانات من جديد.');
    }

    public function downloadBackup(string $file)
    {
        $path = $this->resolveBackupPath($file);

        return Response::download($path);
    }

    public function deleteBackup(string $file): RedirectResponse
    {
        $path = $this->resolveBackupPath($file);
        File::delete($path);

        return back()->with('success', 'تم حذف النسخة الاحتياطية.');
    }

    public function runMaintenance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(array_keys($this->maintenanceActions()))],
        ]);

        $action = $this->maintenanceActions()[$validated['action']];
        try {
            Artisan::call($action['command'], $action['parameters']);
        } catch (Throwable $exception) {
            return back()->withErrors(['maintenance' => $exception->getMessage()]);
        }

        return back()->with('success', $action['success']);
    }

    protected function databaseSnapshot(): array
    {
        $connectionName = config('database.default');
        $driver = config("database.connections.{$connectionName}.driver");

        $snapshot = [
            'ok' => false,
            'connection' => $connectionName,
            'driver' => $driver,
            'database' => config("database.connections.{$connectionName}.database"),
            'host' => config("database.connections.{$connectionName}.host"),
            'port' => config("database.connections.{$connectionName}.port"),
            'version' => null,
            'tables' => null,
            'size' => null,
            'error' => null,
        ];

        try {
            DB::connection()->getPdo();
            $snapshot['ok'] = true;

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $snapshot['version'] = DB::selectOne('select version() as version')->version ?? null;
                $snapshot['tables'] = count(DB::select('show tables'));

                $size = DB::selectOne(
                    'select round(sum(data_length + index_length) / 1024 / 1024, 2) as size_mb from information_schema.tables where table_schema = ?',
                    [$snapshot['database']]
                );

                $snapshot['size'] = $size?->size_mb ? $size->size_mb.' MB' : null;
            } elseif ($driver === 'sqlite') {
                $snapshot['version'] = DB::selectOne('select sqlite_version() as version')->version ?? null;
                $snapshot['tables'] = count(DB::select("select name from sqlite_master where type = 'table'"));
                $snapshot['size'] = File::exists($snapshot['database'])
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
        $freeBytes = @disk_free_space($root);
        $totalBytes = @disk_total_space($root);

        return [
            'app_name' => config('app.name'),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'url' => config('app.url'),
            'locale' => config('app.locale'),
            'timezone' => config('app.timezone'),
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'os' => PHP_OS_FAMILY,
            'storage_free' => $freeBytes ? $this->formatBytes($freeBytes) : 'غير متاح',
            'storage_total' => $totalBytes ? $this->formatBytes($totalBytes) : 'غير متاح',
        ];
    }

    protected function backupFiles(): array
    {
        File::ensureDirectoryExists($this->backupDirectory());

        return collect(File::files($this->backupDirectory()))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['sql', 'sqlite', 'db'], true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'created_at' => date('Y-m-d H:i', $file->getMTime()),
                'extension' => strtolower($file->getExtension()),
            ])
            ->values()
            ->all();
    }

    protected function maintenanceActions(): array
    {
        return [
            'optimize_clear' => [
                'label' => 'تنظيف الكاش',
                'icon' => 'tabler-refresh',
                'command' => 'optimize:clear',
                'parameters' => [],
                'success' => 'تم تنظيف كاش التطبيق.',
            ],
            'config_cache' => [
                'label' => 'إعادة بناء إعدادات Laravel',
                'icon' => 'tabler-adjustments-cog',
                'command' => 'config:cache',
                'parameters' => [],
                'success' => 'تم إعادة بناء كاش الإعدادات.',
            ],
            'storage_link' => [
                'label' => 'ربط مجلد التخزين',
                'icon' => 'tabler-link',
                'command' => 'storage:link',
                'parameters' => ['--force' => true],
                'success' => 'تم إنشاء رابط التخزين.',
            ],
            'migrate' => [
                'label' => 'تشغيل التحديثات',
                'icon' => 'tabler-database-up',
                'command' => 'migrate',
                'parameters' => ['--force' => true],
                'success' => 'تم تشغيل تحديثات قاعدة البيانات.',
            ],
            'key_generate' => [
                'label' => 'توليد مفتاح التطبيق',
                'icon' => 'tabler-key',
                'command' => 'key:generate',
                'parameters' => ['--force' => true],
                'success' => 'تم توليد مفتاح التطبيق.',
            ],
        ];
    }

    protected function createDatabaseBackup(?string $label = null): string
    {
        File::ensureDirectoryExists($this->backupDirectory());

        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");
        $driver = $connection['driver'] ?? $connectionName;
        $timestamp = now()->format('Ymd-His');
        $safeLabel = $this->sanitizeFilename($label ?: 'backup');

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

        $path = $this->backupDirectory().DIRECTORY_SEPARATOR."{$timestamp}-{$safeLabel}-{$database}.sql";
        $handle = fopen($path, 'wb');

        if (! $handle) {
            throw new \RuntimeException('تعذر إنشاء ملف النسخة الاحتياطية.');
        }

        $command = [
            $this->databaseBinary('dump'),
            '--user='.($connection['username'] ?? ''),
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
            '--single-transaction',
            '--routines',
            '--triggers',
            '--add-drop-table',
            '--no-tablespaces',
        ];

        if (! empty($connection['unix_socket'])) {
            $command[] = '--socket='.$connection['unix_socket'];
        } else {
            $command[] = '--host='.($connection['host'] ?? '127.0.0.1');
            $command[] = '--port='.($connection['port'] ?? 3306);
        }

        $command[] = $database;

        $errors = '';
        $process = new Process($command, base_path(), $this->mysqlPasswordEnvironment($connection), null, 600);
        $process->run(function (string $type, string $buffer) use ($handle, &$errors) {
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
        $connection = config("database.connections.{$connectionName}");
        $driver = $connection['driver'] ?? $connectionName;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

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

        $command = [
            $this->databaseBinary('mysql'),
            '--user='.($connection['username'] ?? ''),
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
        ];

        if (! empty($connection['unix_socket'])) {
            $command[] = '--socket='.$connection['unix_socket'];
        } else {
            $command[] = '--host='.($connection['host'] ?? '127.0.0.1');
            $command[] = '--port='.($connection['port'] ?? 3306);
        }

        $command[] = $connection['database'] ?? '';

        $process = new Process($command, base_path(), $this->mysqlPasswordEnvironment($connection), $input, 600);
        $process->run();
        fclose($input);

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: 'فشل استرجاع قاعدة البيانات.');
        }
    }

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
            $settings['charset']
        );

        new \PDO($dsn, $settings['username'], $settings['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }

    protected function makeServerPdo(string $host, int $port, string $username, string $password, string $charset): \PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $host, $port, $charset);

        return new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }

    protected function applyRuntimeDatabaseConfig(array $settings): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.host' => $settings['host'],
            'database.connections.mysql.port' => $settings['port'],
            'database.connections.mysql.database' => $settings['database'],
            'database.connections.mysql.username' => $settings['username'],
            'database.connections.mysql.password' => $settings['password'],
            'database.connections.mysql.charset' => $settings['charset'],
            'database.connections.mysql.collation' => $settings['collation'],
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    protected function databaseBinary(string $type): string
    {
        $environment = $this->readEnvironmentFile();
        $configuredPath = $type === 'dump'
            ? ($environment['SYSTEM_MYSQLDUMP_PATH'] ?? null)
            : ($environment['SYSTEM_MYSQL_PATH'] ?? null);

        $defaultName = $type === 'dump' ? 'mysqldump' : 'mysql';
        $candidates = array_filter([
            $configuredPath,
            'C:\xampp\mysql\bin\\'.$defaultName.'.exe',
            'C:\laragon\bin\mysql\mysql-8.0\bin\\'.$defaultName.'.exe',
            'C:\laragon\bin\mysql\mysql-5.7\bin\\'.$defaultName.'.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $defaultName;
    }

    protected function mysqlPasswordEnvironment(array $connection): array
    {
        $password = $connection['password'] ?? null;

        return $password !== null && $password !== ''
            ? ['MYSQL_PWD' => $password]
            : [];
    }

    protected function backupDirectory(): string
    {
        return storage_path(self::BACKUP_DIRECTORY);
    }

    protected function resolveBackupPath(string $file): string
    {
        $basename = basename($file);
        $path = $this->backupDirectory().DIRECTORY_SEPARATOR.$basename;
        $realPath = realpath($path);
        $realDirectory = realpath($this->backupDirectory());

        if (! $realPath || ! $realDirectory || ! str_starts_with($realPath, $realDirectory) || ! File::exists($realPath)) {
            abort(404);
        }

        return $realPath;
    }

    protected function readEnvironmentFile(): array
    {
        $path = base_path('.env');

        if (! File::exists($path)) {
            return [];
        }

        $values = [];

        foreach (preg_split('/\r\n|\r|\n/', File::get($path)) as $line) {
            if (! preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/', $line, $matches)) {
                continue;
            }

            $values[$matches[1]] = $this->decodeEnvironmentValue($matches[2]);
        }

        return $values;
    }

    protected function writeEnvironmentValues(array $values): void
    {
        $path = base_path('.env');
        $content = File::exists($path) ? File::get($path) : '';

        foreach ($values as $key => $value) {
            $encodedValue = $this->encodeEnvironmentValue($value);
            $line = $key.'='.$encodedValue;

            if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $content)) {
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $content);

                continue;
            }

            $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
        }

        File::put($path, $content);
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

    protected function clearConfigurationCache(): void
    {
        Artisan::call('config:clear');
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
