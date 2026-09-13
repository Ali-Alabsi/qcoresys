<?php

namespace App\Console\Commands;

use App\Services\ApplicationSetupService;
use Illuminate\Console\Command;

class InstallApplicationCommand extends Command
{
    protected $signature = 'qcoresys:install';

    protected $description = 'Migrate the database, seed prototype data, and create the admin account';

    public function handle(ApplicationSetupService $setup): int
    {
        $this->info('Initializing QCoreSys…');

        $setup->install(force: true);

        $this->newLine();
        $this->info('Application is ready.');
        $this->line('Admin login:  '.$setup->adminEmail());
        $this->line('Admin password: '.$setup->adminPassword());
        $this->line('URL: '.url('/admin/login'));

        return self::SUCCESS;
    }
}
