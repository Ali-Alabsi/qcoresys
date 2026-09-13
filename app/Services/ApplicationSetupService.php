<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ApplicationSetupService
{
    private static bool $booted = false;

    /**
     * Prepare .env / APP_KEY, then wipe + migrate + seed once on the first HTTP request.
     */
    public function bootstrap(): void
    {
        if (static::$booted || app()->environment('testing')) {
            return;
        }

        static::$booted = true;

        if (! config('setup.auto', true)) {
            return;
        }

        if ($this->setupAlreadyCompleted()) {
            return;
        }

        $this->ensureEnvironment();

        if (app()->runningInConsole()) {
            return;
        }

        $this->install();
    }

    
    /**
     * Run a one-time database reset (migrate:fresh --seed), or a non-destructive
     * migrate + seed when invoked explicitly from the installer command.
     */
    public function install(bool $force = false): void
    {
        $this->ensureEnvironment();
        $this->configureMysqlConnection();
        $this->ensureDatabaseExists();
        $this->reconnectMysql();

        $handle = $this->acquireLock(storage_path('framework/app-setup.lock'));

        try {
            if ($force) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', ['--force' => true]);
                $this->ensureStorageLink();
                $this->markSetupCompleted();

                return;
            }

            if ($this->setupAlreadyCompleted()) {
                return;
            }

            Artisan::call('migrate:fresh', [
                '--force' => true,
                '--seed' => true,
            ]);

            $this->ensureStorageLink();
            $this->markSetupCompleted();
        } catch (QueryException $e) {
            $this->logQueryException($e, 'Application one-time database setup failed.');
            throw $e;
        } catch (Throwable $e) {
            Log::error('Application one-time database setup failed.', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
                'username' => config('database.connections.mysql.username'),
            ]);

            throw $e;
        } finally {
            $this->releaseLock($handle);
        }
    }

    public function adminEmail(): string
    {
        return (string) config('setup.admin.email', 'admin@qcoresys.com');
    }

    public function adminPassword(): string
    {
        return (string) config('setup.admin.password', 'password');
    }

    public function isInstalled(): bool
    {
        if ($this->setupAlreadyCompleted()) {
            return true;
        }

        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('settings')) {
                return false;
            }

            return User::query()->where('email', $this->adminEmail())->exists()
                && Setting::query()->exists();
        } catch (QueryException $e) {
            $this->logQueryException($e, 'Failed to determine whether the application is installed.');

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Re-apply MySQL credentials from env / config / explicit defaults, then drop
     * any stale PDO so shared hosts do not silently fall back to root@localhost.
     */
    private function configureMysqlConnection(): void
    {
        $connection = (string) $this->settingValue(
            'DB_CONNECTION',
            'database.default',
            'mysql'
        );

        config(['database.default' => $connection]);

        if (! in_array($connection, ['mysql', 'mariadb'], true)) {
            return;
        }

        config([
            "database.connections.{$connection}.host" => $this->settingValue(
                'DB_HOST',
                "database.connections.{$connection}.host",
                '127.0.0.1'
            ),
            "database.connections.{$connection}.port" => $this->settingValue(
                'DB_PORT',
                "database.connections.{$connection}.port",
                '3306'
            ),
            "database.connections.{$connection}.database" => $this->settingValue(
                'DB_DATABASE',
                "database.connections.{$connection}.database",
                'qcoressys'
            ),
            "database.connections.{$connection}.username" => $this->settingValue(
                'DB_USERNAME',
                "database.connections.{$connection}.username",
                'root'
            ),
            "database.connections.{$connection}.password" => $this->passwordValue($connection),
            "database.connections.{$connection}.charset" => $this->settingValue(
                'DB_CHARSET',
                "database.connections.{$connection}.charset",
                'utf8mb4'
            ),
            "database.connections.{$connection}.collation" => $this->settingValue(
                'DB_COLLATION',
                "database.connections.{$connection}.collation",
                'utf8mb4_unicode_ci'
            ),
        ]);

        DB::purge('mysql');

        if ($connection === 'mariadb') {
            DB::purge('mariadb');
        }
    }

    private function reconnectMysql(): void
    {
        $connection = (string) config('database.default');

        if (! in_array($connection, ['mysql', 'mariadb'], true)) {
            return;
        }

        try {
            DB::purge('mysql');
            DB::reconnect('mysql');

            if ($connection === 'mariadb') {
                DB::purge('mariadb');
                DB::reconnect('mariadb');
            }
        } catch (QueryException $e) {
            $this->logQueryException($e, 'Failed to reconnect MySQL during application setup.');
            throw $e;
        }
    }

    private function setupAlreadyCompleted(): bool
    {
        return is_file($this->completedLockPath());
    }

    private function markSetupCompleted(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $path = $this->completedLockPath();
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $written = file_put_contents(
            $path,
            'completed_at='.now()->toIso8601String().PHP_EOL,
            LOCK_EX
        );

        if ($written === false) {
            Log::error('Database setup finished but the completion lock could not be written.', [
                'path' => $path,
            ]);
        }
    }

    private function completedLockPath(): string
    {
        return storage_path('framework/setup_completed.lock');
    }

    private function settingValue(string $envKey, string $configKey, mixed $default): mixed
    {
        $fromEnv = env($envKey);

        if ($fromEnv !== null && $fromEnv !== '') {
            return $fromEnv;
        }

        $fromConfig = config($configKey);

        if ($fromConfig !== null && $fromConfig !== '') {
            return $fromConfig;
        }

        return $default;
    }

    private function passwordValue(string $connection): string
    {
        $fromEnv = env('DB_PASSWORD');

        if ($fromEnv !== null) {
            return (string) $fromEnv;
        }

        return (string) config("database.connections.{$connection}.password", '');
    }

    private function logQueryException(QueryException $e, string $message): void
    {
        Log::error($message, [
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'connection' => config('database.default'),
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => config('database.connections.mysql.database'),
            'username' => config('database.connections.mysql.username'),
        ]);
    }

    private function ensureEnvironment(): void
    {
        $envPath = base_path('.env');

        if (! is_file($envPath) && is_file(base_path('.env.example'))) {
            copy(base_path('.env.example'), $envPath);
            $this->reloadDotenv();
        }

        if (filled(config('app.key'))) {
            return;
        }

        $key = 'base64:'.base64_encode(random_bytes(32));
        $this->writeEnvValue('APP_KEY', $key);
        config(['app.key' => $key]);
    }

    private function reloadDotenv(): void
    {
        if (! is_file(base_path('.env'))) {
            return;
        }

        try {
            Dotenv::createMutable(base_path())->load();
        } catch (Throwable) {
            return;
        }

        config([
            'app.name' => env('APP_NAME', config('app.name')),
            'app.key' => env('APP_KEY', config('app.key')),
            'app.url' => env('APP_URL', config('app.url')),
            'database.default' => env('DB_CONNECTION', config('database.default')),
            'database.connections.mysql.host' => env('DB_HOST', '127.0.0.1'),
            'database.connections.mysql.port' => env('DB_PORT', '3306'),
            'database.connections.mysql.database' => env('DB_DATABASE', 'qcoressys'),
            'database.connections.mysql.username' => env('DB_USERNAME', 'root'),
            'database.connections.mysql.password' => env('DB_PASSWORD', ''),
            'database.connections.sqlite.database' => env('DB_DATABASE', database_path('database.sqlite')),
            'setup.auto' => filter_var(env('AUTO_SETUP', true), FILTER_VALIDATE_BOOLEAN),
            'setup.admin.name' => env('ADMIN_NAME', config('setup.admin.name')),
            'setup.admin.email' => env('ADMIN_EMAIL', config('setup.admin.email')),
            'setup.admin.username' => env('ADMIN_USERNAME', config('setup.admin.username')),
            'setup.admin.password' => env('ADMIN_PASSWORD', config('setup.admin.password')),
            'setup.portal.name' => env('PORTAL_DEMO_NAME', config('setup.portal.name')),
            'setup.portal.email' => env('PORTAL_DEMO_EMAIL', config('setup.portal.email')),
            'setup.portal.username' => env('PORTAL_DEMO_USERNAME', config('setup.portal.username')),
            'setup.portal.password' => env('PORTAL_DEMO_PASSWORD', config('setup.portal.password')),
            'setup.portal.company' => env('PORTAL_DEMO_COMPANY', config('setup.portal.company')),
        ]);

        DB::purge();
    }

    private function ensureDatabaseExists(): void
    {
        $connection = (string) config('database.default');

        if ($connection === 'sqlite') {
            $path = (string) config('database.connections.sqlite.database');

            if ($path !== '' && $path !== ':memory:' && ! is_file($path)) {
                $directory = dirname($path);
                if (! is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                touch($path);
            }

            return;
        }

        if (! in_array($connection, ['mysql', 'mariadb'], true)) {
            return;
        }

        $database = (string) config("database.connections.{$connection}.database");

        if ($database === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            return;
        }

        $charset = (string) config("database.connections.{$connection}.charset", 'utf8mb4');
        $collation = (string) config("database.connections.{$connection}.collation", 'utf8mb4_unicode_ci');

        config(["database.connections.{$connection}.database" => null]);
        DB::purge($connection);

        try {
            DB::connection($connection)->statement(
                "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}"
            );
        } catch (QueryException $e) {
            Log::warning('CREATE DATABASE is not permitted (typical on cPanel). Continuing with the existing database.', [
                'sql' => $e->getSql(),
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'database' => $database,
                'username' => config("database.connections.{$connection}.username"),
            ]);
        } catch (Throwable) {
            // Shared hosts often forbid CREATE DATABASE; migrate will surface a clearer error.
        } finally {
            config(["database.connections.{$connection}.database" => $database]);
            DB::purge($connection);
        }
    }

    private function ensureStorageLink(): void
    {
        if (app()->environment('testing') || file_exists(public_path('storage'))) {
            return;
        }

        try {
            Artisan::call('storage:link');
        } catch (Throwable) {
            // Hosting panels sometimes block symlink creation.
        }
    }

    /**
     * @return resource|null
     */
    private function acquireLock(string $path)
    {
        try {
            $handle = fopen($path, 'c+');
        } catch (Throwable) {
            return null;
        }

        if ($handle === false) {
            return null;
        }

        flock($handle, LOCK_EX);

        return $handle;
    }

    /**
     * @param  resource|null  $handle
     */
    private function releaseLock(mixed $handle): void
    {
        if (! is_resource($handle)) {
            return;
        }

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private function writeEnvValue(string $key, string $value): void
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            return;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return;
        }

        $line = $key.'='.$value;
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        $contents = preg_match($pattern, $contents)
            ? preg_replace($pattern, $line, $contents, 1)
            : rtrim($contents)."\n{$line}\n";

        file_put_contents($path, $contents);
    }
}
