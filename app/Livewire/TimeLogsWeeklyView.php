<?php

namespace App\Livewire;

use App\Livewire\Traits\TimeLogsUtilities;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Timer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeLogsWeeklyView extends Component
{
    use TimeLogsUtilities;

    // Week properties (received from TimeLogsBase)
    public $startOfWeek;

    public $endOfWeek;

    public $currentWeek;

    protected $listeners = [
        'timeLogSaved' => '$refresh',
        'timeLogDeleted' => '$refresh',
    ];

    public function mount($startOfWeek = null, $endOfWeek = null, $currentWeek = null)
    {
        $this->startOfWeek = $startOfWeek ?? now()->startOfWeek()->format('Y-m-d');
        $this->endOfWeek = $endOfWeek ?? now()->endOfWeek()->format('Y-m-d');
        $this->currentWeek = $currentWeek ?? now();
    }

    /**
     * Find and edit a time log for a specific date, project, and timer
     */
    public function findAndEditTimeLog($date, $projectId = null, $timerId = null, $description = null)
    {
        $this->dispatch('find-and-edit-time-log', [
            'date' => $date,
            'projectId' => $projectId,
            'timerId' => $timerId,
            'description' => $description,
        ]);
    }

    /**
     * Open the quick time modal for a specific date, project, and timer
     */
    public function openQuickTimeModal($date = null, $projectId = null, $timerId = null, $description = null)
    {
        $this->dispatch('open-quick-time-modal', [
            'date' => $date,
            'projectId' => $projectId,
            'timerId' => $timerId,
            'description' => $description,
        ]);
    }

    /**
     * Get the weekly data property
     */
    public function getWeeklyDataProperty()
    {
        // Get time logs for the selected week
        $timeLogs = TimeLog::where('user_id', Auth::id())
            ->where('workspace_id', app('current.workspace')->id)
            ->whereNotNull('end_time') // Only show logs that have an end time (completed logs)
            ->whereBetween('start_time', [
                $this->startOfWeek.' 00:00:00',
                $this->endOfWeek.' 23:59:59',
            ])
            ->with(['timer.project', 'timer', 'tags'])
            ->get();

        // Group time logs by project and timer
        $weekData = [];
        $totalDuration = 0;

        // Get all days in the week for display
        $startDate = Carbon::parse($this->startOfWeek);
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $weekDays[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'dayName' => $date->format('l'),
            ];
        }

        // Get all active projects (including those without time logs)
        $allProjects = Project::where('user_id', Auth::id())->get();

        // Make sure the default project is included
        $defaultProject = Project::findOrCreateDefault(Auth::id(), app('current.workspace')->id);
        $projectsWithDefaultProject = $allProjects->toArray();

        // Check if the default project is already in the list
        $defaultProjectExists = false;
        foreach ($projectsWithDefaultProject as $project) {
            if ($project['id'] === $defaultProject->id) {
                $defaultProjectExists = true;
                break;
            }
        }

        // Add the default project if it's not already in the list
        if (! $defaultProjectExists) {
            $projectsWithDefaultProject[] = [
                'id' => $defaultProject->id,
                'name' => $defaultProject->name,
                'description' => $defaultProject->description,
            ];
        }

        // Get all active timers
        $allTimers = Timer::where('user_id', Auth::id())->get();

        // Create a map of project IDs to their timers
        $projectTimersMap = [];
        foreach ($allTimers as $timer) {
            $projectId = $timer->project_id;
            if (! isset($projectTimersMap[$projectId])) {
                $projectTimersMap[$projectId] = [];
            }
            $projectTimersMap[$projectId][] = $timer;
        }

        // Group time logs by project (through timer)
        $logsByProject = $timeLogs->groupBy(function ($timeLog) {
            return $timeLog->timer ? $timeLog->timer->project_id : null;
        });

        // Arrays to store projects with and without logs
        $projectsWithLogs = [];
        $projectsWithoutLogs = [];

        // Process each project (including those without logs)
        foreach ($projectsWithDefaultProject as $project) {
            $projectId = $project['id'] ?? null;
            $projectName = $project['name'];
            $projectTotal = 0;
            $timersWithLogs = [];
            $timersWithoutLogs = [];

            // Get logs for this project (if any)
            $projectLogs = $logsByProject[$projectId] ?? collect();
            $hasLogs = $projectLogs->isNotEmpty();

            // First group logs by timer only
            $timerGroups = $projectLogs->groupBy(function ($log) {
                return $log->timer_id ?? 'manual';
            });

            // Add timers with logs
            foreach ($timerGroups as $timerId => $timerLogs) {
                $timerId = $timerId === 'manual' ? null : $timerId;
                $timerName = $timerId ? ($timerLogs->first()->timer->name ?? 'Unnamed Timer') : 'Manual Entry';

                $timerTotal = 0;
                $dailyDurations = array_fill_keys(array_keys($weekDays), 0);
                $dailyLogIds = array_fill_keys(array_keys($weekDays), null);

                // Group timer logs by description
                $descriptionsCollection = collect();

                // Track descriptions by day to build a more accurate view
                $descriptionsPerDay = [];

                // Calculate daily durations grouped by timer
                foreach ($timerLogs as $log) {
                    $logDate = Carbon::parse($log->start_time)->format('Y-m-d');
                    $dailyDurations[$logDate] = ($dailyDurations[$logDate] ?? 0) + $log->duration_minutes;

                    // Store the log ID for this day (for editing)
                    $dailyLogIds[$logDate] = $log->id;

                    $timerTotal += $log->duration_minutes;

                    // Get the description for this log
                    $description = trim($log->description ?? '');

                    // Only add non-empty descriptions
                    if (! empty($description)) {
                        // Track descriptions by day
                        if (! isset($descriptionsPerDay[$logDate])) {
                            $descriptionsPerDay[$logDate] = [];
                        }
                        $descriptionsPerDay[$logDate][] = $description;

                        // Add to unique descriptions collection with date context
                        $descriptionsCollection->push([
                            'description' => $description,
                            'date' => $logDate,
                            'duration' => $log->duration_minutes,
                        ]);
                    }
                }

                // Get unique descriptions for this timer - group by description
                $uniqueDescriptions = $descriptionsCollection
                    ->groupBy('description')
                    ->map(function ($group, $description) {
                        return [
                            'description' => $description,
                            'count' => $group->count(),
                            'total_duration' => $group->sum('duration'),
                        ];
                    })
                    ->values();

                // Prepare daily descriptions map (for hover tooltips)
                $dailyDescriptions = array_fill_keys(array_keys($weekDays), '');
                foreach ($descriptionsPerDay as $date => $descriptions) {
                    $dailyDescriptions[$date] = implode(', ', array_unique($descriptions));
                }

                $timersWithLogs[] = [
                    'id' => $timerId,
                    'name' => $timerName,
                    'originalName' => $timerName,
                    'descriptions' => $uniqueDescriptions,
                    'daily' => $dailyDurations,
                    'dailyDescriptions' => $dailyDescriptions,
                    'dailyLogIds' => $dailyLogIds,
                    'total' => $timerTotal,
                    'tags' => $timerLogs->flatMap->tags->unique('id')->values(),
                ];

                $projectTotal += $timerTotal;
            }

            // Add timers without logs for this project
            if (isset($projectTimersMap[$projectId])) {
                foreach ($projectTimersMap[$projectId] as $timer) {
                    // Skip timers that already have logs (already added above)
                    $timerAlreadyAdded = false;
                    foreach ($timersWithLogs as $existingTimer) {
                        if ($existingTimer['id'] === $timer->id) {
                            $timerAlreadyAdded = true;
                            break;
                        }
                    }

                    if (! $timerAlreadyAdded) {
                        $dailyDurations = array_fill_keys(array_keys($weekDays), 0);
                        $dailyDescriptions = array_fill_keys(array_keys($weekDays), '');
                        $dailyLogIds = array_fill_keys(array_keys($weekDays), null);

                        $timersWithoutLogs[] = [
                            'id' => $timer->id,
                            'name' => $timer->name,
                            'originalName' => $timer->name,
                            'descriptions' => [],  // Empty descriptions array to match new data structure
                            'daily' => $dailyDurations,
                            'dailyDescriptions' => $dailyDescriptions,
                            'dailyLogIds' => $dailyLogIds,
                            'total' => 0,
                            'tags' => $timer->tags,
                        ];
                    }
                }
            }

            // Add "Manual Entry" option if it doesn't exist
            $hasManualEntry = false;
            foreach (array_merge($timersWithLogs, $timersWithoutLogs) as $timer) {
                if ($timer['id'] === null) {
                    $hasManualEntry = true;
                    break;
                }
            }

            if (! $hasManualEntry) {
                $dailyDurations = array_fill_keys(array_keys($weekDays), 0);
                $dailyDescriptions = array_fill_keys(array_keys($weekDays), '');
                $dailyLogIds = array_fill_keys(array_keys($weekDays), null);

                $timersWithoutLogs[] = [
                    'id' => null,
                    'name' => 'Manual Entry',
                    'originalName' => 'Manual Entry',
                    'description' => '',
                    'daily' => $dailyDurations,
                    'dailyDescriptions' => $dailyDescriptions,
                    'dailyLogIds' => $dailyLogIds,
                    'total' => 0,
                    'tags' => collect(),
                ];
            }

            // Combine timers with logs first, followed by timers without logs
            $timers = array_merge($timersWithLogs, $timersWithoutLogs);

            // Only add the project if it has timers or if it's a project with logs
            if (count($timers) > 0) {
                $projectData = [
                    'id' => $projectId,
                    'name' => $projectName,
                    'timers' => $timers,
                    'total' => $projectTotal,
                    'hasLogs' => $hasLogs,
                ];

                // Add to appropriate array based on whether it has logs
                if ($hasLogs) {
                    $projectsWithLogs[] = $projectData;
                } else {
                    $projectsWithoutLogs[] = $projectData;
                }

                $totalDuration += $projectTotal;
            }
        }

        // Combine projects with logs first, followed by projects without logs
        $weekData = array_merge($projectsWithLogs, $projectsWithoutLogs);

        return [
            'weekDays' => $weekDays,
            'projects' => $weekData,
            'total' => $totalDuration,
        ];
    }

    public function render()
    {
        return view('livewire.time-logs-weekly-view', [
            'weeklyData' => $this->weeklyData,
        ]);
    }
}
