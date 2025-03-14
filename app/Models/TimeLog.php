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
        'duration_minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'project_id' => 'integer',
        'duration_minutes' => 'integer',
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

    // Project relationship is now required, so we don't need the getProjectAttribute method

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
