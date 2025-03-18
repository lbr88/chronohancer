<?php

namespace App\Livewire;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Tags extends Component
{
    public $name;

    public $color = '#3b82f6'; // Default blue color

    public $search = '';

    public $showEditTagModal = false;

    public $showCreateTagModal = false;

    public $editingTagId = null;

    public $editingTagName = null;

    public $editingTagColor = null;

    // Rules for creating a new tag
    protected function getCreateRules()
    {
        return [
            'name' => [
                'required',
                'min:2',
                function ($attribute, $value, $fail) {
                    // Check if a tag with this name already exists for the current user and workspace
                    $exists = Tag::where('name', $value)
                        ->where('user_id', auth()->id())
                        ->where('workspace_id', app('current.workspace')->id)
                        ->exists();

                    if ($exists) {
                        $fail('A tag with this name already exists in your workspace.');
                    }
                },
            ],
            'color' => 'required',
        ];
    }

    // Rules for editing an existing tag
    protected function getEditRules()
    {
        return [
            'editingTagName' => [
                'required',
                'min:2',
                function ($attribute, $value, $fail) {
                    // Check if another tag with this name exists for the current user and workspace
                    $exists = Tag::where('name', $value)
                        ->where('user_id', auth()->id())
                        ->where('workspace_id', app('current.workspace')->id)
                        ->where('id', '!=', $this->editingTagId) // Exclude the current tag
                        ->exists();

                    if ($exists) {
                        $fail('Another tag with this name already exists in your workspace.');
                    }
                },
            ],
            'editingTagColor' => 'required',
        ];
    }

    public function mount()
    {
        $this->showCreateTagModal = false;
        $this->showEditTagModal = false;
    }

    public function updatedSearch()
    {
        // Search is handled in the render method
    }

    /**
     * Open the create tag modal and set a random color
     */
    public function openCreateTagModal()
    {
        $this->reset(['name']);
        $this->color = $this->generateRandomColor();
        $this->showCreateTagModal = true;
        session()->flash('message', 'Opening create tag modal');
    }

    /**
     * Close the create tag modal
     */
    public function closeCreateTagModal()
    {
        $this->showCreateTagModal = false;
        $this->reset(['name']);
    }

    /**
     * Generate a random color in hex format
     */
    private function generateRandomColor()
    {
        $colors = [
            '#ef4444', // red
            '#f97316', // orange
            '#f59e0b', // amber
            '#eab308', // yellow
            '#84cc16', // lime
            '#22c55e', // green
            '#10b981', // emerald
            '#14b8a6', // teal
            '#06b6d4', // cyan
            '#0ea5e9', // sky
            '#3b82f6', // blue
            '#6366f1', // indigo
            '#8b5cf6', // violet
            '#a855f7', // purple
            '#d946ef', // fuchsia
            '#ec4899', // pink
            '#f43f5e', // rose
        ];

        return $colors[array_rand($colors)];
    }

    public function save()
    {
        // Validate using the getCreateRules method
        $this->validate($this->getCreateRules());

        Tag::create([
            'name' => $this->name,
            'color' => $this->color,
            'user_id' => auth()->id(),
            'workspace_id' => app('current.workspace')->id,
        ]);

        $this->reset(['name']);
        $this->showCreateTagModal = false;

        // Clear the cache for recent tags
        Cache::forget('user.'.auth()->id().'.workspace.'.app('current.workspace')->id.'.recent_tags');

        session()->flash('message', 'Tag created successfully.');
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
     * Edit a tag
     */
    public function editTag($tagId)
    {
        $tag = Tag::findOrFail($tagId);

        // Set up editing properties
        $this->editingTagId = $tag->id;
        $this->editingTagName = $tag->name;
        $this->editingTagColor = $tag->color;

        // Show the edit modal
        $this->showEditTagModal = true;
        session()->flash('message', 'Opening edit tag modal for tag ID: '.$tagId);
    }

    /**
     * Save the edited tag details
     */
    public function saveEditedTag()
    {
        $this->validate($this->getEditRules());

        $tag = Tag::findOrFail($this->editingTagId);

        // Update tag details
        $tag->update([
            'name' => $this->editingTagName,
            'color' => $this->editingTagColor,
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Close the modal
        $this->closeEditTagModal();

        // Clear the cache for recent tags
        Cache::forget('user.'.auth()->id().'.workspace.'.app('current.workspace')->id.'.recent_tags');

        session()->flash('message', 'Tag updated successfully.');
    }

    /**
     * Close the edit tag modal
     */
    public function closeEditTagModal()
    {
        $this->showEditTagModal = false;
        $this->editingTagId = null;
        $this->editingTagName = null;
        $this->editingTagColor = null;
    }

    /**
     * Delete a tag
     */
    public function deleteTag($tagId)
    {
        $tag = Tag::findOrFail($tagId);

        // Detach tag from related models before deleting
        $tag->timers()->detach();
        $tag->timeLogs()->detach();
        $tag->projects()->detach();

        // Delete the tag
        $tag->delete();

        // Clear the cache for recent tags
        Cache::forget('user.'.auth()->id().'.workspace.'.app('current.workspace')->id.'.recent_tags');

        session()->flash('message', 'Tag deleted successfully.');
    }

    public function render()
    {
        $tagsQuery = Tag::where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id);

        // Apply search filter if provided
        if (! empty($this->search)) {
            $search = $this->search;
            $tagsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            });
        }

        return view('livewire.tags', [
            'tags' => $tagsQuery->get(),
        ]);
    }
}
