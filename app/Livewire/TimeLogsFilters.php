<?php

namespace App\Livewire;

use App\Livewire\Traits\TimeLogsUtilities;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeLogsFilters extends Component
{
    use TimeLogsUtilities;

    public $filterProject = null;

    public $filterTag = null;

    public $filterDateFrom = null;

    public $filterDateTo = null;

    public $searchQuery = '';

    public $showFilters = false;

    public $startOfWeek;

    public $endOfWeek;

    // Query string parameters
    protected $queryString = [
        'filterProject' => ['except' => null],
        'filterTag' => ['except' => null],
        'searchQuery' => ['except' => ''],
    ];

    protected $listeners = [
        'week-changed' => 'handleWeekChanged',
    ];

    public function mount($startOfWeek = null, $endOfWeek = null)
    {
        $this->startOfWeek = $startOfWeek ?? now()->startOfWeek()->format('Y-m-d');
        $this->endOfWeek = $endOfWeek ?? now()->endOfWeek()->format('Y-m-d');

        // Set filter dates to current week by default
        $this->filterDateFrom = $this->startOfWeek;
        $this->filterDateTo = $this->endOfWeek;
    }

    public function handleWeekChanged($startOfWeek, $endOfWeek)
    {
        $this->startOfWeek = $startOfWeek;
        $this->endOfWeek = $endOfWeek;
        $this->updateFilterDates();
    }

    /**
     * Update filter dates to match the current week
     */
    public function updateFilterDates()
    {
        $this->filterDateFrom = $this->startOfWeek;
        $this->filterDateTo = $this->endOfWeek;
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function resetFilters()
    {
        $this->filterProject = null;
        $this->filterTag = null;
        $this->filterDateFrom = $this->startOfWeek;
        $this->filterDateTo = $this->endOfWeek;
        $this->searchQuery = '';

        // Notify parent component that filters have changed
        $this->dispatch('filters-changed', [
            'filterProject' => $this->filterProject,
            'filterTag' => $this->filterTag,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'searchQuery' => $this->searchQuery,
        ]);
    }

    /**
     * This method is automatically called by Livewire when the searchQuery property is updated
     */
    public function updatedSearchQuery()
    {
        $this->dispatch('filters-changed', [
            'filterProject' => $this->filterProject,
            'filterTag' => $this->filterTag,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'searchQuery' => $this->searchQuery,
        ]);
    }

    /**
     * This method is called when any filter is updated
     */
    public function updatedFilterProject()
    {
        $this->dispatch('filters-changed', [
            'filterProject' => $this->filterProject,
            'filterTag' => $this->filterTag,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'searchQuery' => $this->searchQuery,
        ]);
    }

    public function updatedFilterTag()
    {
        $this->dispatch('filters-changed', [
            'filterProject' => $this->filterProject,
            'filterTag' => $this->filterTag,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'searchQuery' => $this->searchQuery,
        ]);
    }

    public function updatedFilterDateFrom()
    {
        $this->dispatch('filters-changed', [
            'filterProject' => $this->filterProject,
            'filterTag' => $this->filterTag,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'searchQuery' => $this->searchQuery,
        ]);
    }

    public function updatedFilterDateTo()
    {
        $this->dispatch('filters-changed', [
            'filterProject' => $this->filterProject,
            'filterTag' => $this->filterTag,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'searchQuery' => $this->searchQuery,
        ]);
    }

    /**
     * Get the current filter state
     */
    public function getFilterState()
    {
        return [
            'filterProject' => $this->filterProject,
            'filterTag' => $this->filterTag,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'searchQuery' => $this->searchQuery,
        ];
    }

    public function render()
    {
        return view('livewire.time-logs-filters', [
            'projects' => Project::where('user_id', Auth::id())->get(),
            'tags' => \App\Models\Tag::where('user_id', Auth::id())->get(),
        ]);
    }
}
