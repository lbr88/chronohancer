<?php

namespace App\Livewire;

use App\Models\FavoriteJiraIssue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Timer;
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
        if (empty($this->search)) {
            $this->resetPage();
        }
    }

    #[Url]
    public int $page = 1;

    public int $perPage = 10;

    public bool $showFavoritesOnly = false;

    public bool $showMyIssues = true;

    public bool $showDoneIssues = false;

    protected $jiraService;

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

                // Split search term into words
                $words = preg_split('/\s+/', trim($searchTerm));

                // Check each word for Jira key pattern
                foreach ($words as $word) {
                    if (preg_match('/^[A-Z]+-\d+$/i', $word)) {
                        $searchConditions[] = sprintf('key = "%s"', strtoupper($word));
                    }
                }

                // Add text-based search only
                if (! empty($searchTerm)) {
                    $term = strtoupper($searchTerm);
                    // If it looks like a complete key (e.g., PROJ-123)
                    if (preg_match('/^[A-Z]+-\d+$/', $term)) {
                        $searchConditions[] = sprintf('key = "%s"', $term);
                    }
                }

                // Create fuzzy search term
                $fuzzyTerm = implode('*', $words).'*';

                // Add text-based search
                $searchConditions[] = sprintf('text ~ "%s"', $fuzzyTerm);
                $jql[] = sprintf('(%s)',
                    implode(' OR ', $searchConditions)
                );
            }

            // Add my issues filter
            if ($this->showMyIssues) {
                $jql[] = '(assignee = currentUser() OR reporter = currentUser())';
            }

            // Add favorites filter
            if ($this->showFavoritesOnly) {
                $favoriteIds = auth()->user()->favoriteJiraIssues()
                    ->where('workspace_id', app('current.workspace')->id)
                    ->pluck('jira_issue_id');

                if ($favoriteIds->isEmpty()) {
                    return collect();
                }

                $jql[] = 'id in ('.$favoriteIds->join(',').')';
            }

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

    #[Computed]
    public function favoriteIssueIds(): Collection
    {
        return auth()->user()->favoriteJiraIssues()
            ->where('workspace_id', app('current.workspace')->id)
            ->pluck('jira_issue_id');
    }

    public function toggleFavorite(string $issueId, string $key, string $title, ?string $status): void
    {
        $user = auth()->user();
        $workspace = app('current.workspace');

        $existing = FavoriteJiraIssue::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->where('jira_issue_id', $issueId)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            FavoriteJiraIssue::create([
                'user_id' => $user->id,
                'workspace_id' => $workspace->id,
                'jira_issue_id' => $issueId,
                'key' => $key,
                'title' => $title,
                'status' => $status,
            ]);
        }

        $this->dispatch('favorite-toggled');
    }

    public function createTimer(string $issueKey, string $summary, array $labels = []): void
    {
        try {
            $workspace = app('current.workspace');
            if (! $workspace) {
                logger()->error('Failed to create timer: No workspace found');
                $this->dispatch('notify', type: 'error', message: 'Failed to create timer: No workspace found');

                return;
            }

            $defaultProject = Project::findOrCreateDefault(auth()->id(), $workspace->id);

            logger()->info('Creating timer', [
                'issueKey' => $issueKey,
                'summary' => $summary,
                'workspace_id' => $workspace->id,
                'project_id' => $defaultProject->id,
            ]);

            $timer = Timer::create([
                'user_id' => auth()->id(),
                'workspace_id' => $workspace->id,
                'project_id' => $defaultProject->id,
                'name' => "$issueKey: $summary",
                'start_time' => now(),
            ]);

            // Create tags from Jira labels
            if (! empty($labels)) {
                $tags = collect($labels)->map(function ($label) use ($workspace) {
                    return Tag::findOrCreateForUser($label, auth()->id(), $workspace->id);
                });

                // Attach tags to timer
                $timer->tags()->attach($tags->pluck('id'));

                // Also attach tags to the default project
                $defaultProject->tags()->syncWithoutDetaching($tags->pluck('id'));

                logger()->info('Created timer with tags', [
                    'timer_id' => $timer->id,
                    'tags' => $tags->pluck('name'),
                ]);
            }

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

    public function nextPage(): void
    {
        if (($this->page * $this->perPage) < $this->total) {
            $this->page++;
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
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

    public function toggleFavoriteFilter(): void
    {
        $this->showFavoritesOnly = ! $this->showFavoritesOnly;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.jira-issues-list', [
            'issues' => $this->issues,
            'favoriteIds' => $this->favoriteIssueIds,
            'isConfigured' => auth()->user()->hasJiraEnabled(),
        ]);
    }
}
