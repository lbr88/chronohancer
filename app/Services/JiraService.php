<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JiraService
{
    protected $user;

    protected $clientId;

    protected $clientSecret;

    protected $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.jira.client_id');
        $this->clientSecret = config('services.jira.client_secret');
        $this->redirectUri = config('services.jira.redirect_uri');
    }

    /**
     * Set the user for subsequent API calls.
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Check if Jira integration is enabled for the user.
     */
    public function isConfigured(): bool
    {
        return $this->user &&
            $this->user->jira_enabled &&
            $this->user->jira_access_token &&
            $this->user->jira_cloud_id &&
            $this->user->jira_site_url;
    }

    /**
     * Get the OAuth authorization URL.
     */
    public function getAuthorizationUrl(): string
    {
        $scopes = [
            'read:jira-work',
            'read:jira-user',
            'offline_access',
        ];

        // For 3LO (3-Legged OAuth), we need to use the Jira platform scope
        return 'https://auth.atlassian.com/authorize?'.http_build_query([
            'audience' => 'api.atlassian.com',
            'client_id' => $this->clientId,
            'scope' => 'read:jira-work read:jira-user offline_access',
            'redirect_uri' => $this->redirectUri,
            'state' => csrf_token(),
            'response_type' => 'code',
            'prompt' => 'consent',
        ]);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function handleCallback(string $code): array
    {
        // Exchange authorization code for access token
        $response = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        if (! $response->successful()) {
            Log::error('Failed to get Jira access token', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new \Exception('Failed to authenticate with Jira');
        }

        $tokenData = $response->json();

        // Get accessible resources (cloud instances) for the user
        $resourcesResponse = Http::withToken($tokenData['access_token'])
            ->get('https://api.atlassian.com/oauth/token/accessible-resources');

        if (! $resourcesResponse->successful() || empty($resourcesResponse->json())) {
            Log::error('Failed to get Jira cloud ID', [
                'status' => $resourcesResponse->status(),
                'response' => $resourcesResponse->json(),
            ]);
            throw new \Exception('Failed to get Jira cloud ID');
        }

        // Get the first cloud instance (most users only have one)
        $cloudInstance = $resourcesResponse->json()[0];

        return array_merge($tokenData, [
            'cloud_id' => $cloudInstance['id'],
            'site_url' => $cloudInstance['url'],
        ]);
    }

    /**
     * Refresh the access token.
     */
    protected function refreshToken(): bool
    {
        if (! $this->user->jira_refresh_token) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->user->jira_refresh_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->user->update([
                    'jira_access_token' => $data['access_token'],
                    'jira_refresh_token' => $data['refresh_token'],
                    'jira_token_expires_at' => now()->addSeconds($data['expires_in']),
                ]);

                return true;
            }

            Log::error('Failed to refresh Jira token', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while refreshing Jira token: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Make an authenticated request to the Jira API.
     */
    protected function request(string $method, string $endpoint, array $data = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        // Check if token needs refresh
        if (
            $this->user->jira_token_expires_at &&
            Carbon::parse($this->user->jira_token_expires_at)->subMinutes(5)->isPast()
        ) {
            if (! $this->refreshToken()) {
                return null;
            }
        }

        try {
            $baseUrl = "https://api.atlassian.com/ex/jira/{$this->user->jira_cloud_id}";
            $response = Http::withToken($this->user->jira_access_token)
                ->withHeaders(['Accept' => 'application/json'])
                ->{$method}($baseUrl.$endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            $errorResponse = $response->json();
            Log::error('Failed Jira API request', [
                'endpoint' => $endpoint,
                'method' => $method,
                'data' => $data,
                'status' => $response->status(),
                'response' => $errorResponse,
                'error_messages' => $errorResponse['errorMessages'] ?? null,
                'errors' => $errorResponse['errors'] ?? null,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception during Jira API request: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get issue details by ID or key.
     */
    public function getIssue(string $issueIdOrKey): ?array
    {
        $cacheKey = "jira_issue:{$this->user->id}:{$issueIdOrKey}";

        // Try to get from cache first
        if ($cached = cache()->get($cacheKey)) {
            return $cached;
        }

        // If not in cache, fetch from API
        $issue = $this->request('get', "/rest/api/3/issue/{$issueIdOrKey}");

        if ($issue) {
            // Cache for 5 minutes
            cache()->put($cacheKey, $issue, now()->addMinutes(5));
        }

        return $issue;
    }

    /**
     * Search for issues.
     */
    public function searchIssues(string $jql = '', int $maxResults = 10, int $startAt = 0): array
    {
        $cacheKey = "jira_search:{$this->user->id}:".md5($jql.$maxResults.$startAt);

        // Try to get from cache first
        if ($cached = cache()->get($cacheKey)) {
            return $cached;
        }

        $response = $this->request('post', '/rest/api/3/search', [
            'jql' => $jql ?: 'order by updated DESC',
            'maxResults' => $maxResults,
            'startAt' => $startAt,
            'fields' => [
                'summary',
                'status',
                'assignee',
                'reporter',
                'updated',
                'created',
                'priority',
                'issuetype',
                'labels',
                'customfield_10014', // Epic link
                'parent', // Parent issue for subtasks
                'project',
                'description',
                'comment',
            ],
        ]);

        $result = [
            'issues' => collect($response['issues'] ?? []),
            'total' => $response['total'] ?? 0,
            'startAt' => $response['startAt'] ?? 0,
        ];

        // Cache for 2 minutes since search results change more frequently
        cache()->put($cacheKey, $result, now()->addMinutes(2));

        return $result;
    }

    /**
     * Get issue ID from issue key.
     */
    public function getIssueId(string $issueKey): ?string
    {
        $issue = $this->getIssue($issueKey);

        return $issue['id'] ?? null;
    }

    /**
     * Get issue key from issue ID.
     */
    public function getIssueKey(string $issueId): ?string
    {
        $issue = $this->getIssue($issueId);

        return $issue['key'] ?? null;
    }

    /**
     * Validate that an issue exists and is accessible.
     */
    public function validateIssue(string $issueIdOrKey): bool
    {
        return $this->getIssue($issueIdOrKey) !== null;
    }

    // Removed getFavoriteIssues method as Jira's API doesn't support getting starred items
}
