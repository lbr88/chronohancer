<?php

namespace App\Livewire\Settings;

use App\Services\TempoService;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class TempoIntegration extends Component
{
    public $enabled = false;

    public $readOnly = false;

    public $testStatus = null;

    public $testMessage = null;

    protected $rules = [
        'enabled' => 'boolean',
        'readOnly' => 'boolean',
    ];

    public function mount()
    {
        $user = auth()->user();
        $this->enabled = $user->hasTempoEnabled();
        $this->readOnly = config('tempo.read_only', false);
    }

    public function updatedEnabled($value)
    {
        $user = auth()->user();
        if ($value && ! $user->hasJiraEnabled()) {
            $this->enabled = false;
            session()->flash('message', 'You must connect your Jira account before enabling Tempo integration.');

            return;
        }

        if ($value && ! $user->tempo_access_token) {
            $this->enabled = false;
            session()->flash('message', 'You must connect your Tempo account first.');

            return;
        }
    }

    public function save()
    {
        $this->validate();

        // Update read-only mode in config
        Config::set('tempo.read_only', $this->readOnly);

        // Update user's tempo_enabled status
        auth()->user()->update([
            'tempo_enabled' => $this->enabled,
        ]);

        session()->flash('message', 'Tempo integration settings saved successfully.');
    }

    public function connect()
    {
        $this->redirect(route('auth.tempo.redirect'));
    }

    public function disconnect()
    {
        $user = auth()->user();
        $user->update([
            'tempo_access_token' => null,
            'tempo_refresh_token' => null,
            'tempo_token_expires_at' => null,
            'tempo_enabled' => false,
        ]);

        $this->enabled = false;
        session()->flash('message', 'Successfully disconnected from Tempo.');
    }

    public function testConnection(TempoService $tempoService)
    {
        $user = auth()->user();

        if (! $user->hasJiraEnabled()) {
            $this->testStatus = 'error';
            $this->testMessage = 'You must connect your Jira account first.';

            return;
        }

        if (! $user->jira_site_url) {
            $this->testStatus = 'error';
            $this->testMessage = 'Your Jira site URL is not configured. Please reconnect your Jira account.';

            return;
        }

        if (! $tempoService->isConfigured()) {
            $this->testStatus = 'error';
            $this->testMessage = 'Tempo integration is not configured. Please contact your administrator.';

            return;
        }

        if (! $user->hasTempoEnabled()) {
            $this->testStatus = 'error';
            $this->testMessage = 'You need to connect your Tempo account first.';

            return;
        }

        try {
            if ($tempoService->testConnection($user)) {
                $this->testStatus = 'success';
                $this->testMessage = 'Successfully connected to Tempo.';
            } else {
                $this->testStatus = 'error';
                $this->testMessage = 'Failed to connect to Tempo. Please check your connection.';
            }
        } catch (\Exception $e) {
            $this->testStatus = 'error';
            $this->testMessage = 'Error: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.settings.tempo-integration');
    }
}
