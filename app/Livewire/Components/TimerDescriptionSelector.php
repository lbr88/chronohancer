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

        // If no timer is selected, we'll just store the description text without creating a record
        if (! $this->timerId) {
            // Determine which event to dispatch based on the key
            $eventName = str_contains($this->getId(), 'quick-time')
                ? 'quick-time-description-selected'
                : 'description-selected';

            $this->dispatch($eventName, [
                'id' => null,
                'description' => $this->description,
            ]);
            $this->showDropdown = false;

            return;
        }

        // Check if description already exists for this timer
        $existingDescription = TimerDescription::where('description', $this->description)
            ->where('timer_id', $this->timerId)
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->first();

        if ($existingDescription) {
            $this->selectDescription($existingDescription->id, $existingDescription->description);

            return;
        }

        // Create new description
        $timerDescription = TimerDescription::create([
            'description' => $this->description,
            'timer_id' => $this->timerId,
            'user_id' => Auth::id(),
            'workspace_id' => app('current.workspace')->id,
        ]);

        $this->selectDescription($timerDescription->id, $timerDescription->description);
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
