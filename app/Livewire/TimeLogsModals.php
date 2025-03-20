<?php

namespace App\Livewire;

use App\Livewire\Traits\TimeLogsUtilities;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Timer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeLogsModals extends Component
{
    use TimeLogsUtilities;

    // Quick Time Modal
    public $showQuickTimeModal = false;

    public $quickTimeDate;

    public $quickTimeProjectId;

    public $quickTimeTimerId;

    public $quickTimeDescription;

    public $quickTimeSelectedTags = [];

    public $quickTimeDuration = 0;

    public $quickTimeProjectTimers = [];

    // Manual Time Log Modal
    public $showManualTimeLogModal = false;

    public $project_id;

    public $timer_id;

    public $description;

    public $selected_date;

    public $duration_minutes;

    public $selectedTags = [];

    // Edit Modal
    public $editingTimeLog = null;

    // Time Log Selection Modal
    public $showTimeLogSelectionModal = false;

    public $timeLogSelectionOptions = [];

    // Delete Confirmation Modal
    public $confirmingDelete = null;

    // Tempo Worklog Details Modal
    public $showTempoWorklogDetailsModal = false;

    public $tempoWorklogDetails = null;

    public $selectedTempoWorklogId = null;

    // Return to dashboard flag
    public $returnToDashboard = false;

    protected $listeners = [
        'open-quick-time-modal' => 'openQuickTimeModal',
        'open-manual-time-log-modal' => 'openManualTimeLogModal',
        'open-edit-modal' => 'startEdit',
        'find-and-edit-time-log' => 'findAndEditTimeLog',
        'project-selected' => 'handleProjectSelected',
        'tags-updated' => 'handleTagsUpdated',
        'time-input-changed' => 'handleTimeInputChanged',
        'description-selected' => 'handleDescriptionSelected',
        'quick-time-description-selected' => 'handleQuickTimeDescriptionSelected',
        'unified-timer-selected' => 'handleUnifiedTimerSelected',
        'createTimeLogFromEvent' => 'handleCreateTimeLogFromEvent',
        'confirm-delete' => 'confirmDelete',
        'view-tempo-worklog-details' => 'viewTempoWorklogDetails',
    ];

    protected $rules = [
        'project_id' => 'nullable|exists:projects,id',
        'description' => 'nullable',
        'duration_minutes' => 'required',
        'selected_date' => 'required|date',
    ];

    public function mount()
    {
        $this->selected_date = now()->format('Y-m-d');
    }

    /**
     * Handle creating a time log from a calendar event
     */
    public function handleCreateTimeLogFromEvent($data)
    {
        $description = $data['description'];
        $projectId = null;
        $timerId = null;

        // Try to find a previous time log with the same description
        $previousTimeLog = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->where('description', $description)
            ->orderBy('created_at', 'desc')
            ->first();

        // If found, use the same timer and project
        if ($previousTimeLog) {
            $timerId = $previousTimeLog->timer_id;

            if ($timerId) {
                $timer = Timer::find($timerId);
                if ($timer) {
                    $projectId = $timer->project_id;
                }
            }
        }

        $this->openQuickTimeModal(
            $data['date'],
            $projectId, // Use project from previous time log or default
            $timerId,   // Use timer from previous time log or null
            $description
        );

        // Set the duration from the event
        $this->quickTimeDuration = $data['duration_minutes'];

        // Store the Microsoft event ID in the session for later use
        session(['microsoft_event_id' => $data['event_id'] ?? null]);
    }

    /**
     * Open the quick time modal
     */
    public function openQuickTimeModal($date = null, $projectId = null, $timerId = null, $description = null)
    {
        $this->quickTimeDate = $date ?? now()->format('Y-m-d');

        // Convert string 'null' to actual null
        $this->quickTimeProjectId = $projectId === 'null' ? null : $projectId;
        $this->quickTimeTimerId = $timerId === 'null' ? null : $timerId;

        $this->quickTimeDescription = $description;
        $this->quickTimeDuration = 0;

        // Load timers
        $this->loadProjectTimers($this->quickTimeProjectId);

        // Also load tags from previous time logs with the same description
        if ($description) {
            $previousTimeLog = TimeLog::where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where('description', $description)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($previousTimeLog && $previousTimeLog->tags->count() > 0) {
                $this->quickTimeSelectedTags = $previousTimeLog->tags->pluck('id')->toArray();
            }
        }

        $this->showQuickTimeModal = true;
    }

    /**
     * Load timers for a project
     */
    public function loadProjectTimers($projectId = null)
    {
        $query = Timer::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id);

        if ($projectId !== null) {
            // If project is selected, show timers for that project and timers without a project
            $query->where(function ($q) use ($projectId) {
                $q->where('project_id', $projectId)
                    ->orWhereNull('project_id');
            });
        }

        $this->quickTimeProjectTimers = $query->orderBy('name')->get();
    }

    /**
     * Close the quick time modal
     */
    public function closeQuickTimeModal()
    {
        $this->showQuickTimeModal = false;
        $this->quickTimeSelectedTags = [];
    }

    /**
     * Save the quick time entry
     */
    public function saveQuickTime()
    {
        if ($this->quickTimeDuration <= 0) {
            return;
        }

        // Create start and end times from the selected date
        $start_time = Carbon::parse($this->quickTimeDate)->startOfDay();
        $end_time = $start_time->copy()->addMinutes($this->quickTimeDuration);

        // Always use the selected project_id if it exists, otherwise use default project
        $project_id = $this->quickTimeProjectId;
        if ($project_id === null) {
            $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
            $project_id = $defaultProject->id;
        }

        // Create or use existing timer
        $timer_id = $this->quickTimeTimerId;
        if (! $timer_id) {
            // Create a new timer with the project
            $timer = Timer::create([
                'name' => 'Quick Entry',
                'project_id' => $project_id,
                'user_id' => Auth::id(),
                'workspace_id' => app('current.workspace')->id,
                'is_running' => false,
            ]);
            $timer_id = $timer->id;
        } else {
            // If the timer exists but has a different project, update the timer's project
            $timer = Timer::find($timer_id);
            if ($timer && $timer->project_id !== $project_id) {
                $timer->update(['project_id' => $project_id]);
            }
        }

        // Get the Microsoft event ID from the session if it exists
        $microsoftEventId = session('microsoft_event_id');

        $timeLog = TimeLog::create([
            'timer_id' => $timer_id,
            'user_id' => Auth::id(),
            'description' => $this->quickTimeDescription,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $this->quickTimeDuration,
            'workspace_id' => app('current.workspace')->id,
            'microsoft_event_id' => $microsoftEventId,
        ]);

        // Verify the creation was successful
        \Illuminate\Support\Facades\Log::debug('Created quick time log', [
            'id' => $timeLog->id,
            'description' => $timeLog->description,
        ]);

        // Clear the Microsoft event ID from the session
        session()->forget('microsoft_event_id');

        // Attach tags if any are selected
        if (! empty($this->quickTimeSelectedTags)) {
            $timeLog->tags()->attach($this->quickTimeSelectedTags);
        }

        // Dispatch event to update the daily progress bar
        $this->dispatch('timeLogSaved');

        // If this was a Microsoft calendar event, reload the calendar events
        if ($microsoftEventId) {
            // Reload Microsoft calendar events to hide the logged event
            $this->dispatch('load-events');
        }

        $this->closeQuickTimeModal();
        session()->flash('message', 'Time log created successfully.');
    }

    /**
     * Open the manual time log modal
     */
    public function openManualTimeLogModal($date = null)
    {
        $this->selected_date = $date ?? now()->format('Y-m-d');
        $this->reset(['project_id', 'timer_id', 'description', 'duration_minutes', 'selectedTags']);
        $this->showManualTimeLogModal = true;
    }

    /**
     * Close the manual time log modal
     */
    public function closeManualTimeLogModal()
    {
        $this->showManualTimeLogModal = false;
    }

    /**
     * Save the manual time log
     */
    public function save()
    {
        $this->validate();

        // Parse duration string into minutes
        $durationMinutes = $this->parseDurationString($this->duration_minutes);

        // Create start and end times from the selected date
        $start_time = Carbon::parse($this->selected_date)->startOfDay();
        $end_time = $start_time->copy()->addMinutes($durationMinutes);

        // Always use the selected project_id if it exists, otherwise use default project
        $project_id = $this->project_id;
        if ($project_id === null) {
            $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
            $project_id = $defaultProject->id;
        }

        // Create a timer if one doesn't exist
        $timer_id = $this->timer_id;
        if (! $timer_id) {
            // Create a new timer with the project
            $timer = Timer::create([
                'name' => 'Manual Entry',
                'project_id' => $project_id,
                'user_id' => Auth::id(),
                'workspace_id' => app('current.workspace')->id,
                'is_running' => false,
            ]);
            $timer_id = $timer->id;
        }

        $timeLog = TimeLog::create([
            'timer_id' => $timer_id,
            'user_id' => Auth::id(),
            'description' => $this->description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $durationMinutes,
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Verify the creation was successful
        \Illuminate\Support\Facades\Log::debug('Created time log', [
            'id' => $timeLog->id,
            'description' => $timeLog->description,
        ]);

        if (! empty($this->selectedTags)) {
            $timeLog->tags()->attach($this->selectedTags);
        }

        // Dispatch event to update the daily progress bar
        $this->dispatch('timeLogSaved');

        $this->reset(['project_id', 'timer_id', 'description', 'duration_minutes', 'selectedTags']);
        $this->selected_date = now()->format('Y-m-d'); // Reset to today
        $this->closeManualTimeLogModal();
        session()->flash('message', 'Time log created successfully.');
    }

    /**
     * Start editing a time log
     */
    public function startEdit($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $this->editingTimeLog = $timeLogId;
        // We don't set project_id anymore since it's read-only and determined by the timer
        $this->timer_id = $timeLog->timer_id;
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

    /**
     * Find and edit a time log for a specific date, project, and timer
     */
    public function findAndEditTimeLog($date, $projectId, $timerId = null, $description = null)
    {
        // Find time logs for the specific date, project and timer
        $query = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereDate('start_time', $date);

        // Handle project_id by filtering through timer relationship
        if ($projectId !== 'null' && $projectId !== null) {
            $query->whereHas('timer', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }

        // Handle timer_id
        if ($timerId === 'null' || $timerId === null) {
            $query->whereNull('timer_id');
        } else {
            $query->where('timer_id', $timerId);
        }

        // If a description is provided, filter by it
        if ($description) {
            $query->where('description', $description);
        }

        // Get all logs for this combination
        $timeLogs = $query->get();

        if ($timeLogs->count() > 1) {
            // Multiple time logs found, show selection modal
            $this->timeLogSelectionOptions = $timeLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description ?: 'No description',
                    'duration' => $this->formatDuration($log->duration_minutes),
                    'start_time' => $log->start_time ? $log->start_time->format('H:i') : '',
                    'end_time' => $log->end_time ? $log->end_time->format('H:i') : '',
                ];
            })->toArray();

            $this->showTimeLogSelectionModal = true;
        } elseif ($timeLogs->count() == 1) {
            // Only one time log found, edit it directly
            $this->startEdit($timeLogs->first()->id);
        } else {
            // If no log exists, set up for creating a new one
            $this->openManualTimeLogModal($date);
            $this->project_id = $projectId === 'null' ? null : $projectId;

            // If a description was provided, set it for the new time log
            if ($description) {
                $this->description = $description;
            }

            // If a timer ID was provided, store it for the new time log
            if ($timerId && $timerId !== 'null') {
                $this->timer_id = $timerId;
            }
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

    /**
     * Cancel editing a time log
     */
    public function cancelEdit()
    {
        $this->reset([
            'editingTimeLog',
            'project_id',
            'timer_id',
            'description',
            'duration_minutes',
            'selectedTags',
        ]);

        // Redirect back to dashboard if requested
        if ($this->returnToDashboard) {
            return redirect()->route('dashboard');
        }
    }

    /**
     * Update a time log
     */
    public function updateTimeLog()
    {
        $this->validate();

        $timeLog = TimeLog::findOrFail($this->editingTimeLog);

        // Parse duration string into minutes
        $durationMinutes = $this->parseDurationString($this->duration_minutes);

        // Use the selected date if it's been changed
        $start_time = Carbon::parse($this->selected_date)->startOfDay();
        $end_time = $start_time->copy()->addMinutes($durationMinutes);

        // Always use the existing timer - don't allow changing to a different timer
        // This ensures the project association remains the same when editing a time log
        // If the user wants to change both timer and project, they should create a new time log
        $timer_id = $timeLog->timer_id;

        // If there's no timer_id (which should be rare), we need to create one
        if (! $timer_id) {
            // Create a new timer using the same project as before (through the project relationship)
            // or use default project if there's no associated project
            $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);

            $timer = Timer::create([
                'name' => 'Manual Entry',
                'project_id' => $defaultProject->id,
                'user_id' => Auth::id(),
                'workspace_id' => app('current.workspace')->id,
                'is_running' => false,
            ]);
            $timer_id = $timer->id;
        }

        $timeLog->update([
            'timer_id' => $timer_id,
            'description' => $this->description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $durationMinutes,
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Verify the update was successful
        $timeLog->refresh();
        \Illuminate\Support\Facades\Log::debug('Updated time log', [
            'id' => $timeLog->id,
            'description' => $timeLog->description,
        ]);

        $timeLog->tags()->sync($this->selectedTags);

        // Dispatch event to update the daily progress bar
        $this->dispatch('timeLogSaved');

        // Store the return to dashboard flag before resetting
        $returnToDashboard = $this->returnToDashboard;

        $this->reset([
            'editingTimeLog',
            'project_id',
            'timer_id',
            'description',
            'duration_minutes',
            'selectedTags',
        ]);

        session()->flash('message', 'Time log updated successfully.');

        // Redirect back to dashboard if requested
        if ($returnToDashboard) {
            return redirect()->route('dashboard');
        }
    }

    /**
     * Confirm deleting a time log
     */
    public function confirmDelete($timeLogId = null)
    {
        // If called from an event, the parameter will be an array with timeLogId
        if (is_array($timeLogId) && isset($timeLogId['timeLogId'])) {
            $this->confirmingDelete = $timeLogId['timeLogId'];
        } else {
            // If called directly with an ID parameter
            $this->confirmingDelete = $timeLogId;
        }
    }

    /**
     * Cancel deleting a time log
     */
    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    /**
     * Delete a time log
     */
    public function deleteTimeLog($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $timeLog->delete();
        $this->confirmingDelete = null;
        $this->editingTimeLog = null; // Reset editing state to close the modal

        // Dispatch event to update the daily progress bar
        $this->dispatch('timeLogSaved');
        $this->dispatch('timeLogDeleted');

        session()->flash('message', 'Time log deleted successfully.');

        // Redirect back to dashboard if requested
        if ($this->returnToDashboard) {
            return redirect()->route('dashboard');
        }
    }

    /**
     * View Tempo worklog details
     */
    public function viewTempoWorklogDetails($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);

        if (! $timeLog->tempo_worklog_id) {
            return;
        }

        $this->selectedTempoWorklogId = $timeLog->tempo_worklog_id;
        $this->tempoWorklogDetails = $timeLog->getTempoWorklogDetails();
        $this->showTempoWorklogDetailsModal = true;
    }

    /**
     * Close Tempo worklog details modal
     */
    public function closeTempoWorklogDetailsModal()
    {
        $this->showTempoWorklogDetailsModal = false;
        $this->tempoWorklogDetails = null;
        $this->selectedTempoWorklogId = null;
    }

    /**
     * Handle project selection from the project selector component
     */
    public function handleProjectSelected($data)
    {
        if (! isset($data['id'])) {
            return;
        }

        // Check if we're in the quick time modal or the regular edit form
        if ($this->showQuickTimeModal) {
            $this->quickTimeProjectId = $data['id'];
            $this->loadProjectTimers($data['id']);
        } else {
            // Only update project_id if we're not editing an existing time log
            if (! $this->editingTimeLog) {
                $this->project_id = $data['id'];
            }
        }
    }

    /**
     * Handle tag updates from the tag selector component
     */
    public function handleTagsUpdated($selectedTags)
    {
        // Check if we're in the quick time modal or the regular edit form
        if ($this->showQuickTimeModal) {
            $this->quickTimeSelectedTags = $selectedTags;
        } else {
            $this->selectedTags = $selectedTags;
        }
    }

    /**
     * Handle time input changes from the TimeInput component
     */
    public function handleTimeInputChanged($data)
    {
        if ($data['name'] === 'quickTimeDuration') {
            $this->quickTimeDuration = $data['minutes'];
        } elseif ($data['name'] === 'duration_minutes') {
            $this->duration_minutes = $data['value'];
        }
    }

    /**
     * Handle description selection from the timer description selector component
     */
    public function handleDescriptionSelected($data)
    {
        // Always update the description when it changes
        if (isset($data['description'])) {
            $this->description = $data['description'] ?? '';
        }
    }

    /**
     * Handle description selection from the timer description selector component for quick time modal
     */
    public function handleQuickTimeDescriptionSelected($data)
    {
        // Update the description when it changes
        if (isset($data['description'])) {
            $this->quickTimeDescription = $data['description'] ?? '';
        }
    }

    /**
     * Handle unified timer selection from the UnifiedTimerSelector component
     */
    public function handleUnifiedTimerSelected($data)
    {
        // For quick time modal
        if ($this->showQuickTimeModal) {
            // Set timer data
            if (isset($data['timerId'])) {
                $this->quickTimeTimerId = $data['timerId'];
            }

            // Set project data
            if (isset($data['projectId'])) {
                $this->quickTimeProjectId = $data['projectId'];
                $this->loadProjectTimers($data['projectId']);
            }

            // Set description data
            if (isset($data['description'])) {
                $this->quickTimeDescription = $data['description'] ?? '';
            }
        }
        // For manual time log modal or regular edit form
        else {
            // Only update timer and project if we're not editing an existing time log
            if (! $this->editingTimeLog) {
                // Set timer data
                if (isset($data['timerId'])) {
                    $this->timer_id = $data['timerId'];
                }

                // Set project data
                if (isset($data['projectId'])) {
                    $this->project_id = $data['projectId'];
                }
            }

            // Set description data (always allowed)
            if (isset($data['description'])) {
                $this->description = $data['description'] ?? '';
            }
        }
    }

    /**
     * Add quick time
     */
    public function addQuickTime($minutes)
    {
        $this->quickTimeDuration += $minutes;
    }

    /**
     * Set quick time
     */
    public function setQuickTime($minutes)
    {
        $this->quickTimeDuration = $minutes;
    }

    public function render()
    {
        return view('livewire.time-logs-modals', [
            'projects' => Project::where('user_id', Auth::id())->get(),
            'tags' => \App\Models\Tag::where('user_id', Auth::id())->get(),
        ]);
    }
}
