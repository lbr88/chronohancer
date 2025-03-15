<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteJiraIssue extends Model
{
    protected $fillable = [
        'user_id',
        'workspace_id',
        'jira_issue_id',
        'key',
        'title',
        'status',
    ];

    /**
     * Get the user that owns the favorite issue.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workspace that contains the favorite issue.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the Jira issue URL.
     */
    public function getJiraUrlAttribute(): string
    {
        // Get base URL from config
        $baseUrl = config('jira.base_url');

        return "{$baseUrl}/browse/{$this->key}";
    }
}
