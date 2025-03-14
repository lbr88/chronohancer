<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Console\Command;

class FixDefaultProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-default-projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate default projects and ensure only one project is default per user and workspace';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix default projects...');

        // Get all users
        $users = User::all();
        $this->info("Found {$users->count()} users");

        $fixedDefaultCount = 0;
        $fixedNameCount = 0;

        foreach ($users as $user) {
            $this->info("Processing user ID: {$user->id}");

            // Get all workspaces for this user
            $workspaces = Workspace::where('user_id', $user->id)->get();

            foreach ($workspaces as $workspace) {
                $this->info("  Processing workspace ID: {$workspace->id}");

                // Fix default projects for this user and workspace
                $defaultProjects = Project::where('user_id', $user->id)
                    ->where('workspace_id', $workspace->id)
                    ->where('is_default', true)
                    ->get();

                // If there are multiple default projects, keep only one as default
                if ($defaultProjects->count() > 1) {
                    $this->info("    Found {$defaultProjects->count()} default projects, fixing...");

                    // Prefer keeping a "Default Project" as default if it exists
                    $defaultProjectDefault = $defaultProjects->first(function ($project) {
                        return $project->name === 'Default Project';
                    });

                    $keepAsDefault = $defaultProjectDefault ?? $defaultProjects->first();

                    // Unset default flag for all other projects
                    $updated = Project::where('user_id', $user->id)
                        ->where('workspace_id', $workspace->id)
                        ->where('id', '!=', $keepAsDefault->id)
                        ->where('is_default', true)
                        ->update(['is_default' => false]);

                    $fixedDefaultCount += $updated;
                    $this->info("    Fixed {$updated} default projects");
                } elseif ($defaultProjects->count() === 0) {
                    $this->info('    No default project found, creating one...');

                    // Check if a "Default Project" project already exists
                    $defaultProject = Project::where('user_id', $user->id)
                        ->where('workspace_id', $workspace->id)
                        ->where('name', 'Default Project')
                        ->first();

                    if ($defaultProject) {
                        // Set this existing "Default Project" as default
                        $defaultProject->update(['is_default' => true]);
                        $this->info("    Set existing 'Default Project' as default");
                    } else {
                        // Create a new default project
                        Project::create([
                            'name' => 'Default Project',
                            'description' => 'Default project for unassigned timers and time logs',
                            'user_id' => $user->id,
                            'workspace_id' => $workspace->id,
                            'color' => '#9ca3af', // Gray color
                            'is_default' => true,
                        ]);
                        $this->info("    Created new default 'Default Project'");
                    }

                    $fixedDefaultCount++;
                }

                // Fix duplicate "Default Project" names
                $defaultProjects = Project::where('user_id', $user->id)
                    ->where('workspace_id', $workspace->id)
                    ->where('name', 'Default Project')
                    ->get();

                if ($defaultProjects->count() > 1) {
                    $this->info("    Found {$defaultProjects->count()} 'Default Project' projects, fixing names...");

                    // Keep the first one with the original name
                    $keepOriginal = $defaultProjects->first();

                    // Rename others with a suffix
                    $counter = 1;
                    foreach ($defaultProjects as $project) {
                        if ($project->id !== $keepOriginal->id) {
                            $project->update([
                                'name' => "Default Project ({$counter})",
                            ]);
                            $counter++;
                            $fixedNameCount++;
                        }
                    }

                    $this->info('    Renamed '.($defaultProjects->count() - 1)." duplicate 'Default Project' projects");
                }
            }
        }

        $this->info('Completed fixing default projects');
        $this->info("Fixed {$fixedDefaultCount} default project issues");
        $this->info("Renamed {$fixedNameCount} duplicate 'Default Project' projects");

        return Command::SUCCESS;
    }
}
