<?php

namespace App\Livewire\Components;

use App\Models\TimeLog;
use App\Models\Timer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimerDescriptionSelector extends Component
{
    public $timerId;

    public $description = '';

    public $showDropdown = false;

    public $descriptions = [];

    public $createNewDescription = false;

    protected $listeners = [
        'clickAway' => 'closeDropdown',
        'timer-selected' => 'handleTimerSelected',
    ];

    public function mount($timerId = null, $description = null)
    {
        $this->timerId = $timerId;

        if ($description) {
            $this->description = $description;
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
            $this->description = '';
            $this->loadDescriptions();
        }
    }

    public function loadDescriptions($query = '')
    {
        $descriptionsQuery = TimeLog::select('description')
            ->where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->distinct();

        // If we have a timer ID, only load descriptions for that timer
        if ($this->timerId) {
            $descriptionsQuery->where('timer_id', $this->timerId);
        }

        if (! empty($query)) {
            $descriptionsQuery->where('description', 'like', '%'.$query.'%');
        }

        // Get distinct descriptions with their timer information
        $descriptions = $descriptionsQuery->orderBy('description')->get();

        // Convert to a format similar to what we had before
        $this->descriptions = $descriptions->map(function ($item) {
            // Get the timer for this description
            $timer = null;
            if ($this->timerId) {
                $timer = Timer::find($this->timerId);
            } else {
                // Get the most recent timer that used this description
                $timeLog = TimeLog::where('description', $item->description)
                    ->where('user_id', Auth::id())
                    ->where('workspace_id', app('current.workspace')->id)
                    ->latest()
                    ->first();

                if ($timeLog && $timeLog->timer_id) {
                    $timer = Timer::find($timeLog->timer_id);
                }
            }

            // Get the most recent time log with this description
            $timeLog = TimeLog::where('description', $item->description)
                ->where('user_id', Auth::id())
                ->where('workspace_id', app('current.workspace')->id)
                ->latest()
                ->first();

            return (object) [
                'id' => $timeLog ? $timeLog->id : null,
                'description' => $item->description,
                'timer' => $timer,
                'created_at' => $timeLog ? $timeLog->created_at : now(),
            ];
        })->take(10);
    }

    public function updatedDescription()
    {
        $this->showDropdown = true;
        $this->loadDescriptions($this->description);

        // Check if we need to show "Create new description" option
        $this->createNewDescription = ! empty($this->description) &&
            ! collect($this->descriptions)->where('description', $this->description)->count();

        // Notify the parent component that the description has changed
        $eventName = str_contains($this->getId(), 'quick-time')
            ? 'quick-time-description-selected'
            : 'description-selected';

        $this->dispatch($eventName, [
            'description' => $this->description,
            'manuallyChanged' => true,
        ]);
    }

    public function selectDescription($id, $description)
    {
        $this->description = $description;
        $this->showDropdown = false;

        // Determine which event to dispatch based on the key
        $eventName = str_contains($this->getId(), 'quick-time')
            ? 'quick-time-description-selected'
            : 'description-selected';

        $this->dispatch($eventName, [
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

        $this->dispatch($eventName, [
            'description' => $this->description,
        ]);

        $this->showDropdown = false;
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
