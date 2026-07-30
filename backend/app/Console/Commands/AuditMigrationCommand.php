<?php
namespace App\Console\Commands;
use App\Services\FirebaseMigrationService; use Illuminate\Console\Command;
class AuditMigrationCommand extends Command {
 protected $signature='mateen:audit-migration {path : JSON export path}'; protected $description='Report Firebase fixture collection counts';
 public function handle(FirebaseMigrationService $service):int {$rows=$service->audit($this->argument('path'));$this->table(['Collection','Source records'],collect($rows)->map(fn($v,$k)=>[$k,$v]));return self::SUCCESS;}
}
