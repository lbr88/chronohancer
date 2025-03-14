<?php

namespace App\Livewire;

use App\Models\TimeLog;
use App\Models\Timer;
use Livewire\Attributes\Polling;
use Livewire\Component;

class DailyProgressBar extends Component
{
    public $totalDailyMinutes = 0;

    public $dailyProgressPercentage = 0;

    public $remainingDailyTime = '7h 24m';

    public $requiredMinutes = 444; // 7.4 hours = 444 minutes

    public $dailyTimeLogs;

    public $currentTime;

    public $activeTimers = [];

    protected $listeners = [
        'timerStarted' => 'handleTimerStarted',
        'timerStopped' => 'handleTimerStopped',
        'timerPaused' => 'handleTimerPaused',
        'timeLogSaved' => 'loadData',
    ];

    public function mount()
    {
        $this->loadData();
        $this->currentTime = now()->format('H:i:s');
    }

    #[Polling('30s')]
    public function loadData()
    {
        $this->dailyTimeLogs = $this->getDailyTimeLogs();
        $this->activeTimers = $this->getActiveTimers();
        $this->totalDailyMinutes = $this->getTotalDailyMinutes();
        $this->dailyProgressPercentage = $this->getDailyProgressPercentage();
        $this->remainingDailyTime = $this->getRemainingDailyTime();

        // Dispatch an event to update the JavaScript timer
        $this->dispatch('dailyProgressUpdated', [
            'totalMinutes' => $this->totalDailyMinutes,
            'percentage' => $this->dailyProgressPercentage,
            'remainingTime' => $this->remainingDailyTime,
            'activeTimers' => $this->activeTimers,
        ]);
    }

    /**
     * Get all time logs for the current day
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDailyTimeLogs()
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        return TimeLog::where('user_id', auth()->id())
            ->whereNotNull('end_time') // Only completed logs
            ->where('start_time', '>=', $today)
            ->where('start_time', '<', $tomorrow)
            ->with(['project', 'tags', 'timer'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get all active timers for the current day
     *
     * @return array
     */
    public function getActiveTimers()
    {
        $today = now()->startOfDay();

        $activeTimers = Timer::with('latestTimeLog')
            ->where('user_id', auth()->id())
            ->where('is_running', true)
            ->get()
            ->filter(function ($timer) use ($today) {
                // Only include timers that were started today
                return $timer->latestTimeLog &&
                       $timer->latestTimeLog->start_time >= $today &&
                       $timer->latestTimeLog->end_time === null;
            })
            ->map(function ($timer) {
                $startTime = $timer->latestTimeLog->start_time;
                $currentDuration = $startTime->diffInMinutes(now());

                return [
                    'id' => $timer->id,
                    'start_time' => $startTime->toIso8601String(),
                    'current_duration' => $currentDuration,
                ];
            })
            ->values()
            ->toArray();

        return $activeTimers;
    }

    /**
     * Get the total minutes logged for the current day
     * including active timers
     *
     * @return int
     */
    public function getTotalDailyMinutes()
    {
        // Sum of completed time logs
        $completedMinutes = $this->getDailyTimeLogs()->sum('duration_minutes');

        // Add minutes from active timers
        $activeMinutes = collect($this->activeTimers)->sum('current_duration');

        return $completedMinutes + $activeMinutes;
    }

    /**
     * Get the percentage of the required daily hours (7.4 hours = 444 minutes)
     *
     * @return int
     */
    public function getDailyProgressPercentage()
    {
        $totalMinutes = $this->totalDailyMinutes;
        $percentage = min(100, round(($totalMinutes / $this->requiredMinutes) * 100));

        return $percentage;
    }

    /**
     * Get the remaining time to reach the daily goal of 7.4 hours
     *
     * @return string
     */
    public function getRemainingDailyTime()
    {
        $remainingMinutes = max(0, $this->requiredMinutes - $this->totalDailyMinutes);

        $hours = floor($remainingMinutes / 60);
        $minutes = $remainingMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Handle timer started event
     */
    public function handleTimerStarted($data)
    {
        $this->loadData();
    }

    /**
     * Handle timer stopped event
     */
    public function handleTimerStopped($data)
    {
        $this->loadData();
    }

    /**
     * Handle timer paused event
     */
    public function handleTimerPaused($data)
    {
        $this->loadData();
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

    public function render()
    {
        return view('livewire.daily-progress-bar');
    }
}
