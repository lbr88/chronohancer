<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JiraSearch extends Component
{
    public $searchTerm = '';

    public $searchResults = [];

    public $showDropdown = false;

    protected $listeners = [
        'clear-jira-search' => 'clearSearch',
    ];

    public function clearSearch()
    {
        $this->searchTerm = '';
        $this->searchResults = [];
        $this->showDropdown = false;
    }

    public function updatedSearchTerm()
    {
        if (empty($this->searchTerm)) {
            $this->searchResults = [];
            $this->showDropdown = false;

            return;
        }

        // Only search if we have at least 2 characters
        if (strlen($this->searchTerm) >= 2) {
            $this->showDropdown = true;
            $this->performSearch();
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;
        if ($this->showDropdown && ! empty($this->searchTerm)) {
            $this->performSearch();
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function performSearch()
    {
        $user = Auth::user();

        // Check if Jira is configured
        if (! ($user->jira_enabled && $user->jira_access_token && $user->jira_cloud_id && $user->jira_site_url)) {
            $this->searchResults = [];
            $this->showDropdown = false;

            return;
        }

        try {
            $jiraService = app(\App\Services\JiraService::class)->setUser($user);
            $jql = [];

            $searchTerm = trim($this->searchTerm);
            $words = array_filter(preg_split('/\s+/', $searchTerm));

            // Handle exact Jira key matches first
            foreach ($words as $word) {
                if (preg_match('/^[A-Z]+-\d+$/i', $word)) {
                    $jql[] = sprintf('key = "%s"', strtoupper($word));
                    break;
                }
            }

            if (empty($jql)) {
                // Create text search condition
                $searchText = implode(' ', array_map(function ($word) {
                    return strtolower($word).'*';
                }, $words));

                if (! empty($searchText)) {
                    $jql[] = sprintf('(text ~ "%s" OR summary ~ "%s")', $searchText, $searchText);
                }
            }

            // Add status filter by default
            $jql[] = 'status not in (Done, Solved, Closed, Resolved)';

            // Combine conditions and add ordering
            $finalQuery = implode(' AND ', $jql).' ORDER BY updated DESC';

            $response = $jiraService->searchIssues($finalQuery, 5, 0);

            $this->searchResults = $response['issues'] ?? [];
            $this->showDropdown = ! empty($this->searchResults);

        } catch (\Exception $e) {
            logger()->error('Jira issues fetch failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->searchResults = [];
            $this->showDropdown = false;
        }
    }

    public function selectIssue($key, $summary)
    {
        $this->dispatch('jira-issue-selected', [
            'key' => $key,
            'name' => "$key: $summary",
            'jiraKey' => $key,
        ]);

        $this->clearSearch();
    }

    public function render()
    {
        return view('livewire.components.jira-search');
    }
}
