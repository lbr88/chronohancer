<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'timer_id',
        'user_id',
        'project_id',
        'workspace_id',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'tempo_worklog_id',
        'synced_to_tempo_at',
        'jira_issue_id',
        'jira_issue_key',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'project_id' => 'integer',
        'duration_minutes' => 'integer',
        'synced_to_tempo_at' => 'datetime',
        'jira_issue_id' => 'string',
        'jira_issue_key' => 'string',
    ];

    /**
     * Scope a query to only include time logs that haven't been synced to Tempo.
     */
    public function scopeNotSyncedToTempo(Builder $query): void
    {
        $query->whereNull('tempo_worklog_id');
    }

    /**
     * Get Tempo worklog details.
     */
    public function getTempoWorklogDetails(): ?array
    {
        if (! $this->tempo_worklog_id) {
            return null;
        }

        $tempoService = app(App\Services\TempoService::class);

        return $tempoService->getWorklogDetails($this->tempo_worklog_id);
    }

    /**
     * Check if the time log has been synced to Tempo.
     */
    public function isSyncedToTempo(): bool
    {
        return ! is_null($this->tempo_worklog_id);
    }

    public function timer(): BelongsTo
    {
        return $this->belongsTo(Timer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Project relationship is now required, so we don't need the getProjectAttribute method

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get Jira issue details.
     */
    public function getJiraIssueDetails(): ?array
    {
        if (! $this->jira_issue_key) {
            return null;
        }

        $jiraService = app(App\Services\JiraService::class);

        return $jiraService->getIssue($this->jira_issue_key);
    }

    /**
     * Check if the time log has a Jira issue associated.
     */
    public function hasJiraIssue(): bool
    {
        return ! is_null($this->jira_issue_key);
    }

    /**
     * Set Jira issue by key.
     */
    public function setJiraIssue(string $issueKey): bool
    {
        $jiraService = app(App\Services\JiraService::class);

        if (! $jiraService->validateIssue($issueKey)) {
            return false;
        }

        $issueId = $jiraService->getIssueId($issueKey);
        if (! $issueId) {
            return false;
        }

        $this->jira_issue_key = $issueKey;
        $this->jira_issue_id = $issueId;
        $this->save();

        return true;
    }
}
