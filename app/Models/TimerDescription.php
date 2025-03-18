<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimerDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'user_id',
        'workspace_id',
        'timer_id',
    ];

    /**
     * Get the timer that owns the description.
     */
    public function timer(): BelongsTo
    {
        return $this->belongsTo(Timer::class);
    }

    /**
     * Get the user that owns the description.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workspace that owns the description.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the time logs associated with this description.
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }
}
