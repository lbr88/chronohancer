<?php

use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Timer;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Process each user separately to ensure they get their own default project
        $users = User::all();

        foreach ($users as $user) {
            // Find or create the default project for this user
            $defaultProject = Project::where('user_id', $user->id)
                ->where('is_default', true)
                ->first();

            if (! $defaultProject) {
                $defaultProject = Project::create([
                    'name' => 'No Project',
                    'description' => 'Default project for unassigned timers and time logs',
                    'user_id' => $user->id,
                    'color' => '#9ca3af', // Gray color
                    'is_default' => true,
                ]);
            }

            // Update all timers with null project_id to use the default project
            Timer::where('user_id', $user->id)
                ->whereNull('project_id')
                ->update(['project_id' => $defaultProject->id]);

            // Update all time logs with null project_id to use the default project
            TimeLog::where('user_id', $user->id)
                ->whereNull('project_id')
                ->update(['project_id' => $defaultProject->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as we don't know which timers and time logs
        // originally had null project_id. We could set them back to null, but that would
        // defeat the purpose of having a default project.
    }
};
