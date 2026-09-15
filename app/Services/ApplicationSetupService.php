<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ApplicationSetupService
{
    private static bool $booted = false;

    /**
     * Prepare .env / APP_KEY, then migrate + seed on the first HTTP request.
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

        $this->ensureEnvironment();

        if (app()->runningInConsole()) {
            return;
        }

        // Fast path: no DB, no lock, no migration scan when already ready.
        if ($this->isSetupReady()) {
            return;
        }

        $this->install();
    }

    /**
     * Run migrations, seed prototype data, and ensure the admin exists.
     */
    public function install(bool $force = false): void
    {
        $this->ensureEnvironment();

        if (! $force && $this->isSetupReady()) {
            return;
        }

        $this->ensureDatabaseExists();

        $lockPath = storage_path('framework/app-setup.lock');
        $handle = $this->acquireLock($lockPath);

        try {
            if (! $force && $this->isSetupReady()) {
                return;
            }

            $wasInstalled = $this->isInstalled();

            if (! $force && ! $this->needsInstall()) {
                $this->markSetupReady();

                return;
            }

            Artisan::call('migrate', ['--force' => true]);

            // Seed only on first install (or forced CLI reinstall). Never re-seed
            // when applying pending migrations to an already-installed app.
            if ($force || ! $wasInstalled) {
                Artisan::call('db:seed', ['--force' => true]);
            }

            $this->ensureStorageLink();
            $this->markSetupReady();
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
        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('settings')) {
                return false;
            }

            return User::query()->where('email', $this->adminEmail())->exists()
                && Setting::query()->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Whether the cheap filesystem marker matches current migration files.
     */
    public function isSetupReady(): bool
    {
        $path = $this->setupReadyPath();

        if (! is_file($path)) {
            return false;
        }

        $stored = file_get_contents($path);

        if ($stored === false) {
            return false;
        }

        $storedList = array_values(array_filter(array_map('trim', explode("\n", $stored)), fn ($line) => $line !== ''));

        return $storedList === $this->migrationFileNames();
    }

    private function needsInstall(): bool
    {
        return ! $this->isInstalled() || $this->hasPendingMigrations();
    }

    private function hasPendingMigrations(): bool
    {
        try {
            $migrator = app('migrator');

            if (! $migrator->repositoryExists()) {
                return true;
            }

            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $pending = array_diff(array_keys($files), $migrator->getRan());

            return $pending !== [];
        } catch (Throwable) {
            return true;
        }
    }

    /**
     * @return list<string>
     */
    private function migrationFileNames(): array
    {
        $directory = database_path('migrations');

        if (! is_dir($directory)) {
            return [];
        }

        $files = scandir($directory);

        if ($files === false) {
            return [];
        }

        $names = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                $names[] = $file;
            }
        }

        sort($names);

        return $names;
    }

    private function markSetupReady(): void
    {
        $directory = storage_path('framework');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
            $this->setupReadyPath(),
            implode("\n", $this->migrationFileNames())."\n"
        );
    }

    private function setupReadyPath(): string
    {
        return storage_path('framework/setup.ready');
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
