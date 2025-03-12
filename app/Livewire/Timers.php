<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Timer;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Timers extends Component
{
    public $project_name = '';
    public $name;
    public $description;
    public $tag_input = '';
    public $search = '';
    public Collection $existingTimers;
    public $suggestions = [];
    public $notification = null;
    public $showNotification = false;
    public $notificationType = 'success';
    public $showLongRunningTimerModal = false;
    public $longRunningTimerId = null;
    public $longRunningTimerStartTime = null;
    public $customEndTime = null;
    public $actualHoursWorked = null;
    
    protected $rules = [
        'project_name' => 'nullable|string|max:255',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'tag_input' => 'nullable|string',
        'customEndTime' => 'nullable|date',
        'actualHoursWorked' => 'nullable|numeric|min:0.25',
    ];

    protected $listeners = [
        'timerStarted' => 'handleTimerStarted',
        'timerStopped' => 'handleTimerStopped',
    ];

    public function mount()
    {
        // Initialize collections
        $this->existingTimers = collect();
        $this->suggestions = [
            'projects' => [],
            'tags' => []
        ];
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->existingTimers = Timer::with(['project', 'tags'])
                ->where('user_id', auth()->id())
                ->where('name', 'like', '%' . $this->search . '%')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            $this->existingTimers = collect();
        }
    }

    public function updatedProjectName()
    {
        if (strlen($this->project_name) >= 2) {
            $this->suggestions['projects'] = Project::with('tags')
                ->where('user_id', auth()->id())
                ->where('name', 'like', '%' . $this->project_name . '%')
                ->limit(5)
                ->get();
        } else {
            $this->suggestions['projects'] = [];
        }
    }

    public function updatedTagInput()
    {
        // Extract the last tag being typed
        $tags = collect(explode(',', $this->tag_input));
        $lastTag = trim($tags->last());
        
        if (strlen($lastTag) >= 2) {
            $this->suggestions['tags'] = Tag::where('user_id', auth()->id())
                ->where('name', 'like', '%' . $lastTag . '%')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            $this->suggestions['tags'] = [];
        }
    }

    public function selectProject($projectId)
    {
        $project = Project::find($projectId);
        if ($project) {
            $this->project_name = $project->name;
            // If the project has tags, add them to the tag input
            if ($project->tags->isNotEmpty()) {
                $projectTags = $project->tags->pluck('name')->implode(', ');
                $this->tag_input = $this->tag_input
                    ? $this->tag_input . ', ' . $projectTags
                    : $projectTags;
            }
        }
        $this->suggestions['projects'] = [];
    }
    
    public function selectTag($tagName)
    {
        // Extract all tags except the last one (which is being typed)
        $tags = collect(explode(',', $this->tag_input))
            ->map(fn($tag) => trim($tag))
            ->filter(fn($tag) => !empty($tag));
        
        // Remove the last tag (which is being typed)
        if ($tags->count() > 0) {
            $tags->pop();
        }
        
        // Add the selected tag
        $tags->push($tagName);
        
        // Update the tag input
        $this->tag_input = $tags->implode(', ') . ', ';
        
        // Clear suggestions
        $this->suggestions['tags'] = [];
    }
    
    public function useExistingTimer($timerId)
    {
        $timer = Timer::with(['tags', 'project'])->findOrFail($timerId);
        $this->name = $timer->name;
        $this->description = $timer->description;
        $this->project_name = $timer->project?->name ?? '';
        $this->tag_input = $timer->tags->pluck('name')->implode(', ');
        $this->search = '';
        $this->existingTimers = collect();
        
        $this->showNotification('Timer loaded successfully', 'info');
    }

    public function startTimer()
    {
        $this->validate();
        
        // Find or create project if name is provided
        $project_id = null;
        if ($this->project_name) {
            $project = Project::firstOrCreate(
                ['name' => $this->project_name, 'user_id' => auth()->id()],
                ['description' => 'Project created from timer']
            );
            $project_id = $project->id;
        }
        
        // Create new timer
        $timer = Timer::create([
            'user_id' => auth()->id(),
            'project_id' => $project_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_running' => true,
        ]);
        
        // Process tags
        if ($this->tag_input) {
            $tagNames = collect(explode(',', $this->tag_input))
                ->map(fn($name) => trim($name))
                ->filter();
                
            $tags = $tagNames->map(function($name) {
                return Tag::findOrCreateForUser($name, auth()->id());
            });
            
            $timer->tags()->attach($tags->pluck('id'));
            
            // If we have a project, also attach the tags to it
            if ($project_id) {
                $project->tags()->syncWithoutDetaching($tags->pluck('id'));
            }
        }
        
        // Create time log
        TimeLog::create([
            'timer_id' => $timer->id,
            'user_id' => auth()->id(),
            'project_id' => $project_id,
            'start_time' => now(),
            'description' => $this->description ?: null,
        ]);
        
        $this->showNotification('Timer started successfully', 'success');
        $this->dispatch('timerStarted');
        
        // Reset form, making sure to initialize existingTimers as a collection
        $this->reset(['name', 'description', 'project_name', 'tag_input', 'search']);
        $this->existingTimers = collect();
        $this->suggestions = ['projects' => [], 'tags' => []];
    }
    
    public function stopTimer($timerId)
    {
        $timer = Timer::with('timeLogs')->findOrFail($timerId);
        
        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();
        
        if ($latestLog && !$latestLog->end_time) {
            $startTime = $latestLog->start_time;
            $now = now();
            $hoursDiff = $startTime->diffInHours($now);
            $isYesterday = $startTime->format('Y-m-d') !== $now->format('Y-m-d');
            
            // Check if timer has been running for 8+ hours or was started yesterday
            if ($hoursDiff >= 8 || $isYesterday) {
                // Show modal for user to choose how to handle the long-running timer
                $this->longRunningTimerId = $timerId;
                $this->longRunningTimerStartTime = $startTime;
                $this->showLongRunningTimerModal = true;
                return;
            }
            
            // For normal timers, just stop with current time
            $this->completeTimerStop($timerId, $now);
        } else {
            // No active time log, just mark timer as stopped
            $timer->is_running = false;
            $timer->save();
            $this->showNotification('Timer stopped successfully', 'info');
            $this->dispatch('timerStopped', ['timerId' => $timerId]);
        }
    }
    
    public function completeTimerStop($timerId, $endTime)
    {
        $timer = Timer::with('timeLogs')->findOrFail($timerId);
        $timer->is_running = false;
        $timer->save();
        
        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();
        
        if ($latestLog && !$latestLog->end_time) {
            // Update the existing log with end time
            $latestLog->end_time = $endTime;
            $latestLog->duration_minutes = $latestLog->start_time->diffInMinutes($latestLog->end_time);
            $latestLog->save();
        }
        
        $this->showNotification('Timer stopped successfully', 'info');
        $this->dispatch('timerStopped', ['timerId' => $timerId]);
        
        // Reset modal properties
        $this->resetLongRunningTimerModal();
    }
    
    public function handleTimerStarted()
    {
        // This method can be used for additional actions when a timer is started
        // For now, it's just a placeholder for the event listener
    }
    
    public function handleTimerStopped($data)
    {
        // This method can be used for additional actions when a timer is stopped
        // For now, it's just a placeholder for the event listener
    }
    
    public function resetLongRunningTimerModal()
    {
        $this->showLongRunningTimerModal = false;
        $this->longRunningTimerId = null;
        $this->longRunningTimerStartTime = null;
        $this->customEndTime = null;
        $this->actualHoursWorked = null;
    }
    
    public function cancelLongRunningTimerStop()
    {
        $this->resetLongRunningTimerModal();
        $this->showNotification('Timer stop cancelled', 'info');
    }
    
    public function useCustomEndTime()
    {
        if (!$this->customEndTime) {
            $this->showNotification('Please select a valid end time', 'error');
            return;
        }
        
        $endTime = Carbon::parse($this->customEndTime);
        $startTime = $this->longRunningTimerStartTime;
        
        // Validate that end time is after start time
        if ($endTime->isBefore($startTime)) {
            $this->showNotification('End time must be after start time', 'error');
            return;
        }
        
        $this->completeTimerStop($this->longRunningTimerId, $endTime);
    }
    
    public function useActualHoursWorked()
    {
        if (!$this->actualHoursWorked || !is_numeric($this->actualHoursWorked) || $this->actualHoursWorked <= 0) {
            $this->showNotification('Please enter a valid number of hours', 'error');
            return;
        }
        
        $startTime = $this->longRunningTimerStartTime;
        $endTime = (clone $startTime)->addHours((float)$this->actualHoursWorked);
        
        // If calculated end time is in the future, cap it at current time
        if ($endTime->isAfter(now())) {
            $endTime = now();
        }
        
        $this->completeTimerStop($this->longRunningTimerId, $endTime);
    }
    
    public function useCurrentTime()
    {
        $this->completeTimerStop($this->longRunningTimerId, now());
    }
    
    public function showNotification($message, $type = 'success')
    {
        $this->notification = $message;
        $this->notificationType = $type;
        $this->showNotification = true;
        
        // Auto-hide notification after 3 seconds
        $this->dispatch('hideNotification');
    }
    
    public function hideNotification()
    {
        $this->showNotification = false;
    }
    
    public function getContrastColor($hexColor)
    {
        $hexColor = ltrim($hexColor, '#');
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
        return ($luminance > 0.5) ? '#000000' : '#FFFFFF';
    }
    
    public function getTimerDuration($timer)
    {
        if (!$timer->latestTimeLog) {
            return '00:00:00';
        }
        
        $startTime = $timer->latestTimeLog->start_time;
        $endTime = $timer->latestTimeLog->end_time ?? now();
        
        $diff = $startTime->diffInSeconds($endTime);
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $seconds = $diff % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    public function getFormattedStartTime($timer)
    {
        if (!$timer->latestTimeLog) {
            return 'Not started';
        }
        
        return $timer->latestTimeLog->start_time->format('g:i A');
    }
    
    public function render()
    {
        // Cache recent tags for 5 minutes to improve performance
        $recentTags = Cache::remember('user.' . auth()->id() . '.recent_tags', 300, function () {
            return Tag::where('user_id', auth()->id())
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        });
            
        return view('livewire.timers', [
            'recentTags' => $recentTags,
            'runningTimers' => Timer::with(['project', 'tags', 'latestTimeLog'])
                ->where('user_id', auth()->id())
                ->where('is_running', true)
                ->orderBy('updated_at', 'desc')
                ->get(),
        ]);
    }
}
