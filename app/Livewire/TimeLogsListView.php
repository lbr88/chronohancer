<?php

namespace App\Livewire;

use App\Livewire\Traits\TimeLogsUtilities;
use App\Models\Project;
use App\Models\TimeLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeLogsListView extends Component
{
    use TimeLogsUtilities;

    public $sortField = 'start_time';

    public $sortDirection = 'desc';

    public $selectedTimeLogs = [];

    public $selectAll = false;

    public $confirmingBulkDelete = false;

    // Filter properties (received from TimeLogsFilters)
    public $filterProject = null;

    public $filterTag = null;

    public $filterDateFrom = null;

    public $filterDateTo = null;

    public $searchQuery = '';

    // Week properties (received from TimeLogsBase)
    public $startOfWeek;

    public $endOfWeek;

    public $currentWeek;

    // Query string parameters
    protected $queryString = [
        'sortField' => ['except' => 'start_time'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'filters-changed' => 'handleFiltersChanged',
        'timeLogSaved' => '$refresh',
        'timeLogDeleted' => '$refresh',
    ];

    public function mount($startOfWeek = null, $endOfWeek = null, $currentWeek = null)
    {
        $this->startOfWeek = $startOfWeek ?? now()->startOfWeek()->format('Y-m-d');
        $this->endOfWeek = $endOfWeek ?? now()->endOfWeek()->format('Y-m-d');
        $this->currentWeek = $currentWeek ?? now();

        // Set filter dates to current week by default
        $this->filterDateFrom = $this->startOfWeek;
        $this->filterDateTo = $this->endOfWeek;
    }

    public function handleFiltersChanged($filters)
    {
        $this->filterProject = $filters['filterProject'];
        $this->filterTag = $filters['filterTag'];
        $this->filterDateFrom = $filters['filterDateFrom'];
        $this->filterDateTo = $filters['filterDateTo'];
        $this->searchQuery = $filters['searchQuery'];

        // Reset selection when filters change
        $this->selectedTimeLogs = [];
        $this->selectAll = false;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleSelectAll()
    {
        $this->selectAll = ! $this->selectAll;
        if ($this->selectAll) {
            // Get all visible time log IDs based on current filters
            $this->selectedTimeLogs = $this->getFilteredQuery()->pluck('id')->toArray();
        } else {
            $this->selectedTimeLogs = [];
        }
    }

    public function updatedSelectedTimeLogs()
    {
        // Get the count of all visible time logs based on current filters
        $totalCount = $this->getFilteredQuery()->count();
        $selectedCount = count($this->selectedTimeLogs);

        // Update selectAll based on whether all visible logs are selected
        $this->selectAll = ($selectedCount > 0 && $selectedCount >= $totalCount);
    }

    public function confirmBulkDelete()
    {
        if (count($this->selectedTimeLogs) > 0) {
            $this->confirmingBulkDelete = true;
        }
    }

    public function cancelBulkDelete()
    {
        $this->confirmingBulkDelete = false;
    }

    public function bulkDeleteTimeLogs()
    {
        if (count($this->selectedTimeLogs) > 0) {
            TimeLog::whereIn('id', $this->selectedTimeLogs)
                ->where('user_id', Auth::id()) // Security check
                ->delete();

            $count = count($this->selectedTimeLogs);
            $this->selectedTimeLogs = [];
            $this->selectAll = false;
            $this->confirmingBulkDelete = false;

            // Dispatch event to update the daily progress bar
            $this->dispatch('timeLogSaved');

            session()->flash('message', $count.' time '.($count === 1 ? 'log' : 'logs').' deleted successfully.');
        }
    }

    /**
     * Get the filtered query for time logs
     */
    protected function getFilteredQuery()
    {
        $query = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time'); // Only show logs that have an end time (completed logs)

        // Apply filters
        if ($this->filterProject) {
            $query->whereHas('timer', function ($q) {
                $q->where('project_id', $this->filterProject);
            });
        }

        if ($this->filterTag) {
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->filterTag);
            });
        }

        if ($this->filterDateFrom) {
            $query->where('start_time', '>=', $this->filterDateFrom.' 00:00:00');
        }

        if ($this->filterDateTo) {
            $query->where('start_time', '<=', $this->filterDateTo.' 23:59:59');
        }

        // Handle search query
        if ($this->searchQuery) {
            $searchTerm = trim($this->searchQuery);
            $searchTermLower = strtolower($searchTerm);

            // Check if search term contains any part of "no project" or default project
            $matchesDefaultProject = str_contains('no project', $searchTermLower) ||
              str_contains($searchTermLower, 'no') ||
              str_contains($searchTermLower, 'project');

            // Get the default project
            $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);

            // Apply all search conditions
            $query->where(function ($q) use ($searchTerm, $defaultProject, $matchesDefaultProject) {
                // Regular search in description and project name (through timer)
                $q->where('description', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('timer.project', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%');
                    });

                // Include default project results if the search term matches
                if ($matchesDefaultProject) {
                    $q->orWhereHas('timer', function ($q) use ($defaultProject) {
                        $q->where('project_id', $defaultProject->id);
                    });
                }
            });
        }

        return $query;
    }

    public function render()
    {
        $query = $this->getFilteredQuery();

        // Apply sorting
        if ($this->sortField === 'project') {
            $query->join('timers', 'time_logs.timer_id', '=', 'timers.id')
                ->join('projects', 'timers.project_id', '=', 'projects.id')
                ->orderBy('projects.name', $this->sortDirection)
                ->select('time_logs.*');
        } elseif ($this->sortField === 'duration') {
            $query->orderBy('duration_minutes', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        // Get the filtered time logs
        $timeLogs = $query->with(['timer.project' => function ($q) {
            $q->withTrashed(); // Include soft-deleted projects
        }, 'tags', 'timer'])->get();

        // Calculate total duration of filtered logs
        $totalFilteredDuration = $timeLogs->sum('duration_minutes');

        return view('livewire.time-logs-list-view', [
            'timeLogs' => $timeLogs,
            'totalFilteredDuration' => $totalFilteredDuration,
        ]);
    }
}
