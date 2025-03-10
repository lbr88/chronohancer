<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Timer;
use App\Models\Tag;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $period = 'week'; // week, month, year
    public $startDate;
    public $endDate;
    
    public function mount()
    {
        $this->setPeriod('week');
    }
    
    public function setPeriod($period)
    {
        $this->period = $period;
        
        switch ($period) {
            case 'week':
                $this->startDate = now()->startOfWeek();
                $this->endDate = now()->endOfWeek();
                break;
            case 'month':
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
                break;
            case 'year':
                $this->startDate = now()->startOfYear();
                $this->endDate = now()->endOfYear();
                break;
        }
    }
    
    public function getTimeDistributionProperty()
    {
        $timeLogs = TimeLog::where('user_id', auth()->id())
            ->whereBetween('start_time', [$this->startDate, $this->endDate])
            ->get();
        
        // Group by project
        $projectTotals = [];
        $totalDuration = 0;
        
        foreach ($timeLogs as $log) {
            $projectId = $log->project_id;
            if (!isset($projectTotals[$projectId])) {
                $projectTotals[$projectId] = [
                    'id' => $projectId,
                    'name' => $log->project->name,
                    'duration' => 0,
                    'percentage' => 0,
                    'color' => $this->getRandomColor($projectId)
                ];
            }
            
            $projectTotals[$projectId]['duration'] += $log->duration_minutes;
            $totalDuration += $log->duration_minutes;
        }
        
        // Calculate percentages
        if ($totalDuration > 0) {
            foreach ($projectTotals as &$project) {
                $project['percentage'] = round(($project['duration'] / $totalDuration) * 100, 1);
            }
        }
        
        return [
            'projects' => array_values($projectTotals),
            'totalDuration' => $totalDuration,
            'formattedTotal' => $this->formatDuration($totalDuration)
        ];
    }
    
    public function getDailyActivityProperty()
    {
        $days = collect(CarbonPeriod::create($this->startDate, $this->endDate))
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->flip()
            ->map(function () {
                return 0;
            })
            ->toArray();
        
        $dailyData = TimeLog::where('user_id', auth()->id())
            ->whereBetween('start_time', [$this->startDate, $this->endDate])
            ->get()
            ->groupBy(function ($log) {
                return Carbon::parse($log->start_time)->format('Y-m-d');
            })
            ->map(function ($logs) {
                return $logs->sum('duration_minutes');
            })
            ->toArray();
        
        return array_merge($days, $dailyData);
    }
    
    public function getPopularTagsProperty()
    {
        // Get most used tags in the selected period
        return DB::table('tags')
            ->join('tag_time_log', 'tags.id', '=', 'tag_time_log.tag_id')
            ->join('time_logs', 'tag_time_log.time_log_id', '=', 'time_logs.id')
            ->where('time_logs.user_id', auth()->id())
            ->whereBetween('time_logs.start_time', [$this->startDate, $this->endDate])
            ->select('tags.id', 'tags.name', 'tags.color', DB::raw('COUNT(*) as count'))
            ->groupBy('tags.id', 'tags.name', 'tags.color')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }
    
    public function getRunningTimersProperty()
    {
        return Timer::with(['project', 'tags'])
            ->where('user_id', auth()->id())
            ->where('is_running', true)
            ->get();
    }
    
    public function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
        }
        
        return $mins . 'm';
    }
    
    private function getRandomColor($seed)
    {
        // Generate a deterministic color based on the project ID
        srand($seed);
        $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        srand(); // Reset seed
        
        return $color;
    }
    
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
        return view('livewire.dashboard', [
            'projects' => Project::where('user_id', auth()->id())->with('timers')->get(),
            'recentTimeLogs' => TimeLog::where('user_id', auth()->id())
                ->with(['project', 'tags'])
                ->latest()
                ->take(5)
                ->get(),
            'tagCount' => Tag::count(),
            'projectCount' => Project::where('user_id', auth()->id())->count(),
        ]);
    }
}
