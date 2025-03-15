<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Timer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'project_id', 'workspace_id', 'is_running', 'is_paused', 'user_id'];

    protected $casts = [
        'is_running' => 'boolean',
        'is_paused' => 'boolean',
    ];

    protected $with = ['latestTimeLog']; // Eager load by default to prevent N+1 queries

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Project relationship is now required, so we don't need the getProjectAttribute method

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    public function latestTimeLog(): HasOne
    {
        return $this->hasOne(TimeLog::class)->latestOfMany();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getJiraKeyAttribute(): ?string
    {
        if (preg_match('/^([A-Z]+-\d+):/', $this->name, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
