<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id', 'color', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the workspace.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects for the workspace.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the tags for the workspace.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Get the timers for the workspace.
     */
    public function timers(): HasMany
    {
        return $this->hasMany(Timer::class);
    }

    /**
     * Get the time logs for the workspace.
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Find or create the default workspace for a user
     *
     * @param int $userId
     * @return \App\Models\Workspace
     */
    public static function findOrCreateDefault(int $userId): self
    {
        $defaultWorkspace = self::where('user_id', $userId)
            ->where('is_default', true)
            ->first();
            
        if (!$defaultWorkspace) {
            $defaultWorkspace = self::create([
                'name' => 'Default Workspace',
                'description' => 'Your default workspace',
                'user_id' => $userId,
                'color' => '#6366f1', // Indigo color
                'is_default' => true,
            ]);
        }
        
        return $defaultWorkspace;
    }
}
