<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Projects extends Component
{
    public $name;

    public $description;

    public $color = '#3b82f6'; // Default blue color

    public $selectedTags = [];

    public $search = '';

    public $tag_input = '';

    public $tagSuggestions = [];

    public $showEditProjectModal = false;

    public $showCreateProjectModal = false;

    public $editingProjectId = null;

    public $editingProjectName = null;

    public $editingProjectDescription = null;

    public $editingProjectColor = null;

    public $editingProjectTagInput = null;

    // Rules for creating a new project
    protected $createRules = [
        'name' => 'required|min:3',
        'description' => 'required',
        'color' => 'required',
        'tag_input' => 'nullable|string',
    ];

    // Rules for editing an existing project
    protected $editRules = [
        'editingProjectName' => 'required|min:3',
        'editingProjectDescription' => 'required',
        'editingProjectColor' => 'required',
        'editingProjectTagInput' => 'nullable|string',
        'editingProjectIsDefault' => 'boolean',
    ];

    public function mount()
    {
        $this->showCreateProjectModal = false;
        $this->showEditProjectModal = false;
    }

    public function updatedSearch()
    {
        // Search is handled in the render method
    }

    /**
     * Open the create project modal and set default values
     */
    public function openCreateProjectModal()
    {
        $this->reset(['name', 'description', 'tag_input', 'selectedTags']);
        $this->color = '#3b82f6'; // Default blue color
        $this->showCreateProjectModal = true;
        session()->flash('message', 'Opening create project modal');
    }

    /**
     * Close the create project modal
     */
    public function closeCreateProjectModal()
    {
        $this->showCreateProjectModal = false;
        $this->reset(['name', 'description', 'tag_input', 'selectedTags']);
    }

    public function updatedTagInput()
    {
        // Extract the last tag being typed
        $tags = collect(explode(',', $this->tag_input));
        $lastTag = trim($tags->last());

        if (strlen($lastTag) >= 2) {
            $this->tagSuggestions = Tag::where('user_id', auth()->id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where('name', 'like', '%'.$lastTag.'%')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            $this->tagSuggestions = [];
        }
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
        $this->tagSuggestions = [];
    }

    public function save()
    {
        // Validate using the createRules
        $this->validate($this->createRules);

        $project = Project::create([
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'user_id' => auth()->id(),
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Process tags from tag_input
        if ($this->tag_input) {
            $tagNames = collect(explode(',', $this->tag_input))
                ->map(fn ($name) => trim($name))
                ->filter();

            $tags = $tagNames->map(function ($name) {
                return Tag::findOrCreateForUser($name, auth()->id(), app('current.workspace')->id);
            });

            $project->tags()->attach($tags->pluck('id'));
        }

        $this->showCreateProjectModal = false;
        session()->flash('message', 'Project created successfully.');
    }

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

    /**
     * Edit a project
     */
    public $editingProjectIsDefault = false;

    public function editProject($projectId)
    {
        $project = Project::with(['tags'])->findOrFail($projectId);

        // Set up editing properties
        $this->editingProjectId = $project->id;
        $this->editingProjectName = $project->name;
        $this->editingProjectDescription = $project->description;
        $this->editingProjectColor = $project->color ?? '#3b82f6'; // Use default blue if null
        $this->editingProjectTagInput = $project->tags->pluck('name')->implode(', ');
        $this->editingProjectIsDefault = $project->is_default;

        // Show the edit modal
        $this->showEditProjectModal = true;
        session()->flash('message', 'Opening edit project modal for project ID: '.$projectId);
    }

    /**
     * Save the edited project details
     */
    public function saveEditedProject()
    {
        $this->validate($this->editRules);

        $project = Project::findOrFail($this->editingProjectId);

        // If setting this project as default, unset any existing default project
        if ($this->editingProjectIsDefault && ! $project->is_default) {
            Project::where('user_id', auth()->id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        // Update project details
        $project->update([
            'name' => $this->editingProjectName,
            'description' => $this->editingProjectDescription,
            'color' => $this->editingProjectColor,
            'is_default' => $this->editingProjectIsDefault,
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Process tags
        if ($this->editingProjectTagInput) {
            $tagNames = collect(explode(',', $this->editingProjectTagInput))
                ->map(fn ($name) => trim($name))
                ->filter();

            $tags = $tagNames->map(function ($name) {
                return Tag::findOrCreateForUser($name, auth()->id(), app('current.workspace')->id);
            });

            $project->tags()->sync($tags->pluck('id'));
        } else {
            $project->tags()->detach();
        }

        // Close the modal
        $this->closeEditProjectModal();

        session()->flash('message', 'Project updated successfully.');
    }

    /**
     * Close the edit project modal
     */
    public function closeEditProjectModal()
    {
        $this->showEditProjectModal = false;
        $this->editingProjectId = null;
        $this->editingProjectName = null;
        $this->editingProjectDescription = null;
        $this->editingProjectColor = null;
        $this->editingProjectTagInput = null;
        $this->editingProjectIsDefault = false;
    }

    // Debug methods removed

    /**
     * Delete a project (soft delete)
     */
    public function deleteProject($projectId)
    {
        $project = Project::findOrFail($projectId);

        // Prevent deletion of default project
        if ($project->is_default) {
            session()->flash('error', 'The default "No Project" project cannot be deleted.');

            return;
        }

        // Detach tags before soft deleting
        $project->tags()->detach();

        // Soft delete the project
        $project->delete();

        // Set project_id to null for associated timers
        $project->timers()->update(['project_id' => null]);

        // Set project_id to null for associated time logs
        $project->timeLogs()->update(['project_id' => null]);

        session()->flash('message', 'Project deleted successfully.');
    }

    /**
     * Set a project as the default project
     */
    public function setAsDefault($projectId)
    {
        // Unset any existing default project
        Project::where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        // Set the selected project as default
        $project = Project::findOrFail($projectId);
        $project->update(['is_default' => true]);

        session()->flash('message', 'Default project updated successfully.');
    }

    public function render()
    {
        // Cache recent tags for 5 minutes to improve performance
        $recentTags = Cache::remember('user.'.auth()->id().'.workspace.'.app('current.workspace')->id.'.recent_tags', 300, function () {
            return Tag::where('user_id', auth()->id())
                ->where('workspace_id', app('current.workspace')->id)
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        });

        $projectsQuery = Project::where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id)
            ->with('tags');

        // Apply search filter if provided
        if (! empty($this->search)) {
            $search = $this->search;
            $projectsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('tags', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        return view('livewire.projects', [
            'projects' => $projectsQuery->get(),
            'tags' => Tag::where('user_id', auth()->id())->where('workspace_id', app('current.workspace')->id)->get(),
            'recentTags' => $recentTags,
        ]);
    }
}
