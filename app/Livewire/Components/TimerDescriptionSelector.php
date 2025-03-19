<?php

namespace App\Livewire\Components;

use App\Models\Timer;
use App\Models\TimerDescription;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimerDescriptionSelector extends Component
{
    public $timerId;

    public $timerDescriptionId;

    public $description = '';

    public $showDropdown = false;

    public $descriptions = [];

    public $createNewDescription = false;

    protected $listeners = [
        'clickAway' => 'closeDropdown',
        'timer-selected' => 'handleTimerSelected',
    ];

    public function mount($timerId = null, $timerDescriptionId = null)
    {
        $this->timerId = $timerId;
        $this->timerDescriptionId = $timerDescriptionId;

        if ($this->timerDescriptionId) {
            $timerDescription = TimerDescription::find($this->timerDescriptionId);
            if ($timerDescription) {
                $this->description = $timerDescription->description;
            }
        }

        // If we have a timer ID, load descriptions for that timer
        if ($this->timerId) {
            $this->loadDescriptions();
        }
    }

    public function handleTimerSelected($data)
    {
        if (isset($data['id'])) {
            $this->timerId = $data['id'];
            $this->timerDescriptionId = null;
            $this->description = '';
            $this->loadDescriptions();
        }
    }

    public function loadDescriptions($query = '')
    {
        $descriptionsQuery = TimerDescription::with('timer')
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id);

        // If we have a timer ID, only load descriptions for that timer
        if ($this->timerId) {
            $descriptionsQuery->where('timer_id', $this->timerId);
        }

        if (! empty($query)) {
            $descriptionsQuery->where('description', 'like', '%'.$query.'%');
        }

        $this->descriptions = $descriptionsQuery->orderBy('created_at', 'desc')->limit(10)->get();
    }

    public function updatedDescription()
    {
        $this->showDropdown = true;
        $this->loadDescriptions($this->description);

        // Check if we need to show "Create new description" option
        $this->createNewDescription = ! empty($this->description) &&
            ! collect($this->descriptions)->where('description', $this->description)->count();
    }

    public function selectDescription($id, $description)
    {
        $this->timerDescriptionId = $id;
        $this->description = $description;
        $this->showDropdown = false;

        // Determine which event to dispatch based on the key
        $eventName = str_contains($this->getId(), 'quick-time')
            ? 'quick-time-description-selected'
            : 'description-selected';

        $this->dispatch($eventName, [
            'id' => $this->timerDescriptionId,
            'description' => $this->description,
        ]);
    }

    public function createDescription()
    {
        if (empty($this->description)) {
            return;
        }

        // Determine which event to dispatch based on the key
        $eventName = str_contains($this->getId(), 'quick-time')
            ? 'quick-time-description-selected'
            : 'description-selected';

        // If no timer is selected, we'll just store the description text without creating a record
        if (! $this->timerId) {
            $this->dispatch($eventName, [
                'id' => null,
                'description' => $this->description,
            ]);
            $this->showDropdown = false;

            return;
        }

        // Use the new findOrCreateForTimer method from our model to handle uniqueness
        $timerDescription = TimerDescription::findOrCreateForTimer([
            'description' => $this->description,
            'timer_id' => $this->timerId,
            'user_id' => Auth::id(),
            'workspace_id' => app('current.workspace')->id,
        ]);

        // Get component key to determine if this is for restart timer modal
        $componentKey = $this->getId();

        // Special handling for restart timer modal to ensure description is captured correctly
        if (str_contains($componentKey, 'restart-timer')) {
            $this->dispatch('description-selected', [
                'id' => $timerDescription->id,
                'description' => $timerDescription->description,
                'isRestart' => true,
            ]);
            $this->showDropdown = false;
        } else {
            $this->selectDescription($timerDescription->id, $timerDescription->description);
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;
        if ($this->showDropdown) {
            $this->loadDescriptions();
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
        $this->descriptions = collect([]);
        $this->createNewDescription = false;
    }

    public function render()
    {
        return view('livewire.components.timer-description-selector');
    }
}
