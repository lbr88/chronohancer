<?php

namespace App\Models;

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
        'duration_minutes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'project_id' => 'integer',
        'duration_minutes' => 'integer'
    ];

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
    
    /**
     * Get the project for this time log, using the default project if none is assigned
     */
    public function getProjectAttribute($value)
    {
        if ($this->project_id) {
            return $this->getRelationValue('project');
        }
        
        // Use the default project if no project is assigned
        return Project::findOrCreateDefault($this->user_id);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
