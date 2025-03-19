<?php

namespace App\Livewire\Components;

use App\Models\Project;
use App\Models\Timer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimerSelector extends Component
{
    public $timerId;

    public $timerName = '';

    public $showDropdown = false;

    public $timers = [];

    public $createNewTimer = false;

    public $search = '';

    // For creating a new timer
    public $projectId;

    public $projectName = '';

    public $showNewTimerForm = false;

    protected $listeners = [
        'clickAway' => 'closeDropdown',
        'project-selected' => 'handleProjectSelected',
    ];

    public function mount($timerId = null)
    {
        $this->timerId = $timerId;

        if ($this->timerId) {
            $timer = Timer::find($this->timerId);
            if ($timer) {
                $this->timerName = $timer->name;
            }
        }

        // Load timers initially
        $this->loadTimers();
    }

    public function loadTimers($query = '')
    {
        $timersQuery = Timer::with(['project', 'tags', 'latestDescription'])
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id);

        if (! empty($query)) {
            $timersQuery->where('name', 'like', '%'.$query.'%');
        }

        $this->timers = $timersQuery->orderBy('updated_at', 'desc')->limit(10)->get();
    }

    public function updatedSearch()
    {
        $this->showDropdown = true;
        $this->loadTimers($this->search);

        // Check if we need to show "Create new timer" option
        $this->createNewTimer = ! empty($this->search) &&
          ! collect($this->timers)->where('name', $this->search)->count();
    }

    public function selectTimer($id, $name)
    {
        $this->timerId = $id;
        $this->timerName = $name;
        $this->showDropdown = false;

        $this->dispatch('timer-selected', [
            'id' => $this->timerId,
            'name' => $this->timerName,
        ]);
    }

    public function showCreateTimerForm()
    {
        $this->showNewTimerForm = true;
        $this->showDropdown = false;
        $this->timerName = $this->search;
    }

    public function cancelCreateTimer()
    {
        $this->showNewTimerForm = false;
        $this->timerName = '';
        $this->projectId = null;
        $this->projectName = '';
    }

    public function handleProjectSelected($data)
    {
        if (isset($data['id'])) {
            $this->projectId = $data['id'];
            $this->projectName = $data['name'];
        }
    }

    public function createTimer()
    {
        if (empty($this->timerName)) {
            return;
        }

        // Find or create project if project_id or name is provided, or use default project
        $project = null;
        if ($this->projectId) {
            $project = Project::find($this->projectId);
        } elseif ($this->projectName) {
            $project = Project::firstOrCreate(
                ['name' => $this->projectName, 'user_id' => Auth::id(), 'workspace_id' => app('current.workspace')->id],
                ['description' => 'Project created from timer selector']
            );
        }

        if (! $project) {
            // Always use the default project if no project is found
            $project = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
        }

        // Create new timer
        $timer = Timer::create([
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'name' => $this->timerName,
            'is_running' => false,
            'is_paused' => false,
            'workspace_id' => app('current.workspace')->id,
        ]);

        $this->selectTimer($timer->id, $timer->name);
        $this->showNewTimerForm = false;
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;
        if ($this->showDropdown) {
            $this->loadTimers();
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
        $this->createNewTimer = false;
        $this->search = '';
    }

    public function render()
    {
        return view('livewire.components.timer-selector');
    }
}
