<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id', 'color', 'is_default', 'daily_target_minutes', 'weekly_target_minutes'];

    protected $casts = [
        'is_default' => 'boolean',
        'daily_target_minutes' => 'integer',
        'weekly_target_minutes' => 'integer',
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
     * Format minutes to a human-readable string (e.g., "7h 24m")
     */
    public function formatMinutesToHumanReadable(int $minutes): string
    {
        // Handle 0 minutes
        if ($minutes === 0) {
            return '0h';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours}h {$remainingMinutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$remainingMinutes}m";
        }
    }

    /**
     * Parse a human-readable string (e.g., "7h 24m") to minutes
     */
    public function parseHumanReadableToMinutes(string $timeString): int
    {
        $timeString = trim($timeString);

        // Handle empty or "0" input
        if (empty($timeString) || $timeString === '0' || $timeString === '0h' || $timeString === '0m' || $timeString === '0h 0m') {
            return 0;
        }

        $minutes = 0;

        // Match hours (e.g., "7h")
        if (preg_match('/(\d+)h/i', $timeString, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        // Match minutes (e.g., "24m")
        if (preg_match('/(\d+)m/i', $timeString, $matches)) {
            $minutes += (int) $matches[1];
        }

        return $minutes;
    }

    /**
     * Get the daily target time as a human-readable string
     */
    public function getDailyTargetTimeAttribute(): string
    {
        return $this->formatMinutesToHumanReadable($this->daily_target_minutes);
    }

    /**
     * Get the weekly target time as a human-readable string
     */
    public function getWeeklyTargetTimeAttribute(): string
    {
        return $this->formatMinutesToHumanReadable($this->weekly_target_minutes);
    }

    /**
     * Calculate daily target minutes from weekly target minutes
     * Assumes a 5-day work week
     */
    public function calculateDailyFromWeekly(int $weeklyMinutes): int
    {
        return (int) round($weeklyMinutes / 5);
    }

    /**
     * Calculate weekly target minutes from daily target minutes
     * Assumes a 5-day work week
     */
    public function calculateWeeklyFromDaily(int $dailyMinutes): int
    {
        return $dailyMinutes * 5;
    }

    /**
     * Find or create the default workspace for a user
     */
    public static function findOrCreateDefault(int $userId): self
    {
        $defaultWorkspace = self::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        if (! $defaultWorkspace) {
            $defaultWorkspace = self::create([
                'name' => 'Default Workspace',
                'description' => 'Your default workspace',
                'user_id' => $userId,
                'color' => '#6366f1', // Indigo color
                'is_default' => true,
                'daily_target_minutes' => 444, // 7.4 hours = 444 minutes
                'weekly_target_minutes' => 2220, // 37 hours = 2220 minutes
            ]);
        }

        return $defaultWorkspace;
    }
}
