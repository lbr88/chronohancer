<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'project_id', 'workspace_id', 'is_running', 'is_paused', 'user_id', 'jira_key'];

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

    /**
     * Get the description attribute from the latest time log.
     */
    public function getDescriptionAttribute(): ?string
    {
        // Get the latest time log for this timer
        $latestTimeLog = $this->timeLogs()->latest()->first();

        // Return the description from the time log if available
        return $latestTimeLog?->description;
    }

    /**
     * Set the name attribute and trim whitespace.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = trim($value);
    }

    /**
     * Set the jira_key attribute and trim whitespace.
     */
    public function setJiraKeyAttribute($value): void
    {
        $this->attributes['jira_key'] = $value ? trim($value) : null;
    }

    public function getJiraKeyAttribute(): ?string
    {
        if ($this->attributes['jira_key'] ?? null) {
            return $this->attributes['jira_key'];
        }

        if (preg_match('/^([A-Z]+-\d+):/', $this->name, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
