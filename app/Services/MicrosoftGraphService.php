<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphService
{
    protected $user;

    protected $clientId;

    protected $clientSecret;

    protected $redirectUri;

    protected $tenantId;

    public function __construct()
    {
        $this->clientId = config('services.microsoft-graph.client_id');
        $this->clientSecret = config('services.microsoft-graph.client_secret');
        $this->redirectUri = config('services.microsoft-graph.redirect');
        $this->tenantId = config('services.microsoft-graph.tenant');
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
     * Check if Microsoft Graph integration is enabled for the user.
     */
    public function isConfigured(): bool
    {
        $hasUser = $this->user !== null;
        $isEnabled = $hasUser && $this->user->microsoft_enabled;
        $hasAccessToken = $hasUser && ! empty($this->user->microsoft_access_token);
        $hasRefreshToken = $hasUser && ! empty($this->user->microsoft_refresh_token);

        $isConfigured = $hasUser && $isEnabled && $hasAccessToken && $hasRefreshToken;

        Log::info('Microsoft Graph integration configuration check', [
            'user_id' => $hasUser ? $this->user->id : null,
            'has_user' => $hasUser,
            'is_enabled' => $isEnabled,
            'has_access_token' => $hasAccessToken,
            'has_refresh_token' => $hasRefreshToken,
            'is_configured' => $isConfigured,
        ]);

        return $isConfigured;
    }

    /**
     * Get the OAuth authorization URL with calendar permissions.
     */
    public function getAuthorizationUrl(): string
    {
        $scopes = [
            'offline_access',
            'User.Read',
            'Calendars.Read',
            'Calendars.Read.Shared', // Add scope for shared calendars
            // Removed 'Calendars.ReadWrite' to restrict to read-only access
        ];

        return 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/authorize?'.http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'response_mode' => 'query',
            'scope' => implode(' ', $scopes),
            'state' => csrf_token(),
        ]);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function handleCallback(string $code): array
    {
        // Exchange authorization code for access token
        $response = Http::asForm()->post('https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        if (! $response->successful()) {
            Log::error('Failed to get Microsoft access token', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new \Exception('Failed to authenticate with Microsoft');
        }

        $tokenData = $response->json();

        // Get user information
        $userResponse = Http::withToken($tokenData['access_token'])
            ->get('https://graph.microsoft.com/v1.0/me');

        if (! $userResponse->successful()) {
            Log::error('Failed to get Microsoft user info', [
                'status' => $userResponse->status(),
                'response' => $userResponse->json(),
            ]);
            throw new \Exception('Failed to get Microsoft user info');
        }

        $userData = $userResponse->json();

        return array_merge($tokenData, [
            'user_id' => $userData['id'],
            'user_email' => $userData['userPrincipalName'],
        ]);
    }

    /**
     * Refresh the access token.
     */
    protected function refreshToken(): bool
    {
        Log::info('Attempting to refresh Microsoft token', [
            'user_id' => $this->user->id,
            'has_refresh_token' => ! empty($this->user->microsoft_refresh_token),
        ]);

        if (! $this->user->microsoft_refresh_token) {
            Log::warning('Cannot refresh Microsoft token - no refresh token available', [
                'user_id' => $this->user->id,
            ]);

            return false;
        }

        try {
            $tokenUrl = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';

            Log::info('Making Microsoft token refresh request', [
                'user_id' => $this->user->id,
                'token_url' => $tokenUrl,
                'client_id' => $this->clientId,
                'tenant_id' => $this->tenantId,
            ]);

            $response = Http::asForm()->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->user->microsoft_refresh_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Microsoft token refresh successful', [
                    'user_id' => $this->user->id,
                    'expires_in' => $data['expires_in'] ?? 'unknown',
                    'has_new_refresh_token' => isset($data['refresh_token']),
                ]);

                $this->user->update([
                    'microsoft_access_token' => $data['access_token'],
                    'microsoft_refresh_token' => $data['refresh_token'] ?? $this->user->microsoft_refresh_token,
                    'microsoft_token_expires_at' => now()->addSeconds($data['expires_in']),
                ]);

                return true;
            }

            Log::error('Failed to refresh Microsoft token', [
                'user_id' => $this->user->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while refreshing Microsoft token', [
                'user_id' => $this->user->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Make an authenticated request to the Microsoft Graph API.
     */
    protected function request(string $method, string $endpoint, array $data = []): ?array
    {
        Log::info('Microsoft Graph API request', [
            'user_id' => $this->user->id,
            'endpoint' => $endpoint,
            'method' => $method,
            'data' => $data,
            'is_configured' => $this->isConfigured(),
            'has_access_token' => ! empty($this->user->microsoft_access_token),
            'has_refresh_token' => ! empty($this->user->microsoft_refresh_token),
            'token_expires_at' => $this->user->microsoft_token_expires_at,
        ]);

        if (! $this->isConfigured()) {
            Log::warning('Microsoft Graph API not configured for user', [
                'user_id' => $this->user->id,
            ]);

            return null;
        }

        // Check if token needs refresh
        if (
            $this->user->microsoft_token_expires_at &&
            Carbon::parse($this->user->microsoft_token_expires_at)->subMinutes(5)->isPast()
        ) {
            Log::info('Microsoft Graph API token needs refresh', [
                'user_id' => $this->user->id,
                'token_expires_at' => $this->user->microsoft_token_expires_at,
            ]);

            if (! $this->refreshToken()) {
                Log::error('Failed to refresh Microsoft Graph API token', [
                    'user_id' => $this->user->id,
                ]);

                return null;
            }

            Log::info('Microsoft Graph API token refreshed successfully', [
                'user_id' => $this->user->id,
                'new_token_expires_at' => $this->user->microsoft_token_expires_at,
            ]);
        }

        try {
            $baseUrl = 'https://graph.microsoft.com/v1.0';

            // Set timeout to prevent hanging requests
            $httpClient = Http::timeout(10)
                ->withToken($this->user->microsoft_access_token)
                ->withHeaders(['Accept' => 'application/json']);

            // Handle GET requests with query parameters differently
            if ($method === 'get' && ! empty($data)) {
                $queryString = http_build_query($data);
                $fullUrl = $baseUrl.$endpoint.'?'.$queryString;

                Log::info('Making Microsoft Graph API GET request with query params', [
                    'user_id' => $this->user->id,
                    'url' => $fullUrl,
                    'query_params' => $data,
                ]);

                $response = $httpClient->get($fullUrl);
            } else {
                $fullUrl = $baseUrl.$endpoint;

                Log::info('Making Microsoft Graph API request', [
                    'user_id' => $this->user->id,
                    'url' => $fullUrl,
                    'method' => $method,
                    'data' => $data,
                ]);

                $response = $httpClient->{$method}($fullUrl, $data);
            }

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Microsoft Graph API request successful', [
                    'user_id' => $this->user->id,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'has_value' => isset($responseData['value']),
                    'value_count' => isset($responseData['value']) ? count($responseData['value']) : 0,
                ]);

                return $responseData;
            }

            // Handle specific error cases
            if ($response->status() === 401) {
                // Token might be invalid despite not being expired
                Log::warning('Microsoft Graph API unauthorized response - attempting token refresh', [
                    'user_id' => $this->user->id,
                    'status' => $response->status(),
                ]);

                // Force token refresh and try again once
                if ($this->refreshToken()) {
                    return $this->request($method, $endpoint, $data);
                }
            }

            $errorResponse = $response->json();
            Log::error('Failed Microsoft Graph API request', [
                'user_id' => $this->user->id,
                'endpoint' => $endpoint,
                'method' => $method,
                'data' => $data,
                'status' => $response->status(),
                'response' => $errorResponse,
                'error' => $errorResponse['error'] ?? null,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception during Microsoft Graph API request', [
                'user_id' => $this->user->id,
                'endpoint' => $endpoint,
                'method' => $method,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get the user's calendars, including shared calendars.
     */
    public function getCalendars(): ?array
    {
        // Get the user's own calendars
        $ownCalendars = $this->request('get', '/me/calendars');

        if (! $ownCalendars) {
            return null;
        }

        // Get shared calendars
        // Microsoft Graph API doesn't have a direct endpoint for shared calendars
        // We need to get all calendar groups and then get calendars from each group
        $calendarGroups = $this->request('get', '/me/calendarGroups');

        // Prepare the result
        $calendars = [
            'value' => [],
        ];

        // Add own calendars
        if (isset($ownCalendars['value'])) {
            foreach ($ownCalendars['value'] as $calendar) {
                $calendars['value'][] = $calendar;
            }
        }

        // Add calendars from each calendar group
        if ($calendarGroups && isset($calendarGroups['value'])) {
            foreach ($calendarGroups['value'] as $group) {
                $groupCalendars = $this->request('get', '/me/calendarGroups/'.$group['id'].'/calendars');

                if ($groupCalendars && isset($groupCalendars['value'])) {
                    foreach ($groupCalendars['value'] as $calendar) {
                        // Check if this calendar is already in our list (avoid duplicates)
                        $isDuplicate = false;
                        foreach ($calendars['value'] as $existingCalendar) {
                            if ($existingCalendar['id'] === $calendar['id']) {
                                $isDuplicate = true;
                                break;
                            }
                        }

                        if (! $isDuplicate) {
                            // Add a label to indicate it's from a group
                            $calendar['name'] = $calendar['name'].' ('.$group['name'].')';
                            $calendars['value'][] = $calendar;
                        }
                    }
                }
            }
        }

        return $calendars;
    }

    /**
     * Get events from the user's calendar.
     */
    public function getEvents(string $calendarId = 'primary', ?Carbon $startDateTime = null, ?Carbon $endDateTime = null): ?array
    {
        $startDateTime = $startDateTime ?? now();
        $endDateTime = $endDateTime ?? now()->addDays(7);

        // Convert to UTC and format dates in the format expected by Microsoft Graph API
        // Microsoft Graph API expects ISO 8601 format in UTC
        $startDateTimeFormatted = $startDateTime->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
        $endDateTimeFormatted = $endDateTime->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');

        Log::info('Getting calendar events', [
            'user_id' => $this->user->id,
            'calendar_id' => $calendarId,
            'start_date' => $startDateTimeFormatted,
            'end_date' => $endDateTimeFormatted,
        ]);

        $params = [
            'startDateTime' => $startDateTimeFormatted,
            'endDateTime' => $endDateTimeFormatted,
            '$orderby' => 'start/dateTime',
            '$top' => 50,
            '$select' => 'id,subject,start,end,location,isAllDay,organizer,bodyPreview',
        ];

        $endpoint = $calendarId === 'primary'
          ? '/me/calendarView'
          : "/me/calendars/{$calendarId}/calendarView";

        try {
            // Pass the parameters separately to the request method
            $response = $this->request('get', $endpoint, $params);

            // Log the response for debugging
            Log::info('Microsoft calendar events response', [
                'user_id' => $this->user->id,
                'success' => $response !== null,
                'has_value' => $response !== null && isset($response['value']),
                'event_count' => $response !== null && isset($response['value']) ? count($response['value']) : 0,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Exception in getEvents', [
                'user_id' => $this->user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Create a new event in the user's calendar.
     *
     * Note: This method is unavailable with current permissions (read-only).
     * The app only has Calendars.Read permission, not Calendars.ReadWrite.
     */
    public function createEvent(array $eventData, string $calendarId = 'primary'): ?array
    {
        Log::warning('Attempted to create calendar event but app only has read permissions');

        return null;
    }

    /**
     * Update an existing event in the user's calendar.
     *
     * Note: This method is unavailable with current permissions (read-only).
     * The app only has Calendars.Read permission, not Calendars.ReadWrite.
     */
    public function updateEvent(string $calendarId, string $eventId, array $eventData): ?array
    {
        Log::warning('Attempted to update calendar event but app only has read permissions');

        return null;
    }

    /**
     * Delete an event from the user's calendar.
     *
     * Note: This method is unavailable with current permissions (read-only).
     * The app only has Calendars.Read permission, not Calendars.ReadWrite.
     */
    public function deleteEvent(string $calendarId, string $eventId): bool
    {
        Log::warning('Attempted to delete calendar event but app only has read permissions');

        return false;
    }
}
