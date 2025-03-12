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
    public $sortField = 'start_time';
    public $sortDirection = 'desc';
    public $filterProject = null;
    public $filterTag = null;
    public $filterDateFrom = null;
    public $filterDateTo = null;
    public $searchQuery = '';
    public $timeFormat = 'human'; // human, hms, hm
    public $showFilters = false;
    public $confirmingDelete = null;
    public $showQuickTimeModal = false;
    public $showTimeLogSelectionModal = false;
    public $timeLogSelectionOptions = [];
    public $quickTimeDate;
    public $quickTimeProjectId;
    public $quickTimeTimerId;
    public $quickTimeDescription;
    public $quickTimeDuration = 0;
    public $quickTimeProjectTimers = [];
    
    protected $queryString = [
        'sortField' => ['except' => 'start_time'],
        'sortDirection' => ['except' => 'desc'],
        'filterProject' => ['except' => null],
        'filterTag' => ['except' => null],
        'searchQuery' => ['except' => ''],
        'view' => ['except' => 'list'],
    ];
    
    protected $rules = [
        'project_id' => 'nullable|exists:projects,id',
        'description' => 'nullable',
        'duration_minutes' => 'required',
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
    
    /**
     * Parse duration string in format like "3h5m" or "45m" into minutes
     *
     * @param string $durationString
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
    
    public function createForDate($date)
    {
        $this->selected_date = $date;
        $this->reset(['project_id', 'description', 'duration_minutes', 'selectedTags']);
        $this->dispatch('scroll-to-form');
    }
    
    public function save()
    {
        $this->validate();
        
        // Parse duration string into minutes
        $durationMinutes = $this->parseDurationString($this->duration_minutes);
        
        // Create start and end times from the selected date
        $start_time = Carbon::parse($this->selected_date)->startOfDay();
        $end_time = $start_time->copy()->addMinutes($durationMinutes);
        
        $timeLog = TimeLog::create([
            'project_id' => $this->project_id,
            'user_id' => auth()->id(),
            'description' => $this->description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $durationMinutes,
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
        $this->selected_date = $timeLog->start_time->format('Y-m-d');
        
        // Format the duration in a user-friendly way
        $minutes = $timeLog->duration_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            $this->duration_minutes = "{$hours}h{$mins}m";
        } elseif ($hours > 0) {
            $this->duration_minutes = "{$hours}h";
        } else {
            $this->duration_minutes = "{$mins}m";
        }
        
        $this->selectedTags = $timeLog->tags->pluck('id')->toArray();
    }
    
    public function findAndEditTimeLog($date, $projectId, $timerId = null)
    {
        // Find time logs for the specific date, project and timer
        $query = TimeLog::where('user_id', auth()->id())
            ->where('project_id', $projectId)
            ->whereDate('start_time', $date);
            
        if ($timerId) {
            $query->where('timer_id', $timerId);
        } else {
            $query->whereNull('timer_id');
        }
        
        // Get all logs for this combination
        $timeLogs = $query->get();
        
        if ($timeLogs->count() > 1) {
            // Multiple time logs found, show selection modal
            $this->timeLogSelectionOptions = $timeLogs->map(function($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description ?: 'No description',
                    'duration' => $this->formatDuration($log->duration_minutes),
                    'start_time' => $log->start_time->format('H:i'),
                    'end_time' => $log->end_time->format('H:i')
                ];
            })->toArray();
            
            $this->showTimeLogSelectionModal = true;
        } elseif ($timeLogs->count() == 1) {
            // Only one time log found, edit it directly
            $this->startEdit($timeLogs->first()->id);
        } else {
            // If no log exists, set up for creating a new one
            $this->createForDate($date);
            $this->project_id = $projectId;
        }
    }
    
    /**
     * Select a specific time log to edit from multiple options
     */
    public function selectTimeLogToEdit($timeLogId)
    {
        $this->showTimeLogSelectionModal = false;
        $this->timeLogSelectionOptions = [];
        $this->startEdit($timeLogId);
    }
    
    /**
     * Close the time log selection modal without selecting
     */
    public function closeTimeLogSelectionModal()
    {
        $this->showTimeLogSelectionModal = false;
        $this->timeLogSelectionOptions = [];
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
        
        // Parse duration string into minutes
        $durationMinutes = $this->parseDurationString($this->duration_minutes);
        
        // Use the selected date if it's been changed
        $start_time = Carbon::parse($this->selected_date)->startOfDay();
        $end_time = $start_time->copy()->addMinutes($durationMinutes);
        
        $timeLog->update([
            'project_id' => $this->project_id,
            'description' => $this->description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $durationMinutes,
        ]);

        $timeLog->tags()->sync($this->selectedTags);
        
        $this->cancelEdit();
        session()->flash('message', 'Time log updated successfully.');
    }

    public function deleteTimeLog($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $timeLog->delete();
        $this->confirmingDelete = null;
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
            
            // Group by timer and description
            $timerGroups = $projectLogs->groupBy(function($log) {
                // Group by timer_id and a sanitized version of the description
                // This ensures logs with the same timer but different descriptions are grouped separately
                $timerId = $log->timer_id ?? 'manual';
                $description = trim($log->description ?? '');
                return $timerId . '|' . $description;
            });
            
            foreach ($timerGroups as $timerKey => $timerLogs) {
                // Extract timer_id and description from the group key
                list($timerId, $description) = explode('|', $timerKey, 2);
                $timerId = $timerId === 'manual' ? null : $timerId;
                
                $timerName = $timerId ? ($timerLogs->first()->timer->name ?? 'Unnamed Timer') : 'Manual Entry';
                $timerTotal = 0;
                $dailyDurations = array_fill_keys(array_keys($weekDays), 0);
                $dailyDescriptions = array_fill_keys(array_keys($weekDays), $description);
                $dailyLogIds = array_fill_keys(array_keys($weekDays), null);
                
                // Calculate daily durations
                foreach ($timerLogs as $log) {
                    $logDate = Carbon::parse($log->start_time)->format('Y-m-d');
                    $dailyDurations[$logDate] = ($dailyDurations[$logDate] ?? 0) + $log->duration_minutes;
                    
                    // Store the log ID for this day (for editing)
                    // If multiple logs exist for this day, the selection modal will handle it
                    $dailyLogIds[$logDate] = $log->id;
                    
                    $timerTotal += $log->duration_minutes;
                }
                
                // Add description to timer name if it exists
                $displayName = $timerName;
                if (!empty($description)) {
                    $displayName .= ': ' . $description;
                }
                
                $timers[] = [
                    'id' => $timerId,
                    'name' => $displayName,
                    'originalName' => $timerName,
                    'description' => $description,
                    'daily' => $dailyDurations,
                    'dailyDescriptions' => $dailyDescriptions,
                    'dailyLogIds' => $dailyLogIds,
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
                return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
            }
            
            return $mins . 'm';
        }
    }
    
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
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }
    
    public function resetFilters()
    {
        $this->filterProject = null;
        $this->filterTag = null;
        $this->filterDateFrom = null;
        $this->filterDateTo = null;
        $this->searchQuery = '';
    }
    
    /**
     * This method is automatically called by Livewire when the duration_minutes property is updated
     */
    public function updatedDurationMinutes()
    {
        // This will trigger the display of the formatted duration in the UI
        // No need to do anything here as the template will show the formatted duration
    }
    
    public function setTimeFormat($format)
    {
        $this->timeFormat = $format;
    }
    
    public function confirmDelete($timeLogId)
    {
        $this->confirmingDelete = $timeLogId;
    }
    
    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }
    
    public function openQuickTimeModal($date = null, $projectId = null, $timerId = null)
    {
        $this->quickTimeDate = $date ?? now()->format('Y-m-d');
        $this->quickTimeProjectId = $projectId;
        $this->quickTimeTimerId = $timerId;
        $this->quickTimeDescription = null;
        $this->quickTimeDuration = 0;
        
        // Load timers
        $this->loadProjectTimers($projectId);
        
        $this->showQuickTimeModal = true;
    }
    
    public function loadProjectTimers($projectId = null)
    {
        $query = Timer::where('user_id', auth()->id());
        
        if ($projectId) {
            // If project is selected, show timers for that project and timers without a project
            $query->where(function($q) use ($projectId) {
                $q->where('project_id', $projectId)
                  ->orWhereNull('project_id');
            });
        }
        
        $this->quickTimeProjectTimers = $query->orderBy('name')->get();
    }
    
    public function closeQuickTimeModal()
    {
        $this->showQuickTimeModal = false;
    }
    
    public function updatedQuickTimeProjectId($value)
    {
        $this->loadProjectTimers($value);
        $this->quickTimeTimerId = null; // Reset timer selection when project changes
    }
    
    public function addQuickTime($minutes)
    {
        $this->quickTimeDuration += $minutes;
    }
    
    public function setQuickTime($minutes)
    {
        $this->quickTimeDuration = $minutes;
    }
    
    public function saveQuickTime()
    {
        if ($this->quickTimeDuration <= 0) {
            return;
        }
        
        // Create start and end times from the selected date
        $start_time = Carbon::parse($this->quickTimeDate)->startOfDay();
        $end_time = $start_time->copy()->addMinutes($this->quickTimeDuration);
        
        $timeLog = TimeLog::create([
            'project_id' => $this->quickTimeProjectId,
            'timer_id' => $this->quickTimeTimerId,
            'user_id' => auth()->id(),
            'description' => $this->quickTimeDescription,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $this->quickTimeDuration,
        ]);
        
        $this->closeQuickTimeModal();
        session()->flash('message', 'Time log created successfully.');
    }
    
    public function render()
    {
        $query = TimeLog::where('user_id', auth()->id());
        
        // Apply filters
        if ($this->filterProject) {
            $query->where('project_id', $this->filterProject);
        }
        
        if ($this->filterTag) {
            $query->whereHas('tags', function($q) {
                $q->where('tags.id', $this->filterTag);
            });
        }
        
        if ($this->filterDateFrom) {
            $query->where('start_time', '>=', $this->filterDateFrom . ' 00:00:00');
        }
        
        if ($this->filterDateTo) {
            $query->where('start_time', '<=', $this->filterDateTo . ' 23:59:59');
        }
        
        if ($this->searchQuery) {
            $query->where(function($q) {
                $q->where('description', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('project', function($q) {
                      $q->where('name', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }
        
        // Apply sorting
        if ($this->sortField === 'project') {
            $query->join('projects', 'time_logs.project_id', '=', 'projects.id')
                  ->orderBy('projects.name', $this->sortDirection)
                  ->select('time_logs.*');
        } elseif ($this->sortField === 'duration') {
            $query->orderBy('duration_minutes', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        
        return view('livewire.time-logs', [
            'timeLogs' => $query->with(['project', 'tags', 'timer'])->get(),
            'projects' => Project::where('user_id', auth()->id())->get(),
            'tags' => Tag::where('user_id', auth()->id())->get(),
            'allTags' => Tag::where('user_id', auth()->id())->get(),
        ]);
    }
}
