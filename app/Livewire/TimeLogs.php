<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TimeLog;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Timer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimeLogs extends Component
{
    public $project_id;
    public $description;
    public $start_time;
    public $end_time;
    public $duration_minutes;
    public $selectedTags = [];
    public $view = 'list'; // list, weekly
    public $startOfWeek;
    public $endOfWeek;
    public $currentWeek;
    public $editingTimeLog = null;
    public $selected_date;
    
    protected $rules = [
        'project_id' => 'nullable|exists:projects,id',
        'description' => 'nullable',
        'duration_minutes' => 'required|numeric|min:1',
        'selected_date' => 'required|date'
    ];
    
    public function mount()
    {
        $this->selected_date = now()->format('Y-m-d');
        $this->start_time = now()->format('Y-m-d H:i:s');
        $this->initializeWeek();
    }
    
    public function initializeWeek()
    {
        $this->currentWeek = now();
        $this->updateWeekRange();
    }
    
    public function previousWeek()
    {
        $this->currentWeek = $this->currentWeek->subWeek();
        $this->updateWeekRange();
    }
    
    public function nextWeek()
    {
        $this->currentWeek = $this->currentWeek->addWeek();
        $this->updateWeekRange();
    }
    
    public function currentWeek()
    {
        $this->currentWeek = now();
        $this->updateWeekRange();
    }
    
    private function updateWeekRange()
    {
        $this->startOfWeek = $this->currentWeek->copy()->startOfWeek()->format('Y-m-d');
        $this->endOfWeek = $this->currentWeek->copy()->endOfWeek()->format('Y-m-d');
    }
    
    public function switchView($view)
    {
        $this->view = $view;
    }
    
    public function calculateDuration()
    {
        if (!empty($this->start_time) && !empty($this->end_time)) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            $this->duration_minutes = $end->diffInMinutes($start);
        }
    }
    
    public function save()
    {
        $this->validate();
        
        // Create start and end times from the selected date
        $start_time = Carbon::parse($this->selected_date)->startOfDay();
        $end_time = $start_time->copy()->addMinutes((int) $this->duration_minutes);
        
        $timeLog = TimeLog::create([
            'project_id' => $this->project_id,
            'user_id' => auth()->id(),
            'description' => $this->description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => (int) $this->duration_minutes,
        ]);
        
        if (!empty($this->selectedTags)) {
            $timeLog->tags()->attach($this->selectedTags);
        }
        
        $this->reset(['project_id', 'description', 'duration_minutes', 'selectedTags']);
        $this->selected_date = now()->format('Y-m-d'); // Reset to today
        session()->flash('message', 'Time log created successfully.');
    }
    
    public function startEdit($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $this->editingTimeLog = $timeLogId;
        $this->project_id = $timeLog->project_id;
        $this->description = $timeLog->description;
        $this->duration_minutes = $timeLog->duration_minutes;
        $this->selectedTags = $timeLog->tags->pluck('id')->toArray();
    }

    public function cancelEdit()
    {
        $this->reset([
            'editingTimeLog', 'project_id', 'description', 
            'duration_minutes', 'selectedTags'
        ]);
    }

    public function updateTimeLog()
    {
        $this->validate();
        
        $timeLog = TimeLog::findOrFail($this->editingTimeLog);
        
        // Keep the original start time and calculate end time based on duration
        $start_time = $timeLog->start_time;
        $end_time = $start_time->copy()->addMinutes((int) $this->duration_minutes);
        
        $timeLog->update([
            'project_id' => $this->project_id,
            'description' => $this->description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => (int) $this->duration_minutes,
        ]);

        $timeLog->tags()->sync($this->selectedTags);
        
        $this->cancelEdit();
        session()->flash('message', 'Time log updated successfully.');
    }

    public function deleteTimeLog($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $timeLog->delete();
        session()->flash('message', 'Time log deleted successfully.');
    }
    
    public function getWeeklyDataProperty()
    {
        // Get time logs for the selected week
        $timeLogs = TimeLog::where('user_id', auth()->id())
            ->whereBetween('start_time', [
                $this->startOfWeek . ' 00:00:00',
                $this->endOfWeek . ' 23:59:59'
            ])
            ->with(['project', 'timer', 'tags'])
            ->get();
        
        // Group time logs by project and timer
        $weekData = [];
        $totalDuration = 0;
        
        // Get all days in the week for display
        $startDate = Carbon::parse($this->startOfWeek);
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $weekDays[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'dayName' => $date->format('l'),
            ];
        }
        
        // Group by project
        $projects = $timeLogs->groupBy('project_id');
        
        foreach ($projects as $projectId => $projectLogs) {
            $projectName = $projectLogs->first()->project->name;
            $projectTotal = 0;
            $timers = [];
            
            // Group by timer
            $timerGroups = $projectLogs->groupBy('timer_id');
            
            foreach ($timerGroups as $timerId => $timerLogs) {
                $timerName = $timerId ? ($timerLogs->first()->timer->name ?? 'Unnamed Timer') : 'Manual Entry';
                $timerTotal = 0;
                $dailyDurations = array_fill_keys(array_keys($weekDays), 0);
                
                // Calculate daily durations
                foreach ($timerLogs as $log) {
                    $logDate = Carbon::parse($log->start_time)->format('Y-m-d');
                    $dailyDurations[$logDate] = ($dailyDurations[$logDate] ?? 0) + $log->duration_minutes;
                    $timerTotal += $log->duration_minutes;
                }
                
                $timers[] = [
                    'id' => $timerId,
                    'name' => $timerName,
                    'daily' => $dailyDurations,
                    'total' => $timerTotal,
                    'tags' => $timerLogs->flatMap->tags->unique('id')->values()
                ];
                
                $projectTotal += $timerTotal;
            }
            
            $weekData[] = [
                'id' => $projectId,
                'name' => $projectName,
                'timers' => $timers,
                'total' => $projectTotal
            ];
            
            $totalDuration += $projectTotal;
        }
        
        return [
            'weekDays' => $weekDays,
            'projects' => $weekData,
            'total' => $totalDuration
        ];
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
    
    public function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
        }
        
        return $mins . 'm';
    }
    
    public function render()
    {
        return view('livewire.time-logs', [
            'timeLogs' => TimeLog::where('user_id', auth()->id())->latest()->get(),
            'projects' => Project::where('user_id', auth()->id())->get(),
            'tags' => Tag::all(),
        ]);
    }
}
