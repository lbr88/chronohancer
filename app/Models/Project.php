<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'user_id', 'workspace_id', 'color', 'is_default'];

    protected $dates = ['deleted_at'];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // When setting a project as default, ensure no other project for the same user and workspace is default
        static::saving(function ($project) {
            if ($project->is_default) {
                static::where('user_id', $project->user_id)
                    ->where('workspace_id', $project->workspace_id)
                    ->where('id', '!=', $project->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function timers(): HasMany
    {
        return $this->hasMany(Timer::class);
    }

    /**
     * Get time logs associated with this project through timers.
     */
    public function timeLogs()
    {
        return TimeLog::whereHas('timer', function ($query) {
            $query->where('project_id', $this->id)->withTrashed();
        });
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Find or create the default "No Project" project for a user in a workspace
     */
    public static function findOrCreateDefault(int $userId, ?int $workspaceId = null): self
    {
        // If no workspace ID is provided, get the user's default workspace
        if (! $workspaceId) {
            $workspace = Workspace::findOrCreateDefault($userId);
            $workspaceId = $workspace->id;
        }

        // First, ensure only one project is marked as default
        self::fixDefaultProjects($userId, $workspaceId);

        // Try to find the default project
        $defaultProject = self::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('is_default', true)
            ->first();

        if (! $defaultProject) {
            // Check if a default project already exists but is not marked as default
            $defaultProject = self::where('user_id', $userId)
                ->where('workspace_id', $workspaceId)
                ->where('name', 'Default Project')
                ->first();

            if ($defaultProject) {
                // Set this existing default project as default
                $defaultProject->update(['is_default' => true]);
            } else {
                // Create a new default project
                $defaultProject = self::create([
                    'name' => 'Default Project',
                    'description' => 'Default project for unassigned timers and time logs',
                    'user_id' => $userId,
                    'workspace_id' => $workspaceId,
                    'color' => '#9ca3af', // Gray color
                    'is_default' => true,
                ]);
            }
        }

        return $defaultProject;
    }

    /**
     * Fix default projects for a user in a workspace
     * - Ensures only one project is marked as default
     * - Ensures "No Project" projects have unique names
     */
    public static function fixDefaultProjects(int $userId, int $workspaceId): void
    {
        // Get all default projects for this user and workspace
        $defaultProjects = self::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('is_default', true)
            ->get();

        // If there are multiple default projects, keep only one as default
        if ($defaultProjects->count() > 1) {
            // Prefer keeping a "Default Project" as default if it exists
            $defaultProjectDefault = $defaultProjects->first(function ($project) {
                return $project->name === 'Default Project';
            });

            $keepAsDefault = $defaultProjectDefault ?? $defaultProjects->first();

            // Unset default flag for all other projects
            self::where('user_id', $userId)
                ->where('workspace_id', $workspaceId)
                ->where('id', '!=', $keepAsDefault->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        // Fix duplicate "Default Project" names
        $defaultProjects = self::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('name', 'Default Project')
            ->get();

        if ($defaultProjects->count() > 1) {
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
                }
            }
        }
    }
}
