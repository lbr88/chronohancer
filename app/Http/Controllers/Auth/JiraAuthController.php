<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\JiraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JiraAuthController extends Controller
{
    /**
     * Handle the callback from Jira OAuth.
     */
    public function callback(Request $request, JiraService $jiraService)
    {
        if ($request->has('error')) {
            return redirect()->route('settings.integrations.jira')->with('error', 'Failed to connect to Jira: '.$request->get('error_description'));
        }

        try {
            // Exchange the authorization code for access token
            $tokenData = $jiraService->handleCallback($request->get('code'));

            // Update user with Jira credentials
            Auth::user()->update([
                'jira_access_token' => $tokenData['access_token'],
                'jira_refresh_token' => $tokenData['refresh_token'],
                'jira_token_expires_at' => now()->addSeconds($tokenData['expires_in']),
                'jira_cloud_id' => $tokenData['cloud_id'],
                'jira_site_url' => $tokenData['site_url'],
                'jira_enabled' => true,
            ]);

            return redirect()->route('settings.integrations.jira')->with('success', 'Successfully connected to Jira');
        } catch (\Exception $e) {
            return redirect()->route('settings.integrations.jira')->with('error', 'Failed to connect to Jira: '.$e->getMessage());
        }
    }
}
