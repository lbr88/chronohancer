<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class JiraSetupGuide extends Command
{
    protected $signature = 'jira:setup-guide';

    protected $description = 'Display instructions for setting up Jira OAuth integration';

    public function handle()
    {
        $this->info('Jira OAuth Setup Guide');
        $this->line('===================');
        $this->newLine();

        $this->line('Follow these steps to set up your Jira OAuth integration:');
        $this->newLine();

        $this->line('1. Go to https://developer.atlassian.com/console/myapps/');
        $this->line('2. Click "Create" to create a new OAuth app');
        $this->line('3. Fill in the app details:');
        $this->line('   - Name: Chronohancer');
        $this->line('   - Description: Time tracking integration');
        $this->line('4. Under "Authorization" configure:');
        $this->line('   - OAuth 2.0 (3LO)');
        $this->line('   - Add callback URL: '.config('app.url').'/auth/jira/callback');
        $this->line('5. Under "Permissions" configure:');
        $this->line('   - Add "Jira platform REST API"');
        $this->line('   - Enable the following scopes:');
        $this->line('     * read:jira-work');
        $this->line('     * write:jira-work');
        $this->line('     * read:jira-user');
        $this->line('     * offline_access');
        $this->newLine();

        $this->line('After creating the app, you will receive:');
        $this->line('- Client ID');
        $this->line('- Client Secret');
        $this->newLine();

        $this->line('Add these to your .env file:');
        $this->line('JIRA_CLIENT_ID=your_client_id');
        $this->line('JIRA_CLIENT_SECRET=your_client_secret');
        $this->line('JIRA_REDIRECT_URI='.config('app.url').'/auth/jira/callback');
        $this->newLine();

        $this->info('Once configured, users can connect their Jira accounts in Settings > Integrations > Jira');
    }
}
