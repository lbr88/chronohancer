<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Check if the user already has a default project
            $defaultProject = Project::where('user_id', $user->id)
                ->where('is_default', true)
                ->first();

            if (! $defaultProject) {
                // Create a default "No Project" project for the user
                Project::create([
                    'name' => 'No Project',
                    'description' => 'Default project for unassigned timers and time logs',
                    'user_id' => $user->id,
                    'color' => '#9ca3af', // Gray color
                    'is_default' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all default projects
        DB::table('projects')
            ->where('is_default', true)
            ->delete();
    }
};
