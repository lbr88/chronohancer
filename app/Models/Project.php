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

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
    
    /**
     * Find or create the default "No Project" project for a user in a workspace
     *
     * @param int $userId
     * @param int|null $workspaceId
     * @return \App\Models\Project
     */
    public static function findOrCreateDefault(int $userId, ?int $workspaceId = null): self
    {
        // If no workspace ID is provided, get the user's default workspace
        if (!$workspaceId) {
            $workspace = Workspace::findOrCreateDefault($userId);
            $workspaceId = $workspace->id;
        }
        
        $defaultProject = self::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('is_default', true)
            ->first();
            
        if (!$defaultProject) {
            $defaultProject = self::create([
                'name' => 'No Project',
                'description' => 'Default project for unassigned timers and time logs',
                'user_id' => $userId,
                'workspace_id' => $workspaceId,
                'color' => '#9ca3af', // Gray color
                'is_default' => true,
            ]);
        }
        
        return $defaultProject;
    }
}
