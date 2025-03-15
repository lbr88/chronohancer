<?php

namespace App\Console\Commands;

use App\Models\TimeLog;
use App\Services\TempoService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncTempoWorklogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tempo:sync 
                            {--user= : User ID to sync time logs for}
                            {--workspace= : Workspace ID to sync time logs for}
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--all : Sync all time logs (ignores date filters)}
                            {--force : Force sync even if already synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync time logs to Tempo as worklogs';

    /**
     * Execute the console command.
     */
    public function handle(TempoService $tempoService)
    {
        if (! $tempoService->isConfigured()) {
            $this->error('Tempo integration is not configured. Please check your .env file.');

            return 1;
        }

        $this->info('Starting Tempo worklog sync...');

        // Build the query based on options
        $query = TimeLog::query()
            ->whereNotNull('end_time') // Only sync completed time logs
            ->notSyncedToTempo(); // Only sync logs that haven't been synced yet

        // Apply user filter if provided
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
            $this->info("Filtering by user ID: {$userId}");
        }

        // Apply workspace filter if provided
        if ($workspaceId = $this->option('workspace')) {
            $query->where('workspace_id', $workspaceId);
            $this->info("Filtering by workspace ID: {$workspaceId}");
        }

        // Apply date filters if not syncing all
        if (! $this->option('all')) {
            $fromDate = $this->option('from') ? Carbon::parse($this->option('from')) : Carbon::now()->subDays(7);
            $toDate = $this->option('to') ? Carbon::parse($this->option('to')) : Carbon::now();

            $query->whereBetween('start_time', [
                $fromDate->startOfDay(),
                $toDate->endOfDay(),
            ]);

            $this->info("Filtering by date range: {$fromDate->format('Y-m-d')} to {$toDate->format('Y-m-d')}");
        }

        // If force option is used, include already synced logs
        if ($this->option('force')) {
            $query = TimeLog::query()->whereNotNull('end_time');
            $this->info('Force sync enabled - including already synced logs');
        }

        // Get the time logs to sync
        $timeLogs = $query->get();
        $count = $timeLogs->count();

        if ($count === 0) {
            $this->info('No time logs found to sync.');

            return 0;
        }

        $this->info("Found {$count} time logs to sync.");

        // Create a progress bar
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Process each time log
        foreach ($timeLogs as $timeLog) {
            $result = $tempoService->createWorklog($timeLog);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'time_log_id' => $timeLog->id,
                    'message' => $result['message'],
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->info('Sync completed:');
        $this->info("- Successfully synced: {$results['success']}");
        $this->info("- Failed to sync: {$results['failed']}");

        if ($results['failed'] > 0) {
            $this->newLine();
            $this->warn('Errors:');
            foreach ($results['errors'] as $error) {
                $this->warn("  - Time Log #{$error['time_log_id']}: {$error['message']}");
            }
        }

        return 0;
    }
}
