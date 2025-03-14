<?php

use App\Models\Project;
use App\Models\Tag;
use App\Models\TimeLog;
use App\Models\Timer;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default workspaces for existing users
        $users = User::all();
        
        foreach ($users as $user) {
            // Create default workspace
            $workspace = Workspace::create([
                'name' => 'Default Workspace',
                'description' => 'Your default workspace',
                'user_id' => $user->id,
                'color' => '#6366f1', // Indigo color
                'is_default' => true,
            ]);
            
            // Update existing projects
            Project::where('user_id', $user->id)->update(['workspace_id' => $workspace->id]);
            
            // Update existing tags
            Tag::where('user_id', $user->id)->update(['workspace_id' => $workspace->id]);
            
            // Update existing timers
            Timer::where('user_id', $user->id)->update(['workspace_id' => $workspace->id]);
            
            // Update existing time logs
            TimeLog::where('user_id', $user->id)->update(['workspace_id' => $workspace->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just data manipulation
    }
};
