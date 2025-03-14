<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all projects with name "No Project" to "Default Project"
        $projects = Project::where('name', 'No Project')->get();
        foreach ($projects as $project) {
            $project->update(['name' => 'Default Project']);
        }

        // Update all projects with name like "No Project (X)" to "Default Project (X)"
        $projects = Project::where('name', 'like', 'No Project (%')->get();
        foreach ($projects as $project) {
            $newName = str_replace('No Project', 'Default Project', $project->name);
            $project->update(['name' => $newName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update all projects with name "Default Project" back to "No Project"
        $projects = Project::where('name', 'Default Project')->get();
        foreach ($projects as $project) {
            $project->update(['name' => 'No Project']);
        }

        // Update all projects with name like "Default Project (X)" back to "No Project (X)"
        $projects = Project::where('name', 'like', 'Default Project (%')->get();
        foreach ($projects as $project) {
            $newName = str_replace('Default Project', 'No Project', $project->name);
            $project->update(['name' => $newName]);
        }
    }
};
