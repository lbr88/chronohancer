<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use App\Models\TimeLog;
use App\Models\Timer;
use App\Models\TimerDescription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Timers extends Component
{
    public $project_name = '';

    public $project_id = null;

    public $name;

    public $description;

    public $timerDescriptionId;

    public $tag_input = '';

    public $editingTimerDescriptionId;

    public $search = '';

    public $savedTimersSearch = '';

    public $suggestions = [];

    public $notification = null;

    public $showNotification = false;

    public $notificationType = 'success';

    public $showLongRunningTimerModal = false;

    public $showEditTimerModal = false;

    public $showNewTimerModal = false;

    public $showRestartTimerModal = false;

    public $restartTimerId = null;

    public $restartTimerName = null;

    public $restartTimerDescriptionId = null;

    public $timerStartTime = null;

    public $longRunningTimerId = null;

    public $longRunningTimerStartTime = null;

    public $customEndTime = null;

    public $actualHoursWorked = null;

    public $editingTimerId = null;

    public $editingTimerName = null;

    public $editingTimerDescription = null;

    public $editingTimerProjectName = null;

    public $editingTimerTagInput = null;

    public $editingTimeLogId = null;

    public $editingDurationHours = 0;

    public $editingDurationMinutes = 0;

    public $editingDurationHuman = '';

    public $timeFormat;

    public $jiraKey = '';

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
        'timerPaused' => 'handleTimerPaused',
        'refresh-timers' => '$refresh',
        'project-selected' => 'handleProjectSelected',
        'tags-updated' => 'handleTagsUpdated',
        'time-input-changed' => 'handleTimeInputChanged',
        'description-selected' => 'handleDescriptionSelected',
        'timer-selected' => 'handleTimerSelected',
        'unified-timer-selected' => 'handleUnifiedTimerSelected',
        'jira-issue-selected' => 'handleJiraIssueSelected',
    ];

    public function mount()
    {
        // Initialize collections
        $this->suggestions = [
            'projects' => [],
            'tags' => [],
        ];

        // Load user's time format preference
        $this->timeFormat = Auth::user()->time_format ?? 'human';
    }

    /**
     * Handle Jira issue selection from the JiraSearch component
     */
    public function handleJiraIssueSelected($data)
    {
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['jiraKey'])) {
            $this->jiraKey = $data['jiraKey'];
        }
    }

    /**
     * Open the new timer modal and store the current time
     */
    public function openNewTimerModal()
    {
        $this->timerStartTime = now();
        $this->showNewTimerModal = true;

        // Dispatch an event to notify JavaScript that the modal has been opened
        // and the start time has been captured
        $this->dispatch('new-timer-modal-opened', [
            'startTime' => $this->timerStartTime->toIso8601String(),
        ]);
    }

    /**
     * Close the new timer modal
     */
    public function closeNewTimerModal()
    {
        $this->reset(['name', 'description', 'timerDescriptionId', 'project_name', 'project_id', 'tag_input', 'search', 'jiraKey']);
        $this->suggestions = ['projects' => [], 'tags' => []];
        $this->showNewTimerModal = false;
    }

    // Search functionality is now handled by the UnifiedTimerSelector component

    /**
     * Handle project selection from the project selector component
     */
    public function handleProjectSelected($data)
    {
        if (isset($data['id'])) {
            $this->project_id = $data['id'];
            $project = Project::find($data['id']);
            if ($project) {
                $this->project_name = $project->name;

                // If the project has tags, add them to the tag input
                if ($project->tags->isNotEmpty()) {
                    $projectTags = $project->tags->pluck('name')->implode(', ');
                    $this->tag_input = $this->tag_input
                        ? $this->tag_input.', '.$projectTags
                        : $projectTags;
                }
            }
        }
    }

    public function updatedProjectName()
    {
        if (strlen($this->project_name) >= 2) {
            $this->suggestions['projects'] = Project::with('tags')
                ->where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where('name', 'like', '%'.$this->project_name.'%')
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
            $this->suggestions['tags'] = Tag::where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where('name', 'like', '%'.$lastTag.'%')
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
                    ? $this->tag_input.', '.$projectTags
                    : $projectTags;
            }
        }
        $this->suggestions['projects'] = [];
    }

    public function selectTag($tagName)
    {
        // Extract all tags except the last one (which is being typed)
        $tags = collect(explode(',', $this->tag_input))
            ->map(fn ($tag) => trim($tag))
            ->filter(fn ($tag) => ! empty($tag));

        // Remove the last tag (which is being typed)
        if ($tags->count() > 0) {
            $tags->pop();
        }

        // Add the selected tag
        $tags->push($tagName);

        // Update the tag input
        $this->tag_input = $tags->implode(', ').', ';

        // Clear suggestions
        $this->suggestions['tags'] = [];
    }

    // This method is no longer needed as timer selection is handled by the UnifiedTimerSelector component

    public function startTimer()
    {
        $this->validate();

        // Find or create project if project_id or name is provided, or use default project
        $project = null;
        if ($this->project_id) {
            $project = Project::find($this->project_id);
        } elseif ($this->project_name) {
            $project = Project::firstOrCreate(
                ['name' => $this->project_name, 'user_id' => Auth::id(), 'workspace_id' => app('current.workspace')->id],
                ['description' => 'Project created from timer']
            );
        }

        if (! $project) {
            // Always use the default project if no project is found
            $project = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
        }
        $project_id = $project->id;

        // Create new timer
        $timer = Timer::create([
            'user_id' => Auth::id(),
            'project_id' => $project_id,
            'name' => $this->name,
            'is_running' => true,
            'workspace_id' => app('current.workspace')->id,
            'jira_key' => $this->jiraKey ?: null,
        ]);

        // Create a timer description if provided
        $timerDescriptionId = null;
        if (! empty($this->description)) {
            $timerDescription = TimerDescription::create([
                'description' => $this->description,
                'timer_id' => $timer->id,
                'user_id' => Auth::id(),
                'workspace_id' => app('current.workspace')->id,
            ]);
            $timerDescriptionId = $timerDescription->id;
        } elseif ($this->timerDescriptionId) {
            // Use existing timer description
            $timerDescriptionId = $this->timerDescriptionId;
        }

        // Process tags
        if ($this->tag_input) {
            $tagNames = collect(explode(',', $this->tag_input))
                ->map(fn ($name) => trim($name))
                ->filter();

            $tags = $tagNames->map(function ($name) {
                return Tag::findOrCreateForUser($name, Auth::id(), app('current.workspace')->id);
            });

            // Use unique() to prevent duplicate tag IDs
            $timer->tags()->attach($tags->pluck('id')->unique());

            // If we have a project, also attach the tags to it
            if ($project_id) {
                $project->tags()->syncWithoutDetaching($tags->pluck('id'));
            }
        }

        // Use the stored start time if available, otherwise use current time
        $startTime = $this->timerStartTime ?: now();

        // Create time log
        $timeLog = TimeLog::create([
            'timer_id' => $timer->id,
            'timer_description_id' => $timerDescriptionId,
            'user_id' => Auth::id(),
            'start_time' => $startTime,
            'description' => $this->description ?: null, // Keep for backward compatibility
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Refresh the timer to include the latest time log
        $timer->refresh();

        $this->showNotification('Timer started successfully', 'success');
        $this->dispatch('timerStarted', ['timerId' => $timer->id, 'startTime' => $timeLog->start_time->toIso8601String()]);

        // Reset form and close modal
        $this->closeNewTimerModal();
        $this->timerStartTime = null;
    }

    public function stopTimer($timerId)
    {
        $timer = Timer::with('timeLogs')->findOrFail($timerId);

        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();

        if ($latestLog && ! $latestLog->end_time) {
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

        if ($latestLog && ! $latestLog->end_time) {
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
    /**
     * Parse a human-readable duration string into minutes
     *
     * @param  string  $durationString  Human-readable duration (e.g., "1h 30m", "45m", "2h")
     * @return int Total minutes
     */
    public function parseHumanDuration($durationString)
    {
        $totalMinutes = 0;

        // Match hours
        if (preg_match('/(\d+)h/', $durationString, $matches)) {
            $totalMinutes += (int) $matches[1] * 60;
        }

        // Match minutes
        if (preg_match('/(\d+)m/', $durationString, $matches)) {
            $totalMinutes += (int) $matches[1];
        }

        return $totalMinutes;
    }

    /**
     * Format minutes into a human-readable duration string
     *
     * @param  int  $minutes  Total minutes
     * @return string Human-readable duration (e.g., "1h 30m")
     */
    public function formatHumanDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        $result = '';
        if ($hours > 0) {
            $result .= $hours.'h';
            if ($mins > 0) {
                $result .= ' '.$mins.'m';
            }
        } else {
            if ($mins > 0) {
                $result .= $mins.'m';
            } else {
                $result = '0m';
            }
        }

        return $result;
    }

    public function stopAndEditTimer($timerId)
    {
        $timer = Timer::with(['timeLogs', 'tags', 'project'])->findOrFail($timerId);

        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();

        // Store the time log ID and calculated duration for editing
        $this->editingTimeLogId = null;
        $this->editingDurationHours = 0;
        $this->editingDurationMinutes = 0;
        $this->editingDurationHuman = '';

        if ($latestLog && ! $latestLog->end_time) {
            // Calculate the current duration but don't save it yet
            $now = now();
            $durationMinutes = $latestLog->start_time->diffInMinutes($now);

            if ($durationMinutes > 0) {
                // Store the time log ID and calculated duration for editing
                $this->editingTimeLogId = $latestLog->id;
                $this->editingDurationHours = floor($durationMinutes / 60);
                $this->editingDurationMinutes = $durationMinutes % 60;
                $this->editingDurationHuman = $this->formatHumanDuration($durationMinutes);
            } else {
                // If duration is 0 minutes, we'll still allow editing but start with 0
                $this->editingTimeLogId = $latestLog->id;
                $this->editingDurationHours = 0;
                $this->editingDurationMinutes = 0;
                $this->editingDurationHuman = '0m';
            }

            // Mark timer as not running but don't save the time log yet
            $timer->is_running = false;
            $timer->save();
        }

        // Set up editing properties
        $this->editingTimerId = $timer->id;
        $this->editingTimerName = $timer->name;

        // Get the latest description if available
        if ($timer->latestDescription) {
            $this->editingTimerDescription = $timer->latestDescription->description;
            $this->editingTimerDescriptionId = $timer->latestDescription->id;
        } else {
            $this->editingTimerDescription = '';
            $this->editingTimerDescriptionId = null;
        }

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
        $wasRunning = $timer->is_running;

        // Find or create project if name is provided, or use default project
        $project = null;
        if ($this->editingTimerProjectName) {
            $project = Project::firstOrCreate(
                ['name' => $this->editingTimerProjectName, 'user_id' => Auth::id(), 'workspace_id' => app('current.workspace')->id],
                ['description' => 'Project created from timer']
            );
        } else {
            // Always use the default project if no project name is provided
            $project = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
        }
        $project_id = $project->id;

        // Update timer details
        $timer->update([
            'name' => $this->editingTimerName,
            'project_id' => $project_id,
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Create or update timer description
        if (! empty($this->editingTimerDescription)) {
            if ($this->editingTimerDescriptionId) {
                // Update existing description
                $timerDescription = TimerDescription::find($this->editingTimerDescriptionId);
                if ($timerDescription) {
                    $timerDescription->update([
                        'description' => $this->editingTimerDescription,
                    ]);
                }
            } else {
                // Create new description
                $timerDescription = TimerDescription::create([
                    'description' => $this->editingTimerDescription,
                    'timer_id' => $timer->id,
                    'user_id' => Auth::id(),
                    'workspace_id' => app('current.workspace')->id,
                ]);
                $this->editingTimerDescriptionId = $timerDescription->id;
            }
        }

        // Process tags
        if ($this->editingTimerTagInput) {
            $tagNames = collect(explode(',', $this->editingTimerTagInput))
                ->map(fn ($name) => trim($name))
                ->filter();

            $tags = $tagNames->map(function ($name) {
                return Tag::findOrCreateForUser($name, Auth::id(), app('current.workspace')->id);
            });

            $timer->tags()->sync($tags->pluck('id'));

            // If we have a project, also attach the tags to it
            if ($project_id) {
                $project->tags()->syncWithoutDetaching($tags->pluck('id'));
            }
        } else {
            $timer->tags()->detach();
        }

        // Handle time log update if we have a time log ID
        if ($this->editingTimeLogId) {
            $timeLog = TimeLog::find($this->editingTimeLogId);

            if ($timeLog) {
                // Calculate total minutes from human duration input
                $totalMinutes = 0;

                if (! empty($this->editingDurationHuman)) {
                    $totalMinutes = $this->parseHumanDuration($this->editingDurationHuman);
                } else {
                    // Fallback to hours and minutes inputs if human format is empty
                    $totalMinutes = ($this->editingDurationHours * 60) + $this->editingDurationMinutes;
                }

                if ($totalMinutes > 0) {
                    // Calculate the end time based on start time + duration
                    $endTime = (clone $timeLog->start_time)->addMinutes($totalMinutes);

                    // Update the time log with the new duration and end time
                    $timeLog->update([
                        'end_time' => $endTime,
                        'duration_minutes' => (int) $totalMinutes, // Cast to integer to avoid decimal values
                        'timer_description_id' => $this->editingTimerDescriptionId,
                        'description' => $this->editingTimerDescription ?: null, // Keep for backward compatibility
                        'workspace_id' => app('current.workspace')->id,
                    ]);

                    // Dispatch event to update the daily progress bar
                    $this->dispatch('timeLogSaved');
                } else {
                    // If duration is 0 minutes, delete the time log
                    $timeLog->delete();
                    $this->showNotification('Timer had 0 minutes and was not saved', 'info');
                }
            }
        }
        // Update the latest time log's project_id if the timer is running
        elseif ($wasRunning) {
            $latestLog = $timer->timeLogs()->latest()->first();
            if ($latestLog && ! $latestLog->end_time) {
                $latestLog->update([
                    'timer_description_id' => $this->editingTimerDescriptionId,
                    'description' => $this->editingTimerDescription ?: null, // Keep for backward compatibility
                    'workspace_id' => app('current.workspace')->id,
                ]);
            }
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
        $this->editingTimerDescriptionId = null;
        $this->editingTimerProjectName = null;
        $this->editingTimerTagInput = null;
        $this->editingTimeLogId = null;
        $this->editingDurationHours = 0;
        $this->editingDurationMinutes = 0;
        $this->editingDurationHuman = '';
    }

    public function completeTimerStop($timerId, $endTime)
    {
        $timer = Timer::with('timeLogs')->findOrFail($timerId);
        $timer->is_running = false;
        $timer->save();

        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();

        if ($latestLog && ! $latestLog->end_time) {
            // Calculate duration in minutes
            $durationMinutes = $latestLog->start_time->diffInMinutes($endTime);

            // Only save the time log if duration is greater than 0 minutes
            if ($durationMinutes > 0) {
                // Update the existing log with end time
                $latestLog->end_time = $endTime;
                $latestLog->duration_minutes = (int) $durationMinutes; // Cast to integer to avoid decimal values
                $latestLog->save();

                // Dispatch event to update the daily progress bar
                $this->dispatch('timeLogSaved');
            } else {
                // If duration is 0 minutes, delete the time log instead of saving it
                $latestLog->delete();
                $this->showNotification('Timer had 0 minutes and was not saved', 'info');
                $this->dispatch('timerStopped', ['timerId' => $timerId]);
                $this->resetLongRunningTimerModal();

                return;
            }
        }

        $this->showNotification('Timer stopped successfully', 'info');
        $this->dispatch('timerStopped', ['timerId' => $timerId]);

        // Reset modal properties
        $this->resetLongRunningTimerModal();
    }

    public function handleTimerStarted($data = null)
    {
        // This method can be used for additional actions when a timer is started
        // The $data parameter may contain timerId and startTime
        if ($data && isset($data['timerId'])) {
            $this->log("Timer started/restarted: Timer ID {$data['timerId']}");
        }
    }

    /**
     * Log debug messages to the console
     */
    private function log($message)
    {
        if (config('app.debug')) {
            logger($message);
        }
    }

    public function handleTimerStopped($data)
    {
        // This method can be used for additional actions when a timer is stopped
        // For now, it's just a placeholder for the event listener
    }

    /**
     * Handle timer paused event
     */
    public function handleTimerPaused($data)
    {
        // This method can be used for additional actions when a timer is paused
        if ($data && isset($data['timerId'])) {
            $this->log("Timer paused: Timer ID {$data['timerId']}");
        }
    }

    /**
     * Handle tag updates from the tag selector component
     */
    public function handleTagsUpdated($selectedTags)
    {
        // Convert the selected tags to a comma-separated string for the tag_input field
        $tags = Tag::whereIn('id', $selectedTags)->get();
        $this->tag_input = $tags->pluck('name')->implode(', ');
    }

    /**
     * Handle description selection from the timer description selector component
     */
    public function handleDescriptionSelected($data)
    {
        if (isset($data['id'])) {
            $this->timerDescriptionId = $data['id'];
            $this->description = $data['description'];
        }
    }

    /**
     * Handle timer selection from a timer selector component
     */
    public function handleTimerSelected($data)
    {
        if (isset($data['id'])) {
            $timer = Timer::find($data['id']);
            if ($timer) {
                $this->name = $timer->name;
                // Don't set description here, as we want to select or create a new one
                $this->project_id = $timer->project_id;
                $this->project_name = $timer->project ? $timer->project->name : '';

                // Load tags
                if ($timer->tags->isNotEmpty()) {
                    $this->tag_input = $timer->tags->pluck('name')->implode(', ');
                }
            }
        }
    }

    /**
     * Handle unified timer selection from the UnifiedTimerSelector component
     */
    public function handleUnifiedTimerSelected($data)
    {
        // Set timer data
        if (isset($data['timerId'])) {
            $this->name = $data['timerName'] ?? '';
        }

        // Set project data
        if (isset($data['projectId'])) {
            $this->project_id = $data['projectId'];
            $this->project_name = $data['projectName'] ?? '';
        }

        // Set description data
        if (isset($data['timerDescriptionId'])) {
            $this->timerDescriptionId = $data['timerDescriptionId'];
            $this->description = $data['description'] ?? '';
        }

        // If we have a timer ID, load the tags
        if (isset($data['timerId']) && $data['timerId']) {
            $timer = Timer::with('tags')->find($data['timerId']);
            if ($timer && $timer->tags->isNotEmpty()) {
                $this->tag_input = $timer->tags->pluck('name')->implode(', ');
            }
        }
    }

    /**
     * Handle time input changes from the TimeInput component
     *
     * @param  array  $data
     * @return void
     */
    public function handleTimeInputChanged($data)
    {
        if ($data['name'] === 'editingDurationHuman') {
            $this->editingDurationHuman = $data['value'];

            // Parse the duration to update hours and minutes
            $minutes = $this->parseHumanDuration($data['value']);
            $this->editingDurationHours = floor($minutes / 60);
            $this->editingDurationMinutes = $minutes % 60;
        }
    }

    /**
     * Pause a timer without completely stopping it
     * This will create a time log entry and mark the timer as paused
     */
    public function pauseTimer($timerId)
    {
        $timer = Timer::with('timeLogs')->findOrFail($timerId);

        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();

        if ($latestLog && ! $latestLog->end_time) {
            $now = now();
            $durationMinutes = $latestLog->start_time->diffInMinutes($now);

            // Only save the time log if duration is greater than 0 minutes
            if ($durationMinutes > 0) {
                $latestLog->end_time = $now;
                $latestLog->duration_minutes = (int) $durationMinutes; // Cast to integer to avoid decimal values
                $latestLog->save();

                // Dispatch event to update the daily progress bar
                $this->dispatch('timeLogSaved');
            } else {
                // If duration is 0 minutes, delete the time log instead of saving it
                $latestLog->delete();
                $this->showNotification('Timer had 0 minutes and was not saved', 'info');
            }
        }

        // Mark timer as not running but paused
        $timer->is_running = false;
        $timer->is_paused = true;
        $timer->save();

        // Get the total duration for today to include in the event
        $totalDuration = $this->getTimerTotalDurationForToday($timer);

        // Get the last time log duration to include in the event
        $lastDuration = null;
        if ($latestLog && $latestLog->duration_minutes) {
            $lastDuration = $this->formatDuration($latestLog->duration_minutes * 60);
        }

        $this->showNotification('Timer paused', 'info');
        $this->dispatch('timerPaused', [
            'timerId' => $timerId,
            'totalDuration' => $totalDuration,
            'lastDuration' => $lastDuration,
        ]);
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
        if (! $this->customEndTime) {
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
        if (! $this->actualHoursWorked || ! is_numeric($this->actualHoursWorked) || $this->actualHoursWorked <= 0) {
            $this->showNotification('Please enter a valid number of hours', 'error');

            return;
        }

        $startTime = $this->longRunningTimerStartTime;
        $endTime = (clone $startTime)->addHours((float) $this->actualHoursWorked);

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

    /**
     * Format duration based on the selected time format
     *
     * @param  int  $seconds  Number of seconds to format
     * @return string Formatted duration string
     */
    public function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($this->timeFormat === 'hms') {
            // Format as HH:MM:SS
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        } elseif ($this->timeFormat === 'hm') {
            // Format as HH:MM
            return sprintf('%02d:%02d', $hours, $minutes);
        } else {
            // Human readable format (e.g., 3h 40m 5s)
            if ($hours > 0) {
                if ($minutes > 0) {
                    if ($secs > 0) {
                        return $hours.'h '.$minutes.'m '.$secs.'s';
                    }

                    return $hours.'h '.$minutes.'m';
                }

                return $hours.'h';
            }

            if ($minutes > 0) {
                if ($secs > 0) {
                    return $minutes.'m '.$secs.'s';
                }

                return $minutes.'m';
            }

            return $secs.'s';
        }
    }

    /**
     * Set the time format
     *
     * @param  string  $format  Format to use (hms, hm, human)
     */
    public function setTimeFormat($format)
    {
        // Validate the format
        if (! in_array($format, ['human', 'hm', 'hms'])) {
            $format = 'human';
        }

        // Update the local property
        $this->timeFormat = $format;

        // Save to user preferences using DB facade
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', Auth::id())
            ->update(['time_format' => $format]);

        $this->showNotification('Time format preference saved', 'success');
    }

    /**
     * Get the duration of the current timer session
     */
    public function getTimerDuration($timer)
    {
        if (! $timer->latestTimeLog) {
            return $this->timeFormat === 'human' ? '0s' : '00:00:00';
        }

        $startTime = $timer->latestTimeLog->start_time;
        $endTime = $timer->latestTimeLog->end_time ?? now();

        $diff = $startTime->diffInSeconds($endTime);

        return $this->formatDuration($diff);
    }

    /**
     * Get the total duration for a timer for the current day
     */
    public function getTimerTotalDurationForToday($timer)
    {
        // Get all time logs for today for this timer
        $today = now()->startOfDay();
        $timeLogs = $timer->timeLogs()
            ->where('created_at', '>=', $today)
            ->where('workspace_id', app('current.workspace')->id)
            ->get();

        // Calculate total seconds
        $totalSeconds = 0;

        foreach ($timeLogs as $log) {
            if ($log->duration_minutes) {
                // For completed logs, use the stored duration
                $totalSeconds += $log->duration_minutes * 60;
            } elseif (! $log->end_time) {
                // For active logs, calculate the current duration
                $startTime = $log->start_time;
                $endTime = now();
                $totalSeconds += $startTime->diffInSeconds($endTime);
            }
        }

        // Format the total duration using our formatDuration method
        return $this->formatDuration($totalSeconds);
    }

    /**
     * Ensure the start time is properly formatted for JavaScript
     *
     * @param  \App\Models\Timer  $timer
     * @return string
     */
    public function getFormattedStartTimeForJs($timer)
    {
        if (! $timer->latestTimeLog) {
            return now()->toIso8601String();
        }

        return $timer->latestTimeLog->start_time->toIso8601String();
    }

    public function getFormattedStartTime($timer)
    {
        if (! $timer->latestTimeLog) {
            return 'Not started';
        }

        return $timer->latestTimeLog->start_time->format('g:i A');
    }

    /**
     * Edit a timer that is not currently running
     */
    public function editTimer($timerId)
    {
        $timer = Timer::with(['tags', 'project', 'latestDescription'])->findOrFail($timerId);

        // Set up editing properties
        $this->editingTimerId = $timer->id;
        $this->editingTimerName = $timer->name;

        // Get the latest description if available
        if ($timer->latestDescription) {
            $this->editingTimerDescription = $timer->latestDescription->description;
            $this->editingTimerDescriptionId = $timer->latestDescription->id;
        } else {
            $this->editingTimerDescription = '';
            $this->editingTimerDescriptionId = null;
        }

        $this->editingTimerProjectName = $timer->project ? $timer->project->name : '';
        $this->editingTimerTagInput = $timer->tags->pluck('name')->implode(', ');

        // Show the edit modal
        $this->showEditTimerModal = true;
    }

    /**
     * Edit a running timer without stopping it
     */
    public function editRunningTimer($timerId)
    {
        $timer = Timer::with(['tags', 'project', 'latestDescription'])->findOrFail($timerId);

        // Set up editing properties
        $this->editingTimerId = $timer->id;
        $this->editingTimerName = $timer->name;

        // Get the latest description if available
        if ($timer->latestDescription) {
            $this->editingTimerDescription = $timer->latestDescription->description;
            $this->editingTimerDescriptionId = $timer->latestDescription->id;
        } else {
            $this->editingTimerDescription = '';
            $this->editingTimerDescriptionId = null;
        }

        $this->editingTimerProjectName = $timer->project ? $timer->project->name : '';
        $this->editingTimerTagInput = $timer->tags->pluck('name')->implode(', ');

        // Show the edit modal
        $this->showEditTimerModal = true;
    }

    /**
     * Open the restart timer modal
     */
    public function restartTimer($timerId)
    {
        $timer = Timer::with(['latestDescription'])->findOrFail($timerId);

        // Set up restart properties
        $this->restartTimerId = $timer->id;
        $this->restartTimerName = $timer->name;

        // Get the latest description if available
        if ($timer->latestDescription) {
            $this->restartTimerDescriptionId = $timer->latestDescription->id;
        } else {
            $this->restartTimerDescriptionId = null;
        }

        // Show the restart modal
        $this->showRestartTimerModal = true;
    }

    /**
     * Close the restart timer modal
     */
    public function closeRestartTimerModal()
    {
        $this->showRestartTimerModal = false;
        $this->restartTimerId = null;
        $this->restartTimerName = null;
        $this->restartTimerDescriptionId = null;
    }

    /**
     * Confirm and actually restart the timer
     */
    public function confirmRestartTimer()
    {
        $timer = Timer::findOrFail($this->restartTimerId);
        $wasPaused = $timer->is_paused;

        // Mark timer as running and not paused
        $timer->is_running = true;
        $timer->is_paused = false;
        $timer->save();

        // Always use the timer's project_id if it exists, otherwise use default project
        $project_id = $timer->project_id;
        if (! $project_id) {
            $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
            $project_id = $defaultProject->id;

            // Update the timer to use the default project
            $timer->project_id = $project_id;
            $timer->save();
        }

        // Get the description from the selector
        $timerDescriptionId = $this->restartTimerDescriptionId;
        $description = null;

        if ($timerDescriptionId) {
            $timerDescription = TimerDescription::find($timerDescriptionId);
            if ($timerDescription) {
                $description = $timerDescription->description;
            }
        }

        // Create a new time log with current time
        $timeLog = TimeLog::create([
            'timer_id' => $timer->id,
            'timer_description_id' => $timerDescriptionId,
            'user_id' => Auth::id(),
            'start_time' => now(),
            'description' => $description, // Keep for backward compatibility
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Refresh the timer to include the latest time log
        $timer->refresh();

        $message = $wasPaused ? 'Timer resumed successfully' : 'Timer restarted successfully';
        $this->showNotification($message, 'success');

        // Get the total duration for today to include in the event
        $totalDuration = $this->getTimerTotalDurationForToday($timer);

        // Close the modal
        $this->closeRestartTimerModal();

        // Dispatch event with timer ID to ensure frontend updates correctly
        $this->dispatch('timerStarted', [
            'timerId' => $timer->id,
            'startTime' => $timeLog->start_time->toIso8601String(),
            'totalDuration' => $totalDuration,
            'wasPaused' => $wasPaused,
        ]);
    }

    /**
     * Stop a paused timer
     * This simply marks a paused timer as not paused, since the time logs are already created
     */
    public function stopPausedTimer($timerId)
    {
        $timer = Timer::findOrFail($timerId);

        // Mark timer as not paused (it's already not running)
        $timer->is_paused = false;
        $timer->save();

        $this->showNotification('Timer stopped successfully', 'info');
        $this->dispatch('timerStopped', ['timerId' => $timerId]);
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

    /**
     * Get the latest completed time log for a timer
     *
     * @param  \App\Models\Timer  $timer
     * @return \App\Models\TimeLog|null
     */
    public function getLatestCompletedTimeLog($timer)
    {
        return $timer->timeLogs()
            ->whereNotNull('duration_minutes')
            ->whereNotNull('end_time')
            ->latest()
            ->first();
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

        return TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time') // Only completed logs
            ->where('start_time', '>=', $today)
            ->where('start_time', '<', $tomorrow)
            ->with(['timer.project', 'tags'])
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
     * Get the percentage of the required daily hours based on workspace settings
     *
     * @return int
     */
    public function getDailyProgressPercentage()
    {
        $totalMinutes = $this->getTotalDailyMinutes();
        $workspace = app('current.workspace');
        $requiredMinutes = $workspace ? $workspace->daily_target_minutes : 0;

        // If required minutes is 0, return 0 to avoid division by zero
        if ($requiredMinutes === 0) {
            return 0;
        }

        $percentage = min(100, round(($totalMinutes / $requiredMinutes) * 100));

        return $percentage;
    }

    /**
     * Get the remaining time to reach the daily goal based on workspace settings
     *
     * @return string
     */
    public function getRemainingDailyTime()
    {
        $totalMinutes = $this->getTotalDailyMinutes();
        $workspace = app('current.workspace');
        $requiredMinutes = $workspace ? $workspace->daily_target_minutes : 0;

        // If required minutes is 0, return 0m
        if ($requiredMinutes === 0) {
            return '0m';
        }

        $remainingMinutes = max(0, $requiredMinutes - $totalMinutes);

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

    public function render()
    {
        // Cache recent tags for 5 minutes to improve performance
        $recentTags = Cache::remember('user.'.Auth::id().'.workspace.'.app('current.workspace')->id.'.recent_tags', 300, function () {
            return Tag::where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        });

        // Get all timers for the user
        $allTimers = Timer::with(['project', 'tags', 'latestTimeLog', 'latestTimeLog.timerDescription', 'latestDescription'])
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Separate running, paused, and non-running timers
        $runningTimers = $allTimers->where('is_running', true);
        $pausedTimers = $allTimers->where('is_running', false)->where('is_paused', true);
        $savedTimers = $allTimers->where('is_running', false)->where('is_paused', false);

        // Filter saved timers if search is provided
        if (! empty($this->savedTimersSearch)) {
            $search = strtolower($this->savedTimersSearch);
            $savedTimers = $savedTimers->filter(function ($timer) use ($search) {
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

        // Get daily time logs for the progress bar
        $dailyTimeLogs = $this->getDailyTimeLogs();
        $dailyProgressPercentage = $this->getDailyProgressPercentage();
        $remainingDailyTime = $this->getRemainingDailyTime();

        return view('livewire.timers', [
            'recentTags' => $recentTags,
            'runningTimers' => $runningTimers,
            'pausedTimers' => $pausedTimers,
            'savedTimers' => $savedTimers,
            'dailyTimeLogs' => $dailyTimeLogs,
            'dailyProgressPercentage' => $dailyProgressPercentage,
            'remainingDailyTime' => $remainingDailyTime,
        ]);
    }
}
