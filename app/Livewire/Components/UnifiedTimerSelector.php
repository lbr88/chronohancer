<?php

namespace App\Livewire\Components;

use App\Models\Project;
use App\Models\Timer;
use App\Models\TimerDescription;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UnifiedTimerSelector extends Component
{
    public $timerId = null;

    public $timerName = '';

    public $timerDescriptionId = null;

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

    public function mount($timerId = null, $timerDescriptionId = null, $projectId = null, $timerName = null, $description = null, $projectName = null, $showProjectSelector = true)
    {
        $this->timerId = $timerId;
        $this->timerDescriptionId = $timerDescriptionId;
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
            }
        }

        // Load description data if a description ID is provided and no description was passed
        if ($this->timerDescriptionId && ! $description) {
            $timerDescription = TimerDescription::find($this->timerDescriptionId);
            if ($timerDescription) {
                $this->description = $timerDescription->description;
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

    public function useExistingTimer($timerId)
    {
        $timer = Timer::with(['tags', 'project', 'descriptions'])->findOrFail($timerId);
        $this->timerId = $timer->id;
        $this->timerName = $timer->name;
        $this->jiraKey = $timer->jira_key;

        // Get the latest description if available
        $latestDescription = $timer->latestDescription;
        if ($latestDescription) {
            $this->description = $latestDescription->description;
            $this->timerDescriptionId = $latestDescription->id;
        } else {
            $this->description = '';
            $this->timerDescriptionId = null;
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
        if (isset($data['id'])) {
            $this->timerDescriptionId = $data['id'];
            $this->description = $data['description'];

            // Dispatch event to parent component
            $this->dispatchUnifiedTimerSelected();
        }
    }

    protected function dispatchUnifiedTimerSelected()
    {
        $this->dispatch('unified-timer-selected', [
            'timerId' => $this->timerId,
            'timerName' => $this->timerName,
            'timerDescriptionId' => $this->timerDescriptionId,
            'description' => $this->description,
            'projectId' => $this->projectId,
            'projectName' => $this->projectName,
            'jiraKey' => $this->jiraKey,
        ]);
    }

    public function render()
    {
        return view('livewire.components.unified-timer-selector');
    }
}
