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
    public $savedTimersSearch = '';
    public Collection $existingTimers;
    public $suggestions = [];
    public $notification = null;
    public $showNotification = false;
    public $notificationType = 'success';
    public $showLongRunningTimerModal = false;
    public $showEditTimerModal = false;
    public $longRunningTimerId = null;
    public $longRunningTimerStartTime = null;
    public $customEndTime = null;
    public $actualHoursWorked = null;
    public $editingTimerId = null;
    public $editingTimerName = null;
    public $editingTimerDescription = null;
    public $editingTimerProjectName = null;
    public $editingTimerTagInput = null;
    
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
    
    /**
     * Cancel a timer without saving any time log
     */
    public function cancelTimer($timerId)
    {
        $timer = Timer::with('timeLogs')->findOrFail($timerId);
        
        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();
        
        if ($latestLog && !$latestLog->end_time) {
            // Delete the time log entry
            $latestLog->delete();
        }
        
        // Mark timer as not running
        $timer->is_running = false;
        $timer->save();
        
        $this->showNotification('Timer cancelled', 'info');
        $this->dispatch('timerStopped', ['timerId' => $timerId]);
    }
    
    /**
     * Stop a timer and open the edit modal
     */
    public function stopAndEditTimer($timerId)
    {
        $timer = Timer::with(['timeLogs', 'tags', 'project'])->findOrFail($timerId);
        
        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();
        
        if ($latestLog && !$latestLog->end_time) {
            // Stop the timer first
            $now = now();
            $latestLog->end_time = $now;
            $latestLog->duration_minutes = $latestLog->start_time->diffInMinutes($now);
            $latestLog->save();
        }
        
        // Mark timer as not running
        $timer->is_running = false;
        $timer->save();
        
        // Set up editing properties
        $this->editingTimerId = $timer->id;
        $this->editingTimerName = $timer->name;
        $this->editingTimerDescription = $timer->description;
        $this->editingTimerProjectName = $timer->project ? $timer->project->name : '';
        $this->editingTimerTagInput = $timer->tags->pluck('name')->implode(', ');
        
        // Show the edit modal
        $this->showEditTimerModal = true;
        
        $this->dispatch('timerStopped', ['timerId' => $timerId]);
    }
    
    /**
     * Save the edited timer details
     */
    public function saveEditedTimer()
    {
        $timer = Timer::findOrFail($this->editingTimerId);
        
        // Find or create project if name is provided
        $project_id = null;
        if ($this->editingTimerProjectName) {
            $project = Project::firstOrCreate(
                ['name' => $this->editingTimerProjectName, 'user_id' => auth()->id()],
                ['description' => 'Project created from timer']
            );
            $project_id = $project->id;
        }
        
        // Update timer details
        $timer->update([
            'name' => $this->editingTimerName,
            'description' => $this->editingTimerDescription,
            'project_id' => $project_id,
        ]);
        
        // Process tags
        if ($this->editingTimerTagInput) {
            $tagNames = collect(explode(',', $this->editingTimerTagInput))
                ->map(fn($name) => trim($name))
                ->filter();
                
            $tags = $tagNames->map(function($name) {
                return Tag::findOrCreateForUser($name, auth()->id());
            });
            
            $timer->tags()->sync($tags->pluck('id'));
            
            // If we have a project, also attach the tags to it
            if ($project_id) {
                $project->tags()->syncWithoutDetaching($tags->pluck('id'));
            }
        } else {
            $timer->tags()->detach();
        }
        
        // Close the modal
        $this->closeEditTimerModal();
        
        $this->showNotification('Timer updated successfully', 'success');
    }
    
    /**
     * Close the edit timer modal
     */
    public function closeEditTimerModal()
    {
        $this->showEditTimerModal = false;
        $this->editingTimerId = null;
        $this->editingTimerName = null;
        $this->editingTimerDescription = null;
        $this->editingTimerProjectName = null;
        $this->editingTimerTagInput = null;
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
    
    /**
     * Ensure the start time is properly formatted for JavaScript
     *
     * @param \App\Models\Timer $timer
     * @return string
     */
    public function getFormattedStartTimeForJs($timer)
    {
        if (!$timer->latestTimeLog) {
            return now()->toIso8601String();
        }
        
        return $timer->latestTimeLog->start_time->toIso8601String();
    }
    
    public function getFormattedStartTime($timer)
    {
        if (!$timer->latestTimeLog) {
            return 'Not started';
        }
        
        return $timer->latestTimeLog->start_time->format('g:i A');
    }
    
    /**
     * Edit a timer that is not currently running
     */
    public function editTimer($timerId)
    {
        $timer = Timer::with(['tags', 'project'])->findOrFail($timerId);
        
        // Set up editing properties
        $this->editingTimerId = $timer->id;
        $this->editingTimerName = $timer->name;
        $this->editingTimerDescription = $timer->description;
        $this->editingTimerProjectName = $timer->project ? $timer->project->name : '';
        $this->editingTimerTagInput = $timer->tags->pluck('name')->implode(', ');
        
        // Show the edit modal
        $this->showEditTimerModal = true;
    }
    
    /**
     * Restart a timer that is not currently running
     */
    public function restartTimer($timerId)
    {
        $timer = Timer::findOrFail($timerId);
        
        // Mark timer as running
        $timer->is_running = true;
        $timer->save();
        
        // Create a new time log
        TimeLog::create([
            'timer_id' => $timer->id,
            'user_id' => auth()->id(),
            'project_id' => $timer->project_id,
            'start_time' => now(),
            'description' => $timer->description ?: null,
        ]);
        
        $this->showNotification('Timer restarted successfully', 'success');
        $this->dispatch('timerStarted');
    }
    
    /**
     * Delete a timer
     */
    public function deleteTimer($timerId)
    {
        $timer = Timer::findOrFail($timerId);
        
        // Delete the timer
        $timer->delete();
        
        $this->showNotification('Timer deleted successfully', 'success');
    }
    
    /**
     * Filter saved timers based on search input
     */
    public function updatedSavedTimersSearch()
    {
        // This method is automatically called when $savedTimersSearch is updated
        // We don't need to do anything here as the filtering happens in the render method
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
        
        // Get all timers for the user
        $allTimers = Timer::with(['project', 'tags', 'latestTimeLog'])
            ->where('user_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Separate running and non-running timers
        $runningTimers = $allTimers->where('is_running', true);
        $savedTimers = $allTimers->where('is_running', false);
        
        // Filter saved timers if search is provided
        if (!empty($this->savedTimersSearch)) {
            $search = strtolower($this->savedTimersSearch);
            $savedTimers = $savedTimers->filter(function($timer) use ($search) {
                // Search in timer name
                if (str_contains(strtolower($timer->name), $search)) {
                    return true;
                }
                
                // Search in timer description
                if ($timer->description && str_contains(strtolower($timer->description), $search)) {
                    return true;
                }
                
                // Search in project name
                if ($timer->project && str_contains(strtolower($timer->project->name), $search)) {
                    return true;
                }
                
                // Search in tags
                foreach ($timer->tags as $tag) {
                    if (str_contains(strtolower($tag->name), $search)) {
                        return true;
                    }
                }
                
                return false;
            });
        }
            
        return view('livewire.timers', [
            'recentTags' => $recentTags,
            'runningTimers' => $runningTimers,
            'savedTimers' => $savedTimers,
        ]);
    }
}
