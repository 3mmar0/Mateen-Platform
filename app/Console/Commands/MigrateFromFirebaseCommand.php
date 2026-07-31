<?php
namespace App\Console\Commands;
use App\Services\FirebaseMigrationService; use Illuminate\Console\Command;
class MigrateFromFirebaseCommand extends Command {
 protected $signature='mateen:migrate-firebase {path : JSON export path} {--dry-run}'; protected $description='Import a Firebase JSON export into Mateen';
 public function handle(FirebaseMigrationService $service):int {try{$counts=$service->load($this->argument('path'),$this->option('dry-run'));$this->table(['Collection','Records'],collect($counts)->map(fn($v,$k)=>[$k,$v]));return self::SUCCESS;}catch(\Throwable $e){$this->error($e->getMessage());return self::FAILURE;}}
}
