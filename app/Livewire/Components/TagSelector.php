<?php

namespace App\Livewire\Components;

use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TagSelector extends Component
{
    /**
     * The currently selected tag IDs
     */
    public $selectedTags = [];

    /**
     * The input field for adding new tags
     */
    public $tagInput = '';

    /**
     * Tag suggestions based on the current input
     */
    public Collection $tagSuggestions;

    /**
     * Whether to show the tag suggestions dropdown
     */
    public $showSuggestions = false;

    /**
     * Event listeners
     */
    protected $listeners = [
        'clickOutside' => 'closeSuggestions',
    ];

    /**
     * Initialize the component
     */
    public function mount($selectedTags = [])
    {
        $this->selectedTags = $selectedTags;
        $this->tagSuggestions = collect();
    }

    /**
     * Update tag suggestions when the tag input changes
     */
    public function updatedTagInput()
    {
        // Extract the last tag being typed
        $tags = collect(explode(',', $this->tagInput));
        $lastTag = trim($tags->last());

        if (strlen($lastTag) >= 1) {
            $this->tagSuggestions = Tag::where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->where('name', 'like', '%'.$lastTag.'%')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            $this->showSuggestions = $this->tagSuggestions->isNotEmpty();
        } else {
            $this->tagSuggestions = collect();
            $this->showSuggestions = false;
        }
    }

    /**
     * Select a tag from the suggestions
     */
    public function selectTag($tagId, $tagName)
    {
        // Add the tag ID to selected tags if not already selected
        if (! in_array($tagId, $this->selectedTags)) {
            $this->selectedTags[] = $tagId;
        }

        // Clear the tag input
        $this->tagInput = '';
        $this->showSuggestions = false;

        // Emit an event to notify the parent component
        $this->dispatch('tags-updated', $this->selectedTags);
    }

    /**
     * Create a new tag
     */
    public function createTag()
    {
        $tagName = trim($this->tagInput);

        if (empty($tagName)) {
            return;
        }

        // Create the tag
        $tag = Tag::findOrCreateForUser(
            $tagName,
            Auth::id(),
            app('current.workspace')->id
        );

        // Add the tag ID to selected tags if not already selected
        if (! in_array($tag->id, $this->selectedTags)) {
            $this->selectedTags[] = $tag->id;
        }

        // Clear the tag input
        $this->tagInput = '';
        $this->showSuggestions = false;

        // Emit an event to notify the parent component
        $this->dispatch('tags-updated', $this->selectedTags);
    }

    /**
     * Remove a tag from the selected tags
     */
    public function removeTag($tagId)
    {
        $this->selectedTags = array_values(array_filter($this->selectedTags, function ($id) use ($tagId) {
            return $id != $tagId;
        }));

        // Emit an event to notify the parent component
        $this->dispatch('tags-updated', $this->selectedTags);
    }

    /**
     * Close the suggestions dropdown
     */
    public function closeSuggestions()
    {
        $this->showSuggestions = false;
    }

    /**
     * Calculate contrasting text color (black or white) based on background color
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

    /**
     * Render the component
     */
    public function render()
    {
        // Get all selected tags
        $tags = Tag::whereIn('id', $this->selectedTags)->get();

        // Get recent tags for suggestions
        $recentTags = Tag::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotIn('id', $this->selectedTags)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.components.tag-selector', [
            'tags' => $tags,
            'recentTags' => $recentTags,
        ]);
    }
}
