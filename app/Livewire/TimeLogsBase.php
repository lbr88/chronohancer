<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeLogsBase extends Component
{
    public $view = 'list'; // list, weekly

    public $startOfWeek;

    public $endOfWeek;

    public $currentWeek;

    public $lastDispatchedWeekRange = null;

    public $showMicrosoftCalendar = null;

    public $editId = null;

    public $returnToDashboard = false;

    // Query string parameters
    protected $queryString = [
        'view' => ['except' => 'list'],
        'editId' => ['except' => null],
        'returnToDashboard' => ['except' => false],
    ];

    public function mount()
    {
        $this->initializeWeek();

        // If editId is provided, open the edit modal for that time log
        if ($this->editId) {
            $this->dispatch('open-edit-modal', ['timeLogId' => $this->editId]);
        }

        // Check if user has Microsoft Graph integration enabled
        try {
            $user = Auth::user();
            $this->showMicrosoftCalendar = ! empty($user->microsoft_access_token) && ! empty($user->microsoft_refresh_token);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error checking Microsoft Calendar status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->showMicrosoftCalendar = false;
        }
    }

    public function initializeWeek()
    {
        $this->currentWeek = now();
        $this->updateWeekRange();
    }

    public function previousWeek()
    {
        $this->currentWeek = $this->currentWeek->subWeek();
        $this->updateWeekRange();
        $this->dispatchWeekChangedIfNeeded();
    }

    public function nextWeek()
    {
        $this->currentWeek = $this->currentWeek->addWeek();
        $this->updateWeekRange();
        $this->dispatchWeekChangedIfNeeded();
    }

    public function currentWeek()
    {
        // Reset to current week
        $this->currentWeek = now();
        $this->updateWeekRange();

        // Force a dispatch of the weekChanged event regardless of whether the week has changed
        $this->lastDispatchedWeekRange = null; // Reset to force dispatch
        $this->dispatchWeekChangedIfNeeded();

        // Force a re-render to update the UI
        $this->dispatch('$refresh');
    }

    private function updateWeekRange()
    {
        $this->startOfWeek = $this->currentWeek->copy()->startOfWeek()->format('Y-m-d');
        $this->endOfWeek = $this->currentWeek->copy()->endOfWeek()->format('Y-m-d');
    }

    public function switchView($view)
    {
        $this->view = $view;
        if ($view === 'weekly') {
            $this->dispatchWeekChangedIfNeeded();
            // Force reload of Microsoft calendar events when switching to weekly view
            $this->dispatch('load-events');
        } elseif ($view === 'list') {
            // Force reload of Microsoft calendar events when switching to list view
            $this->dispatch('load-events');
        }
    }

    protected function dispatchWeekChangedIfNeeded()
    {
        $currentWeekRange = $this->startOfWeek.'-'.$this->endOfWeek;

        \Illuminate\Support\Facades\Log::info('TimeLogs dispatchWeekChangedIfNeeded check', [
            'current_week_range' => $currentWeekRange,
            'last_dispatched_week_range' => $this->lastDispatchedWeekRange,
            'is_same' => $this->lastDispatchedWeekRange === $currentWeekRange,
        ]);

        // Dispatch if the week range has changed or if lastDispatchedWeekRange is null (forced dispatch)
        if ($this->lastDispatchedWeekRange !== $currentWeekRange) {
            // Set the property before dispatching to prevent duplicate dispatches
            $this->lastDispatchedWeekRange = $currentWeekRange;

            \Illuminate\Support\Facades\Log::info('TimeLogs dispatching weekChanged', [
                'startOfWeek' => $this->startOfWeek,
                'endOfWeek' => $this->endOfWeek,
                'weekRange' => $currentWeekRange,
            ]);

            // Dispatch the event to both Microsoft calendar components
            $this->dispatch('weekChanged', $this->startOfWeek, $this->endOfWeek);
        } else {
            \Illuminate\Support\Facades\Log::info('TimeLogs skipping duplicate weekChanged dispatch', [
                'weekRange' => $currentWeekRange,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.time-logs-base', [
            'projects' => Project::where('user_id', Auth::id())->get(),
            'tags' => Tag::where('user_id', Auth::id())->get(),
            'showMicrosoftCalendar' => $this->showMicrosoftCalendar,
        ]);
    }
}
