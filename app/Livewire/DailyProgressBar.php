<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Polling;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyProgressBar extends Component
{
    public $totalDailyMinutes = 0;
    public $dailyProgressPercentage = 0;
    public $remainingDailyTime = '7h 24m';
    public $requiredMinutes = 444; // 7.4 hours = 444 minutes
    public $dailyTimeLogs;
    public $currentTime;

    public function mount()
    {
        $this->loadData();
        $this->currentTime = now()->format('H:i:s');
    }

    #[Polling('30s')]

    public function loadData()
    {
        $this->dailyTimeLogs = $this->getDailyTimeLogs();
        $this->totalDailyMinutes = $this->getTotalDailyMinutes();
        $this->dailyProgressPercentage = $this->getDailyProgressPercentage();
        $this->remainingDailyTime = $this->getRemainingDailyTime();
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
     * Get the total minutes logged for the current day
     *
     * @return int
     */
    public function getTotalDailyMinutes()
    {
        return $this->getDailyTimeLogs()->sum('duration_minutes');
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
     * Calculate contrasting text color (black or white) based on background color
     *
     * @param string $hexColor
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
