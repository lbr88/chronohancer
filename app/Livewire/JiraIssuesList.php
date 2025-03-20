<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use App\Models\TimeLog;
use App\Models\Timer as TimerModel;
use App\Services\JiraService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class JiraIssuesList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public int $page = 1;

    public int $perPage = 10;

    public bool $showMyIssues = true;

    public bool $showDoneIssues = false;

    // Properties for timer creation modal
    public bool $showTimerModal = false;

    public string $issueKey = '';

    public string $issueSummary = '';

    public array $issueLabels = [];

    public string $timerAction = 'create'; // 'create' or 'start'

    public ?int $projectId = null;

    public ?string $description = '';

    protected $jiraService;

    protected $listeners = [
        'project-selected' => 'handleProjectSelected',
        'description-selected' => 'handleDescriptionSelected',
    ];

    public function boot(JiraService $jiraService)
    {
        $this->jiraService = $jiraService->setUser(auth()->user());
    }

    public function mount()
    {
        if (! auth()->user()->hasJiraEnabled()) {
            return;
        }
    }

    public $total = 0;

    #[Computed]
    public function issues()
    {
        if (! auth()->user()->hasJiraEnabled()) {
            return collect();
        }

        try {
            $jql = [];

            // Add status filter by default
            if (! $this->showDoneIssues) {
                $jql[] = 'status not in (Done, Solved, Closed, Resolved)';
            }

            // Add search filter if provided
            if ($this->search) {
                $searchTerm = $this->search;
                $searchConditions = [];

                // Split search term into words and clean them
                $words = array_filter(preg_split('/\s+/', trim($searchTerm)));
                $searchConditions = [];

                // Handle exact Jira key matches first
                foreach ($words as $word) {
                    if (preg_match('/^[A-Z]+-\d+$/i', $word)) {
                        $jql[] = sprintf('key = "%s"', strtoupper($word));
                        break; // Found a key match, no need to continue with other search conditions
                    }
                }

                // Create text search condition
                $searchText = implode(' ', array_map(function ($word) {
                    return strtolower($word).'*';
                }, $words));

                if (! empty($searchText)) {
                    // Search in text fields
                    $searchConditions[] = sprintf('text ~ "%s"', $searchText);

                    // Also search specifically in summary for better matching
                    $searchConditions[] = sprintf('summary ~ "%s"', $searchText);
                }

                // Add priority and type conditions if any word matches
                foreach ($words as $word) {
                    $lowerWord = strtolower($word);
                    if (preg_match('/^(highest|high|medium|low|lowest)$/i', $word)) {
                        $searchConditions[] = sprintf('priority = "%s"', ucfirst($lowerWord));
                    }
                    if (preg_match('/^(bug|task|story|epic|feature)$/i', $word)) {
                        $searchConditions[] = sprintf('type = "%s"', ucfirst($lowerWord));
                    }
                }

                // Combine all conditions
                if (! empty($searchConditions)) {
                    $jql[] = '('.implode(' OR ', $searchConditions).')';
                }
            }

            // Add my issues filter
            if ($this->showMyIssues) {
                $jql[] = '(assignee = currentUser() OR reporter = currentUser())';
            }

            // Favorites filter removed as Jira's API doesn't support getting starred items

            // Combine conditions and add ordering
            $finalQuery = implode(' AND ', $jql).' ORDER BY updated DESC';

            // Log the query for debugging
            logger()->info('Jira search query', ['query' => $finalQuery]);

            $response = $this->jiraService->searchIssues($finalQuery, $this->perPage, ($this->page - 1) * $this->perPage);
            $this->total = $response['total'];

            return $response['issues'];
        } catch (\Exception $e) {
            // Log detailed error information
            logger()->error('Jira issues fetch failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $finalQuery ?? null,
                'user_id' => auth()->id(),
                'jira_enabled' => auth()->user()->jira_enabled,
                'has_access_token' => ! empty(auth()->user()->jira_access_token),
                'has_cloud_id' => ! empty(auth()->user()->jira_cloud_id),
                'has_site_url' => ! empty(auth()->user()->jira_site_url),
            ]);

            return collect();
        }
    }

    // Removed favoriteIssueIds computed property as Jira's API doesn't support getting starred items

    #[Computed]
    public function existingTimerIssueKeys(): Collection
    {
        $timers = TimerModel::where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id)
            ->get();

        logger()->info('Active timers found:', [
            'count' => $timers->count(),
            'names' => $timers->pluck('jirakey')->toArray(),
            'jira_keys' => $timers->map->jiraKey->filter()->toArray(),
        ]);

        return collect($timers->map->jiraKey->filter()->values());
    }

    /**
     * Get existing timer for a Jira issue key
     */
    #[Computed]
    public function existingTimers(): Collection
    {
        return TimerModel::where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id)
            ->get()
            ->keyBy(function ($timer) {
                return $timer->jiraKey;
            });
    }
    /**
     * Note: We're using a read-only approach for favorites.
     * The toggleFavorite method has been removed as we only have read access to Jira.
     * Favorites are stored locally in the database and can only be viewed, not modified.
     */
    // Removed toggleFavorite method as we're using read-only favorites

    /**
     * Open the timer modal for creating or starting a timer
     */
    public function openTimerModal(string $issueKey, string $summary, array $labels = [], string $action = 'create'): void
    {
        $this->issueKey = $issueKey;
        $this->issueSummary = $summary;
        $this->issueLabels = $labels;
        $this->timerAction = $action;

        // Reset project and description
        $this->projectId = null;
        $this->description = '';

        // If we're starting an existing timer, get the default project
        if ($action === 'start') {
            $workspace = app('current.workspace');
            if ($workspace) {
                $defaultProject = Project::findOrCreateDefault(auth()->id(), $workspace->id);
                $this->projectId = $defaultProject->id;
            }
        }

        $this->showTimerModal = true;
    }

    /**
     * Close the timer modal
     */
    public function closeTimerModal(): void
    {
        $this->showTimerModal = false;
        $this->issueKey = '';
        $this->issueSummary = '';
        $this->issueLabels = [];
        $this->projectId = null;
        $this->description = '';
    }

    /**
     * Handle project selection from the project selector component
     */
    public function handleProjectSelected($data): void
    {
        if (isset($data['id'])) {
            $this->projectId = $data['id'];
        }
    }

    /**
     * Handle description selection from the timer description selector component
     */
    public function handleDescriptionSelected($data): void
    {
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
    }

    /**
     * Create a timer for a Jira issue
     */
    public function createTimer(?string $issueKey = null, ?string $summary = null, array $labels = []): void
    {
        try {
            // If called directly from the view, open the modal instead
            if ($this->showTimerModal === false && $issueKey !== null) {
                $this->openTimerModal($issueKey, $summary, $labels, 'create');

                return;
            }

            // Use modal values if not provided directly
            $issueKey = $issueKey ?? $this->issueKey;
            $summary = $summary ?? $this->issueSummary;
            $labels = ! empty($labels) ? $labels : $this->issueLabels;

            $workspace = app('current.workspace');
            if (! $workspace) {
                logger()->error('Failed to create timer: No workspace found');
                $this->dispatch('notify', type: 'error', message: 'Failed to create timer: No workspace found');

                return;
            }

            // Use selected project or default
            $projectId = $this->projectId;
            if (! $projectId) {
                $defaultProject = Project::findOrCreateDefault(auth()->id(), $workspace->id);
                $projectId = $defaultProject->id;
            }

            logger()->info('Creating timer', [
                'issueKey' => $issueKey,
                'summary' => $summary,
                'workspace_id' => $workspace->id,
                'project_id' => $projectId,
            ]);

            $timer = TimerModel::create([
                'user_id' => auth()->id(),
                'workspace_id' => $workspace->id,
                'project_id' => $projectId,
                'name' => "$issueKey: $summary",
                'jira_key' => $issueKey,
                'is_running' => true,
            ]);

            // Create time log with description
            $timeLog = TimeLog::create([
                'timer_id' => $timer->id,
                'user_id' => auth()->id(),
                'start_time' => now(),
                'description' => $this->description ?: null,
                'workspace_id' => $workspace->id,
            ]);

            // Create tags from Jira labels
            if (! empty($labels)) {
                $tags = collect($labels)->map(function ($label) use ($workspace) {
                    return Tag::findOrCreateForUser($label, auth()->id(), $workspace->id);
                });

                // Attach tags to timer
                $timer->tags()->attach($tags->pluck('id'));

                // Also attach tags to the project
                $project = Project::find($projectId);
                if ($project) {
                    $project->tags()->syncWithoutDetaching($tags->pluck('id'));
                }

                logger()->info('Created timer with tags', [
                    'timer_id' => $timer->id,
                    'tags' => $tags->pluck('name'),
                ]);
            }

            $this->closeTimerModal();
            $this->dispatch('timer-created', timerId: $timer->id);
            $this->dispatch('refresh-timers');
            $this->dispatch('notify', type: 'success', message: 'Timer created successfully');
        } catch (\Exception $e) {
            logger()->error('Failed to create timer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'issueKey' => $issueKey,
                'summary' => $summary,
            ]);
            $this->dispatch('notify', type: 'error', message: 'Failed to create timer: '.$e->getMessage());
        }
    }

    /**
     * Start an existing timer for a Jira issue
     */
    public function startTimer(string $issueKey): void
    {
        try {
            // If called directly from the view, open the modal instead
            if ($this->showTimerModal === false) {
                $existingTimers = $this->existingTimers;
                $timer = $existingTimers[$issueKey] ?? null;

                if (! $timer) {
                    $this->dispatch('notify', type: 'error', message: 'Timer not found for issue: '.$issueKey);

                    return;
                }

                $this->openTimerModal($issueKey, str_replace("$issueKey: ", '', $timer->name), [], 'start');

                return;
            }

            $existingTimers = $this->existingTimers;
            $timer = $existingTimers[$this->issueKey] ?? null;

            if (! $timer) {
                $this->dispatch('notify', type: 'error', message: 'Timer not found for issue: '.$this->issueKey);

                return;
            }

            $workspace = app('current.workspace');
            if (! $workspace) {
                $this->dispatch('notify', type: 'error', message: 'Failed to start timer: No workspace found');

                return;
            }

            // Mark timer as running
            $timer->is_running = true;
            $timer->is_paused = false;
            $timer->save();

            // Use selected project if provided
            if ($this->projectId) {
                $timer->project_id = $this->projectId;
                $timer->save();
            }

            // Create a new time log with description
            $timeLog = TimeLog::create([
                'timer_id' => $timer->id,
                'user_id' => auth()->id(),
                'start_time' => now(),
                'description' => $this->description ?: null,
                'workspace_id' => $workspace->id,
            ]);

            $this->closeTimerModal();
            $this->dispatch('timerStarted', ['timerId' => $timer->id, 'startTime' => $timeLog->start_time->toIso8601String()]);
            $this->dispatch('refresh-timers');
            $this->dispatch('notify', type: 'success', message: 'Timer started successfully');
        } catch (\Exception $e) {
            logger()->error('Failed to start timer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'issueKey' => $this->issueKey,
            ]);
            $this->dispatch('notify', type: 'error', message: 'Failed to start timer: '.$e->getMessage());
        }
    }

    /**
     * Submit the timer form (create or start)
     */
    public function submitTimerForm(): void
    {
        if ($this->timerAction === 'create') {
            $this->createTimer();
        } else {
            $this->startTimer($this->issueKey);
        }
    }

    public function nextPage(): void
    {
        if (($this->page * $this->perPage) < $this->total) {
            $this->page++;
        } else {
            $this->page = 1;
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        } else {
            $this->page = (int) ceil($this->total / $this->perPage);
        }
    }

    public function toggleMyIssues(): void
    {
        $this->showMyIssues = ! $this->showMyIssues;
        $this->resetPage();
    }

    public function toggleDoneIssues(): void
    {
        $this->showDoneIssues = ! $this->showDoneIssues;
        $this->resetPage();
    }

    // Removed toggleFavoriteFilter method as Jira's API doesn't support getting starred items

    public function render()
    {
        return view('livewire.jira-issues-list', [
            'issues' => $this->issues,
            'existingTimerIssueKeys' => $this->existingTimerIssueKeys,
            'isConfigured' => auth()->user()->hasJiraEnabled(),
        ]);
    }
}
