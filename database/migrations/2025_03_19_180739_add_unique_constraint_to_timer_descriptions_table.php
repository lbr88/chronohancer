<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, handle existing duplicates by consolidating time_log references
        $this->consolidateDuplicateTimerDescriptions();

        // Then add the unique constraint
        Schema::table('timer_descriptions', function (Blueprint $table) {
            // Add a unique constraint for the combination of timer_id and description
            // This ensures that a timer cannot have duplicate descriptions
            // We also include user_id and workspace_id to ensure the constraint is properly scoped
            $table->unique(['timer_id', 'description', 'user_id', 'workspace_id'], 'unique_timer_description');
        });
    }

    /**
     * Consolidate duplicate timer descriptions without deleting them directly.
     * Instead, update references in time_logs to point to a single description.
     */
    private function consolidateDuplicateTimerDescriptions(): void
    {
        // Get the database connection
        $connection = DB::connection();
        $dbDriver = $connection->getDriverName();

        // Find duplicates and process them
        $query = '
            SELECT timer_id, description, user_id, workspace_id, COUNT(*) as count
            FROM timer_descriptions
            GROUP BY timer_id, description, user_id, workspace_id
            HAVING COUNT(*) > 1
        ';

        $duplicateGroups = DB::select($query);

        foreach ($duplicateGroups as $group) {
            // Find all duplicates for this group
            $duplicates = DB::table('timer_descriptions')
                ->select('id')
                ->where('timer_id', $group->timer_id)
                ->where('description', $group->description)
                ->where('user_id', $group->user_id)
                ->where('workspace_id', $group->workspace_id)
                ->orderBy('id', 'asc')
                ->get();

            if (count($duplicates) <= 1) {
                continue; // Skip if there's only one (shouldn't happen, but just in case)
            }

            // Keep the first/oldest record (lowest ID)
            $keepId = $duplicates[0]->id;
            $duplicateIds = $duplicates->pluck('id')->toArray();
            $removeIds = array_filter($duplicateIds, function ($id) use ($keepId) {
                return $id !== $keepId;
            });

            if (! empty($removeIds)) {
                // Update all time_logs that reference the duplicate descriptions
                // to point to the one we're keeping
                DB::table('time_logs')
                    ->whereIn('timer_description_id', $removeIds)
                    ->update(['timer_description_id' => $keepId]);

                // Now we can safely delete the duplicates that are no longer referenced
                DB::table('timer_descriptions')
                    ->whereIn('id', $removeIds)
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timer_descriptions', function (Blueprint $table) {
            // Drop the unique constraint if we need to rollback
            $table->dropUnique('unique_timer_description');
        });
    }
};
