<?php

namespace App\Console\Commands;

use App\Services\ApplicationSetupService;
use Illuminate\Console\Command;

class InstallApplicationCommand extends Command
{
    protected $signature = 'qcoresys:install {--fresh : Drop all tables and rebuild seed data from scratch}';

    protected $description = 'Migrate the database, seed prototype data, and create the admin account';

    public function handle(ApplicationSetupService $setup): int
    {
        $fresh = (bool) $this->option('fresh');

        if ($fresh && $this->input->isInteractive() && ! $this->confirm('This will DELETE all database data. Continue?')) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $this->info($fresh ? 'Rebuilding QCoreSys from scratch…' : 'Initializing QCoreSys…');

        $setup->install(force: true, fresh: $fresh);

        $this->newLine();
        $this->info('Application is ready.');
        $this->line('Admin login:  '.$setup->adminEmail());
        $this->line('Admin password: '.$setup->adminPassword());
        $this->line('URL: '.url('/qcs/admin/login'));

        return self::SUCCESS;
    }
}
