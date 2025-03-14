<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'user_id', 'workspace_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function timers(): BelongsToMany
    {
        return $this->belongsToMany(Timer::class);
    }

    public function timeLogs(): BelongsToMany
    {
        return $this->belongsToMany(TimeLog::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    // Static method to find or create a tag by name for a user in a workspace
    public static function findOrCreateForUser(string $name, int $userId, ?int $workspaceId = null, ?string $color = null): self
    {
        // If no workspace ID is provided, get the user's default workspace
        if (!$workspaceId) {
            $workspace = Workspace::findOrCreateDefault($userId);
            $workspaceId = $workspace->id;
        }
        
        return static::firstOrCreate(
            ['name' => $name, 'user_id' => $userId, 'workspace_id' => $workspaceId],
            ['color' => $color ?? '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT)]
        );
    }
}
