<?php

namespace App\Livewire\Settings;

use App\Services\JiraService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JiraIntegration extends Component
{
    public $isConnected = false;

    public $jiraEnabled = false;

    public $jiraSiteUrl = '';

    public function mount()
    {
        $user = Auth::user();
        $this->isConnected = $user->hasJiraEnabled();
        $this->jiraEnabled = $user->jira_enabled;
        $this->jiraSiteUrl = $user->jira_site_url;
    }

    public function updatedJiraEnabled($value)
    {
        Auth::user()->update(['jira_enabled' => $value]);
        $this->dispatch('jira-status-updated');
    }

    public function connect()
    {
        $jiraService = app(JiraService::class);

        return redirect($jiraService->getAuthorizationUrl());
    }

    public function disconnect()
    {
        Auth::user()->disconnectJira();

        $this->isConnected = false;
        $this->jiraEnabled = false;
        $this->jiraSiteUrl = '';

        $this->dispatch('jira-disconnected');
    }

    public function render()
    {
        return view('livewire.settings.jira-integration');
    }
}
