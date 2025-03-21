<?php

namespace App\Livewire\Components;

use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Timer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UnifiedTimerSelector extends Component
{
    public $timerId = null;

    public $timerName = '';

    public $description = '';

    public $projectId = null;

    public $projectName = '';

    public $search = '';

    public $existingTimers = [];

    public $jiraKey = '';

    public $isSearchFocused = false;

    public $recentTimers = [];

    public $showProjectSelector = true;

    protected $listeners = [
        'jira-issue-selected' => 'handleJiraIssueSelected',
    ];

    public function mount($timerId = null, $description = null, $projectId = null, $timerName = null, $projectName = null, $showProjectSelector = true)
    {
        $this->timerId = $timerId;
        $this->projectId = $projectId;
        $this->showProjectSelector = $showProjectSelector;

        // Set timer name if provided
        if ($timerName) {
            $this->timerName = $timerName;
        }

        // Set description if provided
        if ($description) {
            $this->description = $description;
        }

        // Set project name if provided
        if ($projectName) {
            $this->projectName = $projectName;
        }

        // Load timer data if a timer ID is provided and no timer name was passed
        if ($this->timerId && ! $timerName) {
            $timer = Timer::find($this->timerId);
            if ($timer) {
                $this->timerName = $timer->name;
                $this->jiraKey = $timer->jira_key;

                // Load project data
                if ($timer->project_id) {
                    $this->projectId = $timer->project_id;
                    $this->projectName = $timer->project ? $timer->project->name : '';
                }

                // Get the latest description from time logs
                $latestTimeLog = TimeLog::where('timer_id', $this->timerId)
                    ->where('user_id', Auth::id())
                    ->where('workspace_id', app('current.workspace')->id)
                    ->whereNotNull('description')
                    ->latest()
                    ->first();

                if ($latestTimeLog) {
                    $this->description = $latestTimeLog->description;
                }
            }
        }

        // Load project data if a project ID is provided and no project name was passed
        if ($this->projectId && ! $this->projectName) {
            $project = Project::find($this->projectId);
            if ($project) {
                $this->projectName = $project->name;
            }
        }

        // Load recent timers
        $this->loadRecentTimers();
    }

    /**
     * Load recent timers for quick selection
     */
    protected function loadRecentTimers()
    {
        $this->recentTimers = Timer::with(['project', 'tags'])
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->existingTimers = Timer::with(['project', 'tags'])
                ->where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where('name', 'like', '%'.$this->search.'%')
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();
        } elseif ($this->isSearchFocused) {
            // If search is focused but no search text, show recent timers
            $this->existingTimers = $this->recentTimers;
        } else {
            $this->existingTimers = [];
        }
    }

    /**
     * Handle search input focus event
     */
    public function focusSearch()
    {
        $this->isSearchFocused = true;

        // If no search text, show recent timers
        if (strlen($this->search) < 2) {
            $this->existingTimers = $this->recentTimers;
        }
    }

    /**
     * Handle search input blur event
     */
    public function blurSearch()
    {
        // Add a small delay to allow for timer selection
        $this->isSearchFocused = false;
    }

    /**
     * Clear existing timers to hide dropdown
     */
    public function clearExistingTimers()
    {
        $this->existingTimers = [];
    }

    public function useExistingTimer($timerId)
    {
        $timer = Timer::with(['tags', 'project'])->findOrFail($timerId);
        $this->timerId = $timer->id;
        $this->timerName = $timer->name;
        $this->jiraKey = $timer->jira_key;

        // Get the latest description from time logs
        $latestTimeLog = TimeLog::where('timer_id', $timerId)
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('description')
            ->latest()
            ->first();

        if ($latestTimeLog) {
            $this->description = $latestTimeLog->description;
        } else {
            $this->description = '';
        }

        // Set project data
        $this->projectId = $timer->project_id;
        $this->projectName = $timer->project ? $timer->project->name : '';

        // Clear search
        $this->search = '';
        $this->existingTimers = [];

        // Dispatch event to parent component
        $this->dispatchUnifiedTimerSelected();
    }

    public function handleJiraIssueSelected($data)
    {
        if (isset($data['name'])) {
            $this->timerName = $data['name'];
        }

        if (isset($data['jiraKey'])) {
            $this->jiraKey = $data['jiraKey'];
        }

        // Dispatch event to parent component
        $this->dispatchUnifiedTimerSelected();
    }

    public function updatedTimerName()
    {
        // Log the timer name update
        logger()->debug("Timer name updated to: {$this->timerName}");

        // When the timer name is updated, dispatch the event to the parent component
        $this->dispatchUnifiedTimerSelected();
    }

    public function handleProjectSelected($data)
    {
        if (isset($data['id'])) {
            $this->projectId = $data['id'];
            $project = Project::find($data['id']);
            if ($project) {
                $this->projectName = $project->name;
            }

            // Dispatch event to parent component
            $this->dispatchUnifiedTimerSelected();
        }
    }

    public function handleDescriptionSelected($data)
    {
        if (isset($data['description'])) {
            $this->description = $data['description'];

            // Dispatch event to parent component
            $this->dispatchUnifiedTimerSelected();
        }
    }

    protected function dispatchUnifiedTimerSelected()
    {
        $data = [
            'timerId' => $this->timerId,
            'timerName' => $this->timerName,
            'description' => $this->description,
            'projectId' => $this->projectId,
            'projectName' => $this->projectName,
            'jiraKey' => $this->jiraKey,
        ];

        // Log the data being dispatched
        logger()->debug('Dispatching unified-timer-selected with data: '.json_encode($data));

        $this->dispatch('unified-timer-selected', $data);
    }

    public function render()
    {
        return view('livewire.components.unified-timer-selector');
    }
}
