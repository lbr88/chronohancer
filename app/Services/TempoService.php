<?php

namespace App\Services;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TempoService
{
    protected string $baseUrl;

    protected string $clientId;

    protected string $clientSecret;

    protected bool $enabled;

    protected bool $readOnly;

    protected string $redirectUri;

    /**
     * Create a new Tempo service instance.
     */
    public function __construct()
    {
        $this->baseUrl = config('tempo.base_url');
        $this->clientId = config('tempo.client_id');
        $this->clientSecret = config('tempo.client_secret');
        $this->enabled = config('tempo.enabled', false);
        $this->readOnly = config('tempo.read_only', false);
        $this->redirectUri = config('tempo.redirect_uri');
    }

    /**
     * Get the authorization URL for OAuth 2.0.
     */
    public function getAuthorizationUrl(User $user): string
    {
        if (! $user->hasJiraEnabled() || ! $user->jira_site_url) {
            throw new \Exception('Jira integration must be configured first');
        }

        $jiraUrl = rtrim($user->jira_site_url, '/');
        if (! str_starts_with($jiraUrl, 'https://')) {
            $jiraUrl = 'https://'.$jiraUrl;
        }
        if (! str_ends_with($jiraUrl, '.atlassian.net')) {
            $jiraUrl .= '.atlassian.net';
        }

        return $jiraUrl.'/plugins/servlet/ac/io.tempo.jira/oauth-authorize/?'.http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
        ]);
    }

    /**
     * Handle the OAuth callback and get access token.
     */
    public function handleCallback(string $code): array
    {
        try {
            $response = Http::asForm()->post('https://api.tempo.io/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'code' => $code,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Received Tempo OAuth tokens', [
                    'has_access_token' => isset($data['access_token']),
                    'has_refresh_token' => isset($data['refresh_token']),
                    'has_expires_in' => isset($data['expires_in']),
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            Log::error('Failed to get Tempo access token', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get access token: '.($response->json('message') ?? $response->status()),
            ];
        } catch (\Exception $e) {
            Log::error('Exception while getting Tempo access token: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Refresh the access token using a refresh token.
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = Http::asForm()->post('https://api.tempo.io/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'refresh_token' => $refreshToken,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Failed to refresh Tempo access token', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to refresh token: '.($response->json('message') ?? $response->status()),
            ];
        } catch (\Exception $e) {
            Log::error('Exception while refreshing Tempo access token: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check if the Tempo integration is enabled and properly configured.
     */
    public function isConfigured(): bool
    {
        return $this->enabled &&
            ! empty($this->clientId) &&
            ! empty($this->clientSecret) &&
            ! empty($this->baseUrl) &&
            ! empty($this->redirectUri);
    }

    /**
     * Test the connection by trying to get user info.
     */
    public function testConnection(User $user): bool
    {
        if (! $user->tempo_access_token) {
            return false;
        }

        try {
            // Try to get worklogs for today to test the connection
            $today = now()->format('Y-m-d');
            $response = Http::withToken($user->tempo_access_token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->get("{$this->baseUrl}/worklogs", [
                    'from' => $today,
                    'to' => $today,
                ]);

            if ($response->successful()) {
                $worklogs = $response->json();
                Log::info('Tempo test connection response', [
                    'status' => $response->status(),
                    'success' => true,
                    'user_id' => $user->id,
                    'worklogs' => $worklogs,
                    'worklog_count' => count($worklogs['results'] ?? []),
                ]);
            } else {
                Log::info('Tempo test connection response', [
                    'status' => $response->status(),
                    'success' => false,
                    'user_id' => $user->id,
                    'error' => $response->json(),
                ]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to test Tempo connection', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return false;
        }
    }

    /**
     * Check if the Tempo integration is in read-only mode.
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * Get an access token for the Tempo API.
     */
    public function getAccessToken(User $user): ?string
    {
        // Check if token needs refresh
        if ($this->needsTokenRefresh($user)) {
            $this->refreshUserToken($user);
        }

        return $user->tempo_access_token;
    }

    /**
     * Check if a user's token needs to be refreshed.
     */
    protected function needsTokenRefresh(User $user): bool
    {
        if (! $user->tempo_token_expires_at) {
            return true;
        }

        // Refresh if token expires in less than 1 hour
        return now()->addHour()->gt($user->tempo_token_expires_at);
    }

    /**
     * Refresh a user's access token.
     */
    protected function refreshUserToken(User $user): bool
    {
        if (! $user->tempo_refresh_token) {
            return false;
        }

        $result = $this->refreshToken($user->tempo_refresh_token);

        if ($result['success']) {
            $data = $result['data'];
            $user->update([
                'tempo_access_token' => $data['access_token'],
                'tempo_refresh_token' => $data['refresh_token'],
                'tempo_token_expires_at' => now()->addSeconds($data['expires_in']),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Store OAuth tokens for a user.
     */
    public function storeUserTokens(User $user, array $tokenData): void
    {
        Log::info('Storing Tempo OAuth tokens', [
            'user_id' => $user->id,
            'has_access_token' => isset($tokenData['access_token']),
            'has_refresh_token' => isset($tokenData['refresh_token']),
            'has_expires_in' => isset($tokenData['expires_in']),
            'token_data' => $tokenData,
        ]);

        $user->update([
            'tempo_access_token' => $tokenData['access_token'] ?? null,
            'tempo_refresh_token' => $tokenData['refresh_token'] ?? null,
            'tempo_token_expires_at' => isset($tokenData['expires_in']) ? now()->addSeconds($tokenData['expires_in']) : null,
            'tempo_enabled' => true,
        ]);
    }

    /**
     * Convert a TimeLog to a Tempo worklog.
     */
    protected function convertToWorklog(TimeLog $timeLog): array
    {
        $user = $timeLog->user;

        if (! $user->hasJiraEnabled()) {
            throw new \Exception("User {$user->id} does not have Jira integration enabled");
        }

        if (! $user->jira_account_id) {
            throw new \Exception("No Jira account ID found for user {$user->id}");
        }

        if (! $timeLog->jira_issue_id) {
            throw new \Exception("No Jira issue ID found for time log {$timeLog->id}");
        }

        // Format the description with project and tags
        $description = $timeLog->description ?? '';

        if ($timeLog->timer && $timeLog->timer->project) {
            $description = "[{$timeLog->timer->project->name}] ".$description;
        }

        if ($timeLog->tags && $timeLog->tags->count() > 0) {
            $tagNames = $timeLog->tags->pluck('name')->implode(', ');
            $description .= " [Tags: {$tagNames}]";
        }

        // Convert duration from minutes to seconds (Tempo API uses seconds)
        $durationSeconds = $timeLog->duration_minutes * 60;

        // Format the date in the required format (YYYY-MM-DD)
        $startDate = $timeLog->start_time->format('Y-m-d');

        // Create the worklog payload
        return [
            'authorAccountId' => $user->jira_account_id,
            'description' => $description,
            'startDate' => $startDate,
            'timeSpentSeconds' => $durationSeconds,
            'startTime' => $timeLog->start_time->format('H:i:s'),
            'issueId' => $timeLog->jira_issue_id,
            'remainingEstimateSeconds' => 0,
        ];
    }

    /**
     * Create a worklog in Tempo.
     */
    public function createWorklog(TimeLog $timeLog): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Tempo integration is not configured',
            ];
        }

        // Check if read-only mode is enabled
        if ($this->isReadOnly()) {
            return [
                'success' => false,
                'message' => 'Tempo integration is in read-only mode. Syncing is disabled.',
            ];
        }

        // Check if user has Jira enabled and time log has Jira issue
        if (! $timeLog->user->hasJiraEnabled() || ! $timeLog->jira_issue_id) {
            return [
                'success' => false,
                'message' => 'Jira integration is required and must be configured with an issue assigned',
            ];
        }

        // Skip if already synced and has a worklog ID, unless forced
        if ($timeLog->tempo_worklog_id && ! request()->boolean('force')) {
            return [
                'success' => true,
                'message' => 'Time log already synced to Tempo',
                'data' => ['tempoWorklogId' => $timeLog->tempo_worklog_id],
            ];
        }

        try {
            $user = $timeLog->user;
            if (! $user->tempo_enabled) {
                return [
                    'success' => false,
                    'message' => 'Tempo integration is not enabled for this user',
                ];
            }

            $token = $this->getAccessToken($user);
            if (! $token) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token',
                ];
            }

            $worklog = $this->convertToWorklog($timeLog);

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/worklogs", $worklog);

            if ($response->successful()) {
                // Update the time log with the Tempo worklog ID and sync timestamp
                $tempoWorklogId = $response->json('tempoWorklogId');
                $timeLog->update([
                    'tempo_worklog_id' => $tempoWorklogId,
                    'synced_to_tempo_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Worklog created successfully',
                    'data' => $response->json(),
                ];
            }

            Log::error('Failed to create Tempo worklog', [
                'status' => $response->status(),
                'response' => $response->json(),
                'worklog' => $worklog,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create worklog: '.($response->json('message') ?? $response->status()),
            ];
        } catch (\Exception $e) {
            Log::error('Exception while creating Tempo worklog: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get worklog details from Tempo.
     */
    public function getWorklogDetails(string $worklogId, User $user): ?array
    {
        if (! $this->isConfigured() || ! $user->tempo_enabled) {
            return null;
        }

        try {
            $token = $this->getAccessToken($user);
            if (! $token) {
                return null;
            }

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->get("{$this->baseUrl}/worklogs/{$worklogId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to get Tempo worklog details', [
                'status' => $response->status(),
                'response' => $response->json(),
                'worklogId' => $worklogId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while getting Tempo worklog details: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Sync multiple time logs to Tempo.
     */
    public function syncTimeLogs(array $timeLogIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Check if read-only mode is enabled
        if ($this->isReadOnly()) {
            return [
                'success' => 0,
                'failed' => count($timeLogIds),
                'errors' => [
                    [
                        'message' => 'Tempo integration is in read-only mode. Syncing is disabled.',
                    ],
                ],
            ];
        }

        $timeLogs = TimeLog::whereIn('id', $timeLogIds)->get();

        foreach ($timeLogs as $timeLog) {
            $result = $this->createWorklog($timeLog);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'time_log_id' => $timeLog->id,
                    'message' => $result['message'],
                ];
            }
        }

        return $results;
    }
}
