<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, fix any existing duplicate project names and default projects
        $this->fixExistingProjects();

        // Add a unique constraint to ensure project names are unique per user and workspace
        Schema::table('projects', function (Blueprint $table) {
            $table->unique(['user_id', 'workspace_id', 'name'], 'projects_user_workspace_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique('projects_user_workspace_name_unique');
        });
    }

    /**
     * Fix existing duplicate project names and default projects
     */
    private function fixExistingProjects(): void
    {
        // Get all users with their projects
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            // Get all workspaces for this user
            $workspaces = \App\Models\Workspace::where('user_id', $user->id)->get();

            foreach ($workspaces as $workspace) {
                // Fix default projects and duplicate "No Project" names
                Project::fixDefaultProjects($user->id, $workspace->id);
            }
        }
    }
};
