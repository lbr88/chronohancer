<?php

namespace App\Http\Controllers;

use App\Services\TempoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TempoAuthController extends Controller
{
    protected TempoService $tempoService;

    public function __construct(TempoService $tempoService)
    {
        $this->tempoService = $tempoService;
    }

    /**
     * Redirect the user to Tempo's OAuth authorization page.
     */
    public function redirect()
    {
        $user = auth()->user();

        if (! $user->hasJiraEnabled()) {
            return redirect()->route('settings.integrations.jira')
                ->with('error', 'You must connect your Jira account before enabling Tempo integration.');
        }

        if (! $this->tempoService->isConfigured()) {
            return redirect()->route('settings.integrations.tempo')
                ->with('error', 'Tempo integration is not configured. Please contact your administrator.');
        }

        try {
            $authUrl = $this->tempoService->getAuthorizationUrl($user);
            \Log::info('Redirecting to Tempo auth URL', [
                'url' => $authUrl,
                'user_id' => $user->id,
                'jira_site_url' => $user->jira_site_url,
            ]);

            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            \Log::error('Failed to start Tempo authorization', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'jira_site_url' => $user->jira_site_url,
            ]);

            return redirect()->route('settings.integrations.tempo')
                ->with('error', 'Failed to start Tempo authorization: '.$e->getMessage());
        }
    }

    /**
     * Handle the OAuth callback from Tempo.
     */
    public function callback(Request $request)
    {
        if (! $this->tempoService->isConfigured()) {
            return redirect()->route('settings.integrations.tempo')->with('error', 'Tempo integration is not configured.');
        }

        if ($request->has('error')) {
            return redirect()->route('settings.integrations.tempo')->with('error', 'Failed to connect to Tempo: '.$request->get('error_description', 'Unknown error'));
        }

        $code = $request->get('code');
        if (! $code) {
            return redirect()->route('settings.integrations.tempo')->with('error', 'No authorization code received from Tempo.');
        }

        \Log::info('Processing Tempo OAuth callback', [
            'code_length' => strlen($code),
            'user_id' => Auth::id(),
        ]);

        $result = $this->tempoService->handleCallback($code);
        if (! $result['success']) {
            \Log::error('Failed to handle Tempo callback', [
                'error' => $result['message'] ?? 'Unknown error',
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('settings.integrations.tempo')->with('error', 'Failed to get access token: '.($result['message'] ?? 'Unknown error'));
        }

        \Log::info('Tempo callback successful', [
            'has_data' => isset($result['data']),
            'data_keys' => isset($result['data']) ? array_keys($result['data']) : [],
            'user_id' => Auth::id(),
        ]);

        // Store the tokens for the user
        $user = Auth::user();
        $this->tempoService->storeUserTokens($user, $result['data']);

        // Refresh the user instance to get the latest token data
        $user->refresh();

        \Log::info('Tempo tokens stored', [
            'has_access_token' => ! empty($user->tempo_access_token),
            'has_refresh_token' => ! empty($user->tempo_refresh_token),
            'has_expires_at' => ! empty($user->tempo_token_expires_at),
            'tempo_enabled' => $user->tempo_enabled,
            'user_id' => $user->id,
        ]);

        return redirect()->route('settings.integrations.tempo')->with('success', 'Successfully connected to Tempo.');

    }

    /**
     * Disconnect Tempo from the user's account.
     */
    public function disconnect()
    {
        $user = Auth::user();
        $user->update([
            'tempo_access_token' => null,
            'tempo_refresh_token' => null,
            'tempo_token_expires_at' => null,
            'tempo_enabled' => false,
        ]);

        return redirect()->route('settings.integrations.tempo')->with('success', 'Successfully disconnected from Tempo.');
    }
}
