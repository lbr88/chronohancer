<?php

namespace App\Livewire;

use App\Models\TimeLog;
use App\Services\TempoService;
use Carbon\Carbon;
use Livewire\Component;

class TempoSync extends Component
{
    public $dateFrom;

    public $dateTo;

    public $syncAll = false;

    public $syncInProgress = false;

    public $syncResults = null;

    public $showSyncModal = false;

    public $syncStatus = 'idle'; // idle, syncing, completed, failed

    public $syncProgress = 0;

    public $totalLogs = 0;

    public $processedLogs = 0;

    public $syncErrors = [];

    public function mount()
    {
        // Default to last 7 days
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function openSyncModal()
    {
        $this->resetSyncState();
        $this->showSyncModal = true;
    }

    public function closeSyncModal()
    {
        $this->showSyncModal = false;
    }

    public function resetSyncState()
    {
        $this->syncStatus = 'idle';
        $this->syncProgress = 0;
        $this->totalLogs = 0;
        $this->processedLogs = 0;
        $this->syncErrors = [];
        $this->syncResults = null;
    }

    public function updatedSyncAll()
    {
        if ($this->syncAll) {
            $this->dateFrom = null;
            $this->dateTo = null;
        } else {
            // Reset to last 7 days
            $this->dateFrom = now()->subDays(7)->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    public function startSync(TempoService $tempoService)
    {
        if (! $tempoService->isConfigured()) {
            session()->flash('error', 'Tempo integration is not configured. Please check your settings.');

            return;
        }

        if ($tempoService->isReadOnly()) {
            session()->flash('error', 'Tempo integration is in read-only mode. Syncing is disabled.');

            return;
        }

        $this->resetSyncState();
        $this->syncStatus = 'syncing';
        $this->syncInProgress = true;

        // Build the query based on options
        $query = TimeLog::query()
            ->where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time'); // Only sync completed time logs

        // Apply date filters if not syncing all
        if (! $this->syncAll) {
            $fromDate = Carbon::parse($this->dateFrom);
            $toDate = Carbon::parse($this->dateTo);

            $query->whereBetween('start_time', [
                $fromDate->startOfDay(),
                $toDate->endOfDay(),
            ]);
        }

        // Only sync logs that haven't been synced yet
        $query->notSyncedToTempo();

        // Get the time logs to sync
        $timeLogs = $query->get();
        $this->totalLogs = $timeLogs->count();

        if ($this->totalLogs === 0) {
            $this->syncStatus = 'completed';
            $this->syncInProgress = false;
            $this->syncResults = [
                'success' => 0,
                'failed' => 0,
                'message' => 'No time logs found to sync.',
            ];

            return;
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Process each time log
        foreach ($timeLogs as $index => $timeLog) {
            $result = $tempoService->createWorklog($timeLog);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'time_log_id' => $timeLog->id,
                    'description' => $timeLog->description ?? 'No description',
                    'date' => $timeLog->start_time->format('Y-m-d'),
                    'message' => $result['message'],
                ];
                $this->syncErrors[] = [
                    'time_log_id' => $timeLog->id,
                    'description' => $timeLog->description ?? 'No description',
                    'date' => $timeLog->start_time->format('Y-m-d'),
                    'message' => $result['message'],
                ];
            }

            $this->processedLogs++;
            $this->syncProgress = ($this->processedLogs / $this->totalLogs) * 100;
        }

        $this->syncStatus = 'completed';
        $this->syncInProgress = false;
        $this->syncResults = [
            'success' => $results['success'],
            'failed' => $results['failed'],
            'message' => "Sync completed: {$results['success']} successful, {$results['failed']} failed.",
        ];

        // Refresh the page to show updated sync status
        $this->dispatch('refresh');
    }

    public function render()
    {
        $tempoService = app(TempoService::class);
        $isReadOnly = $tempoService->isReadOnly();

        // Count how many logs would be synced with current filters
        $query = TimeLog::query()
            ->where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time')
            ->notSyncedToTempo();

        // Apply date filters if not syncing all
        if (! $this->syncAll) {
            $fromDate = Carbon::parse($this->dateFrom);
            $toDate = Carbon::parse($this->dateTo);

            $query->whereBetween('start_time', [
                $fromDate->startOfDay(),
                $toDate->endOfDay(),
            ]);
        }

        $logsToSync = $query->count();

        // Count how many logs have already been synced
        $syncedLogsQuery = TimeLog::query()
            ->where('user_id', auth()->id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('tempo_worklog_id');

        // Apply date filters if not syncing all
        if (! $this->syncAll) {
            $fromDate = Carbon::parse($this->dateFrom);
            $toDate = Carbon::parse($this->dateTo);

            $syncedLogsQuery->whereBetween('start_time', [
                $fromDate->startOfDay(),
                $toDate->endOfDay(),
            ]);
        }

        $syncedLogs = $syncedLogsQuery->count();

        return view('livewire.tempo-sync', [
            'logsToSync' => $logsToSync,
            'syncedLogs' => $syncedLogs,
            'tempoConfigured' => $tempoService->isConfigured(),
            'isReadOnly' => $isReadOnly,
        ]);
    }
}
