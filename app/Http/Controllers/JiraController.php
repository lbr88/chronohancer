<?php

namespace App\Http\Controllers;

use App\Services\JiraService;
use Illuminate\Http\JsonResponse;

class JiraController extends Controller
{
    protected $jiraService;

    public function __construct(JiraService $jiraService)
    {
        $this->jiraService = $jiraService;
    }

    public function getIssue(string $key): JsonResponse
    {
        try {
            // Use the cached getIssue method from JiraService
            $issue = $this->jiraService
                ->setUser(auth()->user())
                ->getIssue($key);

            if (! $issue) {
                return response()->json(['error' => 'Issue not found'], 404);
            }

            // Return cached issue data
            return response()->json($issue);
        } catch (\Exception $e) {
            logger()->error('Failed to fetch Jira issue for tooltip', [
                'error' => $e->getMessage(),
                'key' => $key,
                'user_id' => auth()->id(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
