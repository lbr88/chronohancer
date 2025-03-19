<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use App\Models\TimeLog;
use App\Models\Timer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeLogs extends Component
{
    public $project_id;

    public $timer_id;

    public $description;

    public $timerDescriptionId;

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

    public $showManualTimeLogModal = false;

    public $showTimeLogSelectionModal = false;

    public $timeLogSelectionOptions = [];

    public $quickTimeDate;

    public $quickTimeProjectId;

    public $quickTimeTimerId;

    public $quickTimeDescription;

    public $quickTimeTimerDescriptionId;

    public $quickTimeSelectedTags = [];

    public $quickTimeDuration = 0;

    public $quickTimeProjectTimers = [];

    public $selectedTimeLogs = [];

    public $selectAll = false;

    public $confirmingBulkDelete = false;

    public $showTempoWorklogDetailsModal = false;

    public $tempoWorklogDetails = null;

    public $selectedTempoWorklogId = null;

    // Microsoft Calendar integration status
    public $showMicrosoftCalendar = null;

    protected $listeners = [
        'timeLogSaved' => '$refresh',
        'createTimeLogFromEvent' => 'handleCreateTimeLogFromEvent',
        'weekChanged' => 'updateWeekForCalendar',
        'project-selected' => 'handleProjectSelected',
        'tags-updated' => 'handleTagsUpdated',
        'time-input-changed' => 'handleTimeInputChanged',
        'description-selected' => 'handleDescriptionSelected',
        'quick-time-description-selected' => 'handleQuickTimeDescriptionSelected',
        'unified-timer-selected' => 'handleUnifiedTimerSelected',
    ];

    protected $queryString = [
        'sortField' => ['except' => 'start_time'],
        'sortDirection' => ['except' => 'desc'],
        'filterProject' => ['except' => null],
        'filterTag' => ['except' => null],
        'searchQuery' => ['except' => ''],
        'view' => ['except' => 'list'],
        'editId' => ['except' => null],
        'returnToDashboard' => ['except' => false],
    ];

    public $editId = null;

    public $returnToDashboard = false;

    protected $rules = [
        'project_id' => 'nullable|exists:projects,id',
        'description' => 'nullable',
        'duration_minutes' => 'required',
        'selected_date' => 'required|date',
    ];

    public function mount()
    {
        $this->selected_date = now()->format('Y-m-d');
        $this->start_time = now()->format('Y-m-d H:i:s');
        $this->initializeWeek();

        // Set filter dates to current week by default
        $this->filterDateFrom = $this->startOfWeek;
        $this->filterDateTo = $this->endOfWeek;

        // If editId is provided, open the edit modal for that time log
        if ($this->editId) {
            $this->startEdit($this->editId);
        }
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
        $this->updateFilterDates();
        $this->dispatchWeekChangedIfNeeded();
    }

    public function nextWeek()
    {
        $this->currentWeek = $this->currentWeek->addWeek();
        $this->updateWeekRange();
        $this->updateFilterDates();
        $this->dispatchWeekChangedIfNeeded();
    }

    public function currentWeek()
    {
        // Reset to current week
        $this->currentWeek = now();
        $this->updateWeekRange();
        $this->updateFilterDates();

        // Force a dispatch of the weekChanged event regardless of whether the week has changed
        // This ensures the calendar components are updated
        $this->lastDispatchedWeekRange = null; // Reset to force dispatch
        $this->dispatchWeekChangedIfNeeded();

        // Force a re-render to update the UI
        $this->dispatch('$refresh');
    }

    /**
     * Update filter dates to match the current week
     */
    private function updateFilterDates()
    {
        $this->filterDateFrom = $this->startOfWeek;
        $this->filterDateTo = $this->endOfWeek;
    }

    public function updateWeekForCalendar()
    {
        $this->dispatchWeekChangedIfNeeded();
    }

    private function updateWeekRange()
    {
        $this->startOfWeek = $this->currentWeek->copy()->startOfWeek()->format('Y-m-d');
        $this->endOfWeek = $this->currentWeek->copy()->endOfWeek()->format('Y-m-d');
    }

    // Track the last dispatched week range to prevent duplicate dispatches - must be public to persist between requests
    public $lastDispatchedWeekRange = null;

    public function switchView($view)
    {
        $this->view = $view;
        if ($view === 'weekly') {
            $this->dispatchWeekChangedIfNeeded();

            // Force reload of Microsoft calendar events when switching to weekly view
            $this->dispatch('load-events');
        } elseif ($view === 'list') {
            // When switching to list view, ensure filter dates match the current week
            $this->updateFilterDates();

            // Force reload of Microsoft calendar events when switching to list view
            $this->dispatch('load-events');
        }
    }

    /**
     * Dispatch the weekChanged event if the week range has changed or if forced
     */
    protected function dispatchWeekChangedIfNeeded()
    {
        $currentWeekRange = $this->startOfWeek.'-'.$this->endOfWeek;

        \Illuminate\Support\Facades\Log::info('TimeLogs dispatchWeekChangedIfNeeded check', [
            'current_week_range' => $currentWeekRange,
            'last_dispatched_week_range' => $this->lastDispatchedWeekRange,
            'is_same' => $this->lastDispatchedWeekRange === $currentWeekRange,
        ]);

        // Dispatch if the week range has changed or if lastDispatchedWeekRange is null (forced dispatch)
        if ($this->lastDispatchedWeekRange !== $currentWeekRange) {
            // Set the property before dispatching to prevent duplicate dispatches
            $this->lastDispatchedWeekRange = $currentWeekRange;

            \Illuminate\Support\Facades\Log::info('TimeLogs dispatching weekChanged', [
                'startOfWeek' => $this->startOfWeek,
                'endOfWeek' => $this->endOfWeek,
                'weekRange' => $currentWeekRange,
            ]);

            // Dispatch the event to both Microsoft calendar components
            $this->dispatch('weekChanged', $this->startOfWeek, $this->endOfWeek);
        } else {
            \Illuminate\Support\Facades\Log::info('TimeLogs skipping duplicate weekChanged dispatch', [
                'weekRange' => $currentWeekRange,
            ]);
        }
    }

    public function calculateDuration()
    {
        if (! empty($this->start_time) && ! empty($this->end_time)) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            $this->duration_minutes = $end->diffInMinutes($start);
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

    public function createForDate($date)
    {
        $this->selected_date = $date;
        $this->reset(['project_id', 'timer_id', 'timerDescriptionId', 'description', 'duration_minutes', 'selectedTags']);
        $this->dispatch('scroll-to-form');
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
            ->where(function ($query) use ($description) {
                $query->where('description', $description)
                    ->orWhereHas('timerDescription', function ($q) use ($description) {
                        $q->where('description', $description);
                    });
            })
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

            // Also get the timer description ID if available
            $this->quickTimeTimerDescriptionId = $previousTimeLog->timer_description_id;
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
            $this->project_id = $data['id'];
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
     *
     * @param  array  $data
     * @return void
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
     *
     * @param  array  $data
     * @return void
     */
    public function handleDescriptionSelected($data)
    {
        if (isset($data['id'])) {
            $this->timerDescriptionId = $data['id'];
            $this->description = $data['description'];
        }
    }

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
            'timer_description_id' => $this->timerDescriptionId,
            'user_id' => Auth::id(),
            'description' => $this->description, // Keep for backward compatibility
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $durationMinutes,
            'workspace_id' => app('current.workspace')->id,
        ]);

        if (! empty($this->selectedTags)) {
            $timeLog->tags()->attach($this->selectedTags);
        }

        // Dispatch event to update the daily progress bar
        $this->dispatch('timeLogSaved');

        $this->reset(['project_id', 'timer_id', 'timerDescriptionId', 'description', 'duration_minutes', 'selectedTags']);
        $this->selected_date = now()->format('Y-m-d'); // Reset to today
        session()->flash('message', 'Time log created successfully.');
    }

    public function startEdit($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $this->editingTimeLog = $timeLogId;
        $this->project_id = $timeLog->timer?->project_id;
        $this->timer_id = $timeLog->timer_id;
        $this->timerDescriptionId = $timeLog->timer_description_id;
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
            // First try to find by timer_description
            $timerDescriptionIds = \App\Models\TimerDescription::where('description', $description)
                ->pluck('id')
                ->toArray();

            if (! empty($timerDescriptionIds)) {
                $query->whereIn('timer_description_id', $timerDescriptionIds);
            } else {
                // Fallback to the legacy description field
                $query->where('description', $description);
            }
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
            $this->createForDate($date);
            $this->project_id = $projectId;

            // If a description was provided, set it for the new time log
            if ($description) {
                $this->description = $description;
            }

            // If a timer ID was provided, store it for the new time log
            if ($timerId) {
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

    public function cancelEdit()
    {
        $this->reset([
            'editingTimeLog',
            'project_id',
            'timer_id',
            'timerDescriptionId',
            'description',
            'duration_minutes',
            'selectedTags',
        ]);

        // Redirect back to dashboard if requested
        if ($this->returnToDashboard) {
            return redirect()->route('dashboard');
        }
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

        // Use the existing timer - don't allow changing to a different timer that might
        // be associated with a different project
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
            'timer_description_id' => $this->timerDescriptionId,
            'description' => $this->description, // Keep for backward compatibility
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $durationMinutes,
            'workspace_id' => app('current.workspace')->id,
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
            'timerDescriptionId',
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

    public function deleteTimeLog($timeLogId)
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $timeLog->delete();
        $this->confirmingDelete = null;
        $this->editingTimeLog = null; // Reset editing state to close the modal

        // Dispatch event to update the daily progress bar
        $this->dispatch('timeLogSaved');

        session()->flash('message', 'Time log deleted successfully.');

        // Redirect back to dashboard if requested
        if ($this->returnToDashboard) {
            return redirect()->route('dashboard');
        }
    }

    public function getWeeklyDataProperty()
    {
        // Get time logs for the selected week
        $timeLogs = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time') // Only show logs that have an end time (completed logs)
            ->whereBetween('start_time', [
                $this->startOfWeek.' 00:00:00',
                $this->endOfWeek.' 23:59:59',
            ])
            ->with(['timer.project', 'timer', 'tags', 'timerDescription'])
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

        // Get all active projects (including those without time logs)
        $allProjects = Project::where('user_id', Auth::id())->get();

        // Make sure the default project is included
        $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
        $projectsWithDefaultProject = $allProjects->toArray();

        // Check if the default project is already in the list
        $defaultProjectExists = false;
        foreach ($projectsWithDefaultProject as $project) {
            if ($project['id'] === $defaultProject->id) {
                $defaultProjectExists = true;
                break;
            }
        }

        // Add the default project if it's not already in the list
        if (! $defaultProjectExists) {
            $projectsWithDefaultProject[] = [
                'id' => $defaultProject->id,
                'name' => $defaultProject->name,
                'description' => $defaultProject->description,
            ];
        }

        // Get all active timers
        $allTimers = Timer::where('user_id', Auth::id())->get();

        // Create a map of project IDs to their timers
        $projectTimersMap = [];
        foreach ($allTimers as $timer) {
            $projectId = $timer->project_id;
            if (! isset($projectTimersMap[$projectId])) {
                $projectTimersMap[$projectId] = [];
            }
            $projectTimersMap[$projectId][] = $timer;
        }

        // Group time logs by project (through timer)
        $logsByProject = $timeLogs->groupBy(function ($timeLog) {
            return $timeLog->timer ? $timeLog->timer->project_id : null;
        });

        // Arrays to store projects with and without logs
        $projectsWithLogs = [];
        $projectsWithoutLogs = [];

        // Process each project (including those without logs)
        foreach ($projectsWithDefaultProject as $project) {
            $projectId = $project['id'] ?? null;
            $projectName = $project['name'];
            $projectTotal = 0;
            $timersWithLogs = [];
            $timersWithoutLogs = [];

            // Get logs for this project (if any)
            $projectLogs = $logsByProject[$projectId] ?? collect();
            $hasLogs = $projectLogs->isNotEmpty();

            // Group logs by timer and timer description
            $timerGroups = $projectLogs->groupBy(function ($log) {
                // Group by timer_id and timer_description_id
                // This ensures logs with the same timer but different descriptions are grouped separately
                $timerId = $log->timer_id ?? 'manual';
                $timerDescriptionId = $log->timer_description_id ?? 'no-desc';

                return $timerId.'|'.$timerDescriptionId;
            });

            // Add timers with logs
            foreach ($timerGroups as $timerKey => $timerLogs) {
                // Extract timer_id and timer_description_id from the group key
                [$timerId, $timerDescriptionId] = explode('|', $timerKey, 2);
                $timerId = $timerId === 'manual' ? null : $timerId;
                $timerDescriptionId = $timerDescriptionId === 'no-desc' ? null : $timerDescriptionId;

                $timerName = $timerId ? ($timerLogs->first()->timer->name ?? 'Unnamed Timer') : 'Manual Entry';
                $description = '';

                // Get the description from the timer description if available
                if ($timerDescriptionId && $timerLogs->first()->timerDescription) {
                    $description = $timerLogs->first()->timerDescription->description;
                } else {
                    // Fallback to the legacy description field
                    $description = trim($timerLogs->first()->description ?? '');
                }

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
                if (! empty($description)) {
                    $displayName .= ': '.$description;
                }

                $timersWithLogs[] = [
                    'id' => $timerId,
                    'name' => $displayName,
                    'originalName' => $timerName,
                    'description' => $description,
                    'daily' => $dailyDurations,
                    'dailyDescriptions' => $dailyDescriptions,
                    'dailyLogIds' => $dailyLogIds,
                    'total' => $timerTotal,
                    'tags' => $timerLogs->flatMap->tags->unique('id')->values(),
                ];

                $projectTotal += $timerTotal;
            }

            // Add timers without logs for this project
            if (isset($projectTimersMap[$projectId])) {
                foreach ($projectTimersMap[$projectId] as $timer) {
                    // Skip timers that already have logs (already added above)
                    $timerAlreadyAdded = false;
                    foreach ($timersWithLogs as $existingTimer) {
                        if ($existingTimer['id'] === $timer->id) {
                            $timerAlreadyAdded = true;
                            break;
                        }
                    }

                    if (! $timerAlreadyAdded) {
                        $dailyDurations = array_fill_keys(array_keys($weekDays), 0);
                        $dailyDescriptions = array_fill_keys(array_keys($weekDays), '');
                        $dailyLogIds = array_fill_keys(array_keys($weekDays), null);

                        $timersWithoutLogs[] = [
                            'id' => $timer->id,
                            'name' => $timer->name,
                            'originalName' => $timer->name,
                            'description' => '',
                            'daily' => $dailyDurations,
                            'dailyDescriptions' => $dailyDescriptions,
                            'dailyLogIds' => $dailyLogIds,
                            'total' => 0,
                            'tags' => $timer->tags,
                        ];
                    }
                }
            }

            // Add "Manual Entry" option if it doesn't exist
            $hasManualEntry = false;
            foreach (array_merge($timersWithLogs, $timersWithoutLogs) as $timer) {
                if ($timer['id'] === null) {
                    $hasManualEntry = true;
                    break;
                }
            }

            if (! $hasManualEntry) {
                $dailyDurations = array_fill_keys(array_keys($weekDays), 0);
                $dailyDescriptions = array_fill_keys(array_keys($weekDays), '');
                $dailyLogIds = array_fill_keys(array_keys($weekDays), null);

                $timersWithoutLogs[] = [
                    'id' => null,
                    'name' => 'Manual Entry',
                    'originalName' => 'Manual Entry',
                    'description' => '',
                    'daily' => $dailyDurations,
                    'dailyDescriptions' => $dailyDescriptions,
                    'dailyLogIds' => $dailyLogIds,
                    'total' => 0,
                    'tags' => collect(),
                ];
            }

            // Combine timers with logs first, followed by timers without logs
            $timers = array_merge($timersWithLogs, $timersWithoutLogs);

            // Only add the project if it has timers or if it's a project with logs
            if (count($timers) > 0) {
                $projectData = [
                    'id' => $projectId,
                    'name' => $projectName,
                    'timers' => $timers,
                    'total' => $projectTotal,
                    'hasLogs' => $hasLogs,
                ];

                // Add to appropriate array based on whether it has logs
                if ($hasLogs) {
                    $projectsWithLogs[] = $projectData;
                } else {
                    $projectsWithoutLogs[] = $projectData;
                }

                $totalDuration += $projectTotal;
            }
        }

        // Combine projects with logs first, followed by projects without logs
        $weekData = array_merge($projectsWithLogs, $projectsWithoutLogs);

        return [
            'weekDays' => $weekDays,
            'projects' => $weekData,
            'total' => $totalDuration,
        ];
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
        $this->showFilters = ! $this->showFilters;
    }

    public function resetFilters()
    {
        $this->filterProject = null;
        $this->filterTag = null;
        $this->filterDateFrom = $this->startOfWeek;
        $this->filterDateTo = $this->endOfWeek;
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

    /**
     * This method is automatically called by Livewire when the searchQuery property is updated
     */
    public function updatedSearchQuery()
    {
        // Force a re-render when the search query changes
        $this->render();
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

    public function toggleSelectAll()
    {
        $this->selectAll = ! $this->selectAll;
        if ($this->selectAll) {
            // Get all visible time log IDs based on current filters
            $query = TimeLog::where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->whereNotNull('end_time'); // Only include logs that have an end time

            // Apply the same filters as in the render method
            if ($this->filterProject) {
                $query->whereHas('timer', function ($q) {
                    $q->where('project_id', $this->filterProject);
                });
            }

            if ($this->filterTag) {
                $query->whereHas('tags', function ($q) {
                    $q->where('tags.id', $this->filterTag);
                });
            }

            if ($this->filterDateFrom) {
                $query->where('start_time', '>=', $this->filterDateFrom.' 00:00:00');
            }

            if ($this->filterDateTo) {
                $query->where('start_time', '<=', $this->filterDateTo.' 23:59:59');
            }

            // Handle search query
            if ($this->searchQuery) {
                $searchTerm = trim($this->searchQuery);
                $searchTermLower = strtolower($searchTerm);

                // Check if search term contains any part of "no project" or default project
                $matchesDefaultProject = str_contains('no project', $searchTermLower) ||
                    str_contains($searchTermLower, 'no') ||
                    str_contains($searchTermLower, 'project');

                // Get the default project
                $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
                $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);

                // Apply all search conditions
                $query->where(function ($q) use ($searchTerm, $defaultProject, $matchesDefaultProject) {
                    // Regular search in description and project name (through timer)
                    $q->where('description', 'like', '%'.$searchTerm.'%')
                        ->orWhereHas('timer.project', function ($q) use ($searchTerm) {
                            $q->where('name', 'like', '%'.$searchTerm.'%');
                        });

                    // Include default project results if the search term matches
                    if ($matchesDefaultProject) {
                        $q->orWhereHas('timer', function ($q) use ($defaultProject) {
                            $q->where('project_id', $defaultProject->id);
                        });
                    }
                });
            }

            $this->selectedTimeLogs = $query->pluck('id')->toArray();
        } else {
            $this->selectedTimeLogs = [];
        }

        // Force a re-render to update the UI
        $this->dispatch('refresh');
    }

    public function updatedSelectedTimeLogs()
    {
        // Get the count of all visible time logs based on current filters
        $query = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time'); // Only include logs that have an end time

        // Apply the same filters as in the render method
        if ($this->filterProject) {
            $query->whereHas('timer', function ($q) {
                $q->where('project_id', $this->filterProject);
            });
        }

        if ($this->filterTag) {
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->filterTag);
            });
        }

        if ($this->filterDateFrom) {
            $query->where('start_time', '>=', $this->filterDateFrom.' 00:00:00');
        }

        if ($this->filterDateTo) {
            $query->where('start_time', '<=', $this->filterDateTo.' 23:59:59');
        }

        // Handle search query
        if ($this->searchQuery) {
            $searchTerm = trim($this->searchQuery);
            $searchTermLower = strtolower($searchTerm);

            // Check if search term contains any part of "no project" or default project
            $matchesDefaultProject = str_contains('no project', $searchTermLower) ||
                str_contains($searchTermLower, 'no') ||
                str_contains($searchTermLower, 'project');

            // Get the default project
            $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);

            // Apply all search conditions
            $query->where(function ($q) use ($searchTerm, $defaultProject, $matchesDefaultProject) {
                // Regular search in description and project name (through timer)
                $q->where('description', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('timer.project', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%');
                    });

                // Include default project results if the search term matches
                if ($matchesDefaultProject) {
                    $q->orWhereHas('timer', function ($q) use ($defaultProject) {
                        $q->where('project_id', $defaultProject->id);
                    });
                }
            });
        }

        $totalCount = $query->count();
        $selectedCount = count($this->selectedTimeLogs);

        // Update selectAll based on whether all visible logs are selected
        $this->selectAll = ($selectedCount > 0 && $selectedCount >= $totalCount);
    }

    public function confirmBulkDelete()
    {
        if (count($this->selectedTimeLogs) > 0) {
            $this->confirmingBulkDelete = true;
        }
    }

    public function cancelBulkDelete()
    {
        $this->confirmingBulkDelete = false;
    }

    public function bulkDeleteTimeLogs()
    {
        if (count($this->selectedTimeLogs) > 0) {
            TimeLog::whereIn('id', $this->selectedTimeLogs)
                ->where('user_id', Auth::id()) // Security check
                ->delete();

            $count = count($this->selectedTimeLogs);
            $this->selectedTimeLogs = [];
            $this->selectAll = false;
            $this->confirmingBulkDelete = false;

            // Dispatch event to update the daily progress bar
            $this->dispatch('timeLogSaved');

            session()->flash('message', $count.' time '.($count === 1 ? 'log' : 'logs').' deleted successfully.');
        }
    }

    public function openQuickTimeModal($date = null, $projectId = null, $timerId = null, $description = null)
    {
        $this->quickTimeDate = $date ?? now()->format('Y-m-d');

        // Convert string 'null' to actual null
        $this->quickTimeProjectId = $projectId === 'null' ? null : $projectId;
        $this->quickTimeTimerId = $timerId === 'null' ? null : $timerId;

        $this->quickTimeDescription = $description;
        $this->quickTimeTimerDescriptionId = null;
        $this->quickTimeDuration = 0;

        // Load timers
        $this->loadProjectTimers($this->quickTimeProjectId);

        // Also load tags from previous time logs with the same description
        if ($description) {
            $previousTimeLog = TimeLog::where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where(function ($query) use ($description) {
                    $query->where('description', $description)
                        ->orWhereHas('timerDescription', function ($q) use ($description) {
                            $q->where('description', $description);
                        });
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($previousTimeLog && $previousTimeLog->tags->count() > 0) {
                $this->quickTimeSelectedTags = $previousTimeLog->tags->pluck('id')->toArray();
            }
        }

        $this->showQuickTimeModal = true;
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
        $totalMinutes = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time') // Only include logs that have an end time
            ->whereDate('start_time', $date)
            ->sum('duration_minutes');

        $remainingMinutes = max(0, $targetMinutes - $totalMinutes);

        return $remainingMinutes;
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

    public function closeQuickTimeModal()
    {
        $this->showQuickTimeModal = false;
        $this->quickTimeSelectedTags = [];
    }

    public function openManualTimeLogModal($date = null)
    {
        $this->selected_date = $date ?? now()->format('Y-m-d');
        $this->reset(['project_id', 'timer_id', 'timerDescriptionId', 'description', 'duration_minutes', 'selectedTags']);
        $this->showManualTimeLogModal = true;
    }

    public function closeManualTimeLogModal()
    {
        $this->showManualTimeLogModal = false;
    }

    public function updatedQuickTimeProjectId($value)
    {
        $this->loadProjectTimers($value);
        $this->quickTimeTimerId = null; // Reset timer selection when project changes
    }

    /**
     * Handle project selection from the project selector component for quick time modal
     */
    public function handleQuickTimeProjectSelected($data)
    {
        if (isset($data['id'])) {
            $this->quickTimeProjectId = $data['id'];
            $this->loadProjectTimers($data['id']);
            $this->quickTimeTimerId = null; // Reset timer selection when project changes
        }
    }

    /**
     * Handle description selection from the timer description selector component for quick time modal
     */
    public function handleQuickTimeDescriptionSelected($data)
    {
        if (isset($data['id'])) {
            $this->quickTimeTimerDescriptionId = $data['id'];
            $this->quickTimeDescription = $data['description'];
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
            if (isset($data['timerDescriptionId'])) {
                $this->quickTimeTimerDescriptionId = $data['timerDescriptionId'];
                $this->quickTimeDescription = $data['description'] ?? '';
            }
        }
        // For manual time log modal or regular edit form
        else {
            // Set timer data
            if (isset($data['timerId'])) {
                $this->timer_id = $data['timerId'];
            }

            // Set project data
            if (isset($data['projectId'])) {
                $this->project_id = $data['projectId'];
            }

            // Set description data
            if (isset($data['timerDescriptionId'])) {
                $this->timerDescriptionId = $data['timerDescriptionId'];
                $this->description = $data['description'] ?? '';
            }
        }
    }

    /**
     * Handle Jira issue selection from the JiraSearch component
     */
    public function handleJiraIssueSelected($data)
    {
        if ($this->showQuickTimeModal) {
            if (isset($data['name'])) {
                $this->quickTimeDescription = $data['name'];
            }
        } else {
            if (isset($data['name'])) {
                $this->description = $data['name'];
            }
        }
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
            'timer_description_id' => $this->quickTimeTimerDescriptionId,
            'user_id' => Auth::id(),
            'description' => $this->quickTimeDescription, // Keep for backward compatibility
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration_minutes' => $this->quickTimeDuration,
            'workspace_id' => app('current.workspace')->id,
            'microsoft_event_id' => $microsoftEventId,
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

    public function render()
    {
        $query = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time'); // Only show logs that have an end time (completed logs)

        // Apply filters
        if ($this->filterProject) {
            $query->whereHas('timer', function ($q) {
                $q->where('project_id', $this->filterProject);
            });
        }

        if ($this->filterTag) {
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->filterTag);
            });
        }

        if ($this->filterDateFrom) {
            $query->where('start_time', '>=', $this->filterDateFrom.' 00:00:00');
        }

        if ($this->filterDateTo) {
            $query->where('start_time', '<=', $this->filterDateTo.' 23:59:59');
        }

        // Handle search query
        if ($this->searchQuery) {
            $searchTerm = trim($this->searchQuery);
            $searchTermLower = strtolower($searchTerm);

            // Check if search term contains any part of "no project" or default project
            $matchesDefaultProject = str_contains('no project', $searchTermLower) ||
                str_contains($searchTermLower, 'no') ||
                str_contains($searchTermLower, 'project');

            // Get the default project
            $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);

            // Apply all search conditions
            $query->where(function ($q) use ($searchTerm, $defaultProject, $matchesDefaultProject) {
                // Regular search in description and project name (through timer)
                $q->where('description', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('timer.project', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%');
                    });

                // Include default project results if the search term matches
                if ($matchesDefaultProject) {
                    $q->orWhereHas('timer', function ($q) use ($defaultProject) {
                        $q->where('project_id', $defaultProject->id);
                    });
                }
            });
        }

        // Apply sorting
        if ($this->sortField === 'project') {
            $query->join('timers', 'time_logs.timer_id', '=', 'timers.id')
                ->join('projects', 'timers.project_id', '=', 'projects.id')
                ->orderBy('projects.name', $this->sortDirection)
                ->select('time_logs.*');
        } elseif ($this->sortField === 'duration') {
            $query->orderBy('duration_minutes', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        // Get the filtered time logs
        $timeLogs = $query->with(['timer.project' => function ($q) {
            $q->withTrashed(); // Include soft-deleted projects
        }, 'tags', 'timer'])->get();

        // Calculate total duration of filtered logs
        $totalFilteredDuration = $timeLogs->sum('duration_minutes');

        // Use a public property for Microsoft Calendar status to persist between requests
        if (! isset($this->showMicrosoftCalendar)) {
            try {
                // Check if user has Microsoft Graph integration enabled
                $user = Auth::user();
                $this->showMicrosoftCalendar = ! empty($user->microsoft_access_token) && ! empty($user->microsoft_refresh_token);

                // Log the Microsoft Calendar status for debugging
                \Illuminate\Support\Facades\Log::info('TimeLogs Microsoft Calendar Status', [
                    'showMicrosoftCalendar' => $this->showMicrosoftCalendar,
                    'weekRange' => $this->startOfWeek.'-'.$this->endOfWeek,
                    'user_id' => Auth::id(),
                    'last_dispatched_week_range' => $this->lastDispatchedWeekRange,
                ]);
            } catch (\Exception $e) {
                // If there's an error checking Microsoft status, disable it
                \Illuminate\Support\Facades\Log::error('Error checking Microsoft Calendar status', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->showMicrosoftCalendar = false;
            }
        }

        return view('livewire.time-logs', [
            'timeLogs' => $timeLogs,
            'totalFilteredDuration' => $totalFilteredDuration,
            'projects' => Project::where('user_id', Auth::id())->get(),
            'tags' => Tag::where('user_id', Auth::id())->get(),
            'allTags' => Tag::where('user_id', Auth::id())->get(),
            'showMicrosoftCalendar' => $this->showMicrosoftCalendar,
        ]);
    }
}
