<?php

namespace App\Models;

use App\Services\JiraService;
use App\Services\TempoService;
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
        'timer_description_id',
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
        'microsoft_event_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'project_id' => 'integer',
        'duration_minutes' => 'integer',
        'synced_to_tempo_at' => 'datetime',
        'jira_issue_id' => 'string',
        'jira_issue_key' => 'string',
        'microsoft_event_id' => 'string',
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

        $tempoService = app(TempoService::class);

        return $tempoService->getWorklogDetails($this->tempo_worklog_id, $this->user);
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

    /**
     * Get the timer description associated with this time log.
     */
    public function timerDescription(): BelongsTo
    {
        return $this->belongsTo(TimerDescription::class);
    }

    /**
     * Get the description attribute from the timer description or the local description.
     */
    public function getDescriptionTextAttribute(): ?string
    {
        return $this->timerDescription?->description ?? $this->description;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Note: The project relationship is accessed through the timer relationship.
     * When eager loading, use 'timer.project' instead of 'project'.
     *
     * @deprecated Use timer.project for eager loading instead
     */
    public function project()
    {
        // This is a placeholder relationship to prevent errors when using ->with('project')
        // It doesn't actually return a proper relationship
        return $this->timer();
    }

    /**
     * Get the project attribute through the timer relationship.
     * Kept for backward compatibility.
     */
    public function getProjectAttribute()
    {
        return $this->timer?->project;
    }

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

        $jiraService = app(JiraService::class);

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
     * Check if the time log was created from a Microsoft calendar event.
     */
    public function isFromMicrosoftEvent(): bool
    {
        return ! is_null($this->microsoft_event_id);
    }

    /**
     * Set Jira issue by key.
     */
    public function setJiraIssue(string $issueKey): bool
    {
        $jiraService = app(JiraService::class);

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
