<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Building;
use App\Models\Elevator;
use App\Models\Installation;
use App\Models\Incident;
use App\Models\User;
use App\Models\Branch;
use App\Models\MaintenanceCheck;
use Carbon\Carbon;

class CleanupTrashCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete trashed items older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = [
            Building::class,
            Elevator::class,
            Installation::class,
            Incident::class,
            User::class,
            Branch::class,
            MaintenanceCheck::class,
        ];

        $cutoffDate = Carbon::now()->subDays(30);
        $totalDeleted = 0;

        foreach ($models as $modelClass) {
            $deletedCount = $modelClass::onlyTrashed()
                ->where('deleted_at', '<=', $cutoffDate)
                ->forceDelete();
            
            $totalDeleted += $deletedCount;
            
            if ($deletedCount > 0) {
                $this->info("Deleted $deletedCount items from " . class_basename($modelClass));
            }
        }

        $this->info("Trash cleanup completed. Total items permanently deleted: $totalDeleted");
    }
}
