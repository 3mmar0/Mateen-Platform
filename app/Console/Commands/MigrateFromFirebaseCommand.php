<?php

namespace App\Console\Commands;

use App\Services\FirebaseMigrationService;
use Illuminate\Console\Command;

class MigrateFromFirebaseCommand extends Command
{
    protected $signature = 'mateen:migrate-firebase
        {path : JSON export path}
        {--dry-run}
        {--admin-email= : Preserve password for this email}
        {--admin-password= : Plain password to set for --admin-email}';

    protected $description = 'Import a Firebase JSON export into Mateen';

    public function handle(FirebaseMigrationService $service): int
    {
        try {
            $counts = $service->load($this->argument('path'), $this->option('dry-run'), [
                'admin_email' => $this->option('admin-email'),
                'admin_password' => $this->option('admin-password'),
            ]);
            $this->table(
                ['Collection', 'Records'],
                collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all()
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
