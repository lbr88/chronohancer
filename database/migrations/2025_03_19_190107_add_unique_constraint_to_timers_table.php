<?php

use App\Models\Timer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * First resolve duplicate timer names, then add a unique constraint
     */
    public function up(): void
    {
        // First, identify and fix duplicate timers
        $this->fixDuplicateTimers();

        // Then add the unique constraint
        Schema::table('timers', function (Blueprint $table) {
            // Add a unique constraint for name, project_id, user_id, and workspace_id
            $table->unique(['name', 'project_id', 'user_id', 'workspace_id'], 'timers_name_project_user_workspace_unique');
        });
    }

    /**
     * Identify and fix duplicate timers
     */
    private function fixDuplicateTimers(): void
    {
        // Find duplicate timers based on name, project_id, user_id, and workspace_id
        $duplicates = DB::table('timers')
            ->select('name', 'project_id', 'user_id', 'workspace_id', DB::raw('COUNT(*) as count'))
            ->groupBy('name', 'project_id', 'user_id', 'workspace_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all timers with this duplicate combination
            $timers = DB::table('timers')
                ->where('name', $duplicate->name)
                ->where('project_id', $duplicate->project_id)
                ->where('user_id', $duplicate->user_id)
                ->where('workspace_id', $duplicate->workspace_id)
                ->orderBy('id')
                ->get();

            // Keep the first timer as is (this will be our primary timer)
            $primaryTimer = $timers->first();
            $counter = 1;

            // Process the rest of the timers (duplicates)
            foreach ($timers->skip(1) as $timer) {
                // Update the name to make it unique
                DB::table('timers')
                    ->where('id', $timer->id)
                    ->update([
                        'name' => $timer->name.' ('.$counter.')',
                    ]);

                // Note: We're not deleting any timers, just renaming them.
                // If we were to delete timers in the future, we would need to:
                // 1. Update time_logs to point to the primary timer
                // 2. Update timer_descriptions to point to the primary timer
                // 3. Transfer any other relationships (tags, etc.)

                $counter++;
            }
        }
    }

    /**
     * Transfer relationships from one timer to another
     * This method is not used in the current migration but is provided
     * as a reference for future migrations that might need to delete timers.
     */
    private function transferRelationships(int $fromTimerId, int $toTimerId): void
    {
        // Transfer time logs
        DB::table('time_logs')
            ->where('timer_id', $fromTimerId)
            ->update(['timer_id' => $toTimerId]);

        // Transfer timer descriptions
        DB::table('timer_descriptions')
            ->where('timer_id', $fromTimerId)
            ->update(['timer_id' => $toTimerId]);

        // Transfer tag relationships
        DB::table('tag_timer')
            ->where('timer_id', $fromTimerId)
            ->update(['timer_id' => $toTimerId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->dropUnique('timers_name_project_user_workspace_unique');
        });

        // Note: The duplicate resolution cannot be reversed as it modifies data
    }
};
