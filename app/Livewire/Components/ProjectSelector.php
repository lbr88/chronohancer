<?php

namespace App\Livewire\Components;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProjectSelector extends Component
{
    public $projectId;

    public $projectName = '';

    public $showDropdown = false;

    public $projects = [];

    public $createNewProject = false;

    protected $listeners = [
        'clickAway' => 'closeDropdown',
    ];

    public function mount($projectId = null)
    {
        $this->projectId = $projectId;

        if ($this->projectId) {
            $project = Project::find($this->projectId);
            if ($project) {
                $this->projectName = $project->name;
            }
        }

        // Load all projects initially
        $this->loadProjects();
    }

    public function loadProjects($query = '')
    {
        $projectsQuery = Project::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id);

        if (! empty($query)) {
            $projectsQuery->where('name', 'like', '%'.$query.'%');
        }

        $this->projects = $projectsQuery->orderBy('name')->get();
    }

    public function updatedProjectName()
    {
        $this->showDropdown = true;
        $this->loadProjects($this->projectName);

        // Check if we need to show "Create new project" option
        $this->createNewProject = ! empty($this->projectName) &&
          ! collect($this->projects)->where('name', $this->projectName)->count();
    }

    public function selectProject($id, $name)
    {
        $this->projectId = $id;
        $this->projectName = $name;
        $this->showDropdown = false;

        $this->dispatch('project-selected', [
            'id' => $this->projectId,
            'name' => $this->projectName,
        ]);
    }

    public function createProject()
    {
        if (empty($this->projectName)) {
            return;
        }

        // Check if project already exists
        $existingProject = Project::where('name', $this->projectName)
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->first();

        if ($existingProject) {
            $this->selectProject($existingProject->id, $existingProject->name);

            return;
        }

        // Create new project
        $project = Project::create([
            'name' => $this->projectName,
            'description' => 'Project created from selector',
            'user_id' => Auth::id(),
            'workspace_id' => app('current.workspace')->id,
            'color' => '#'.dechex(rand(0x000000, 0xFFFFFF)), // Random color
        ]);

        $this->selectProject($project->id, $project->name);
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;
        if ($this->showDropdown) {
            $this->loadProjects();
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.components.project-selector');
    }
}
