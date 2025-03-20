<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Auth;

trait TimeLogsUtilities
{
    public $timeFormat = 'human'; // human, hms, hm

    /**
     * Format duration in minutes to a human-readable string
     *
     * @param  int  $minutes
     * @return string
     */
    public function formatDuration($minutes)
    {
        if ($this->timeFormat === 'hms') {
            // Format as HH:MM:SS
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            $secs = 0; // We don't have seconds in our data model

            return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } elseif ($this->timeFormat === 'hm') {
            // Format as HH:MM
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;

            return sprintf('%02d:%02d', $hours, $mins);
        } else {
            // Human readable format (e.g., 3h 40m)
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;

            if ($hours > 0) {
                return $hours.'h '.($mins > 0 ? $mins.'m' : '');
            }

            return $mins.'m';
        }
    }

    /**
     * Format the remaining time in a human-readable format
     *
     * @param  int  $minutes
     * @return string
     */
    public function formatRemainingTime($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    /**
     * Calculate contrasting text color (black or white) based on background color
     *
     * @param  string  $hexColor
     * @return string
     */
    public function getContrastColor($hexColor)
    {
        // Remove # if present
        $hexColor = ltrim($hexColor, '#');

        // Convert to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));

        // Calculate luminance - ITU-R BT.709
        $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;

        // Return black for bright colors, white for dark colors
        return ($luminance > 0.5) ? '#000000' : '#FFFFFF';
    }

    /**
     * Get CSS class for duration display
     *
     * @param  int  $minutes
     * @return string
     */
    public function getDurationClass($minutes)
    {
        if ($minutes < 30) {
            return 'bg-green-100 text-green-800'; // Short duration
        } elseif ($minutes < 120) {
            return 'bg-blue-100 text-blue-800'; // Medium duration
        } else {
            return 'bg-purple-100 text-purple-800'; // Long duration
        }
    }

    /**
     * Parse duration string in format like "3h5m" or "45m" into minutes
     *
     * @param  string  $durationString
     * @return int
     */
    public function parseDurationString($durationString)
    {
        // If it's already a number, return it
        if (is_numeric($durationString)) {
            return (int) $durationString;
        }

        $minutes = 0;

        // Match hours
        if (preg_match('/(\d+)h/', $durationString, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        // Match minutes
        if (preg_match('/(\d+)m/', $durationString, $matches)) {
            $minutes += (int) $matches[1];
        }

        return $minutes ?: (int) $durationString; // Fallback to treating it as minutes
    }

    /**
     * Calculate the remaining time to reach the daily target for a specific date
     *
     * @param  string  $date  Date in Y-m-d format
     * @return int Remaining minutes to reach the daily target
     */
    public function getRemainingTimeForDate($date)
    {
        $workspace = app('current.workspace');
        $targetMinutes = $workspace ? $workspace->daily_target_minutes : 0;

        // If target minutes is 0, return 0 (no target)
        if ($targetMinutes === 0) {
            return 0;
        }

        // Get all time logs for the specified date
        $totalMinutes = \App\Models\TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time') // Only include logs that have an end time
            ->whereDate('start_time', $date)
            ->sum('duration_minutes');

        $remainingMinutes = max(0, $targetMinutes - $totalMinutes);

        return $remainingMinutes;
    }

    /**
     * Set time format
     *
     * @param  string  $format
     * @return void
     */
    public function setTimeFormat($format)
    {
        $this->timeFormat = $format;
    }
}
