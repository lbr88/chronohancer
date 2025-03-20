<div class="timer-page">
    <div class="p-6 lg:p-8 max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Time Tracker</h1>
                <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Manage your time efficiently with intuitive timers</p>
            </div>
            <button
                type="button"
                wire:click="openNewTimerModal"
                class="mt-4 sm:mt-0 flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Start Timer
            </button>
        </div>

        <div class="grid grid-cols-1 gap-8">
            <!-- Running Timers Panel -->
            <div>
                <!-- Running Timers -->
                <div class="ch-card">
                    <div class="ch-card-header">
                        <h2 class="ch-card-title">Running Timers</h2>
                        <div class="flex items-center gap-4">
                            <!-- Auto-Pause Toggle -->
                            <div class="inline-flex flex-col">
                                <div class="inline-flex items-center">
                                    <button
                                        wire:click="toggleAutoPauseTimers"
                                        type="button"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $autoPauseTimers ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                                        role="switch"
                                        aria-checked="{{ $autoPauseTimers ? 'true' : 'false' }}">
                                        <span class="sr-only">Auto-pause other timers</span>
                                        <span
                                            aria-hidden="true"
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoPauseTimers ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Auto-pause timers</span>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Only one timer active at a time</span>
                            </div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $runningTimers->count() }} active</span>
                        </div>
                    </div>

                    <!-- Running Timers -->
                    @if($runningTimers->isNotEmpty())
                    <div class="ch-card-body">
                        @foreach($runningTimers as $timer)
                        <div class="py-3 px-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <div class="flex items-center justify-between">
                                <!-- Left side: Timer info -->
                                <div class="flex items-center flex-grow">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-3">
                                        <div class="h-2 w-2 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse"></div>
                                    </div>

                                    <div class="flex-grow">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $timer->name }}</h3>

                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            @if($timer->project)
                                            <span>{{ $timer->project->name }}</span>
                                            @endif
                                        </div>

                                        @if($timer->tags->count() > 0)
                                        <div class="mt-1 ch-tag-list">
                                            @foreach($timer->tags as $tag)
                                            <span class="ch-tag"
                                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                {{ $tag->name }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @endif

                                        @php
                                        $timerDescription = null;
                                        // Get description from latest time log or timer
                                        if ($timer->latestTimeLog && $timer->latestTimeLog->description) {
                                        $timerDescription = $timer->latestTimeLog->description;
                                        }
                                        // Fall back to timer description field
                                        elseif ($timer->description) {
                                        $timerDescription = $timer->description;
                                        }
                                        @endphp
                                        @if($timerDescription)
                                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                            {{ $timerDescription }}
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Middle: Timer display -->
                                <div class="flex flex-col items-center justify-center mx-4 min-w-[120px]">
                                    <div class="text-xl font-mono font-bold text-indigo-600 dark:text-indigo-400 timer-display" id="timer-{{ $timer->id }}" data-start="{{ $this->getFormattedStartTimeForJs($timer) }}" data-time-format="{{ $timeFormat }}">
                                        {{ $this->getTimerDuration($timer) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Today: {{ $this->getTimerTotalDurationForToday($timer) }}
                                    </div>
                                    @php
                                    $latestCompletedLog = $this->getLatestCompletedTimeLog($timer);
                                    @endphp
                                    @if($latestCompletedLog)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Last: {{ $this->formatDuration($latestCompletedLog->duration_minutes * 60) }}
                                    </div>
                                    @endif
                                </div>

                                <!-- Right side: Action buttons -->
                                <div class="flex items-center space-x-1 ml-auto">
                                    <button
                                        wire:click="cancelTimer({{ $timer->id }})"
                                        class="ch-btn-icon-secondary"
                                        title="Cancel timer without saving">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span class="sr-only">Cancel</span>
                                    </button>

                                    <button
                                        wire:click="pauseTimer({{ $timer->id }})"
                                        class="ch-btn-icon-warning"
                                        title="Pause timer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="sr-only">Pause</span>
                                    </button>

                                    <button
                                        wire:click="stopAndEditTimer({{ $timer->id }})"
                                        class="ch-btn-icon-primary"
                                        title="Stop timer and edit details">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="sr-only">Stop & Edit</span>
                                    </button>

                                    <button
                                        wire:click="stopTimer({{ $timer->id }})"
                                        class="ch-btn-icon-danger stop-button p-0"
                                        title="Stop timer and save time">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <rect x="5" y="5" width="14" height="14" rx="2" />
                                        </svg>
                                        <span class="sr-only">Stop</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="ch-empty-state">
                        <div class="ch-empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="ch-empty-state-title">No Active Timers</h3>
                        <p class="ch-empty-state-description">
                            Start a new timer to track your time. You can create multiple timers for different tasks.
                        </p>
                    </div>
                    @endif

                    <!-- Paused Timers Section -->
                    @if($pausedTimers->isNotEmpty())
                    <div class="border-t border-gray-200 dark:border-gray-700">
                        <div class="ch-card-header">
                            <h2 class="ch-card-title">Paused Timers</h2>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $pausedTimers->count() }} paused</span>
                        </div>
                        <div class="ch-card-body">
                            @foreach($pausedTimers as $timer)
                            <div class="py-3 px-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="flex items-center justify-between">
                                    <!-- Left side: Timer info -->
                                    <div class="flex items-center flex-grow">
                                        <div class="h-8 w-8 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>

                                        <div class="flex-grow">
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $timer->name }} <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Paused</span></h3>

                                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                @if($timer->project)
                                                <span>{{ $timer->project->name }}</span>
                                                @endif
                                            </div>

                                            @if($timer->tags->count() > 0)
                                            <div class="mt-1 ch-tag-list">
                                                @foreach($timer->tags as $tag)
                                                <span class="ch-tag"
                                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                    {{ $tag->name }}
                                                </span>
                                                @endforeach
                                            </div>
                                            @endif

                                            @php
                                            $timerDescription = null;
                                            // Get description from latest time log or timer
                                            if ($timer->latestTimeLog && $timer->latestTimeLog->description) {
                                            $timerDescription = $timer->latestTimeLog->description;
                                            }
                                            // Fall back to timer description field
                                            elseif ($timer->description) {
                                            $timerDescription = $timer->description;
                                            }
                                            @endphp
                                            @if($timerDescription)
                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                                {{ $timerDescription }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Middle: Timer display -->
                                    <div class="flex flex-col items-center justify-center mx-4 min-w-[120px]">
                                        <div class="text-xl font-mono font-bold text-yellow-600 dark:text-yellow-400">
                                            {{ $this->getTimerTotalDurationForToday($timer) }}
                                        </div>
                                        @php
                                        $latestCompletedLog = $this->getLatestCompletedTimeLog($timer);
                                        @endphp
                                        @if($latestCompletedLog)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Last: {{ $this->formatDuration($latestCompletedLog->duration_minutes * 60) }}
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Right side: Action buttons -->
                                    <div class="flex items-center space-x-1 ml-auto">
                                        <button
                                            wire:click="restartTimer({{ $timer->id }})"
                                            class="ch-btn-icon-primary"
                                            title="Resume timer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="sr-only">Resume</span>
                                        </button>

                                        <button
                                            wire:click="editTimer({{ $timer->id }})"
                                            class="ch-btn-icon-secondary"
                                            title="Edit timer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span class="sr-only">Edit</span>
                                        </button>

                                        <button
                                            wire:click="stopPausedTimer({{ $timer->id }})"
                                            class="ch-btn-icon-danger stop-button p-0"
                                            title="Stop timer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <rect x="5" y="5" width="14" height="14" rx="2" />
                                            </svg>
                                            <span class="sr-only">Stop</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Saved Timers Section -->
        <div class="mt-8">
            <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Saved Timers</h2>
                <div class="flex items-center gap-4">
                    <div class="relative flex-grow max-w-md">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="savedTimersSearch"
                            placeholder="Search timers..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm pr-10 px-3 py-2">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $savedTimers->count() }} timers</span>
                </div>
            </div>

            @if($savedTimers->isNotEmpty())
            <div class="ch-card">
                <div class="overflow-x-auto">
                    <table class="ch-table">
                        <thead class="ch-table-header">
                            <tr>
                                <th scope="col" class="ch-table-header-cell">Name</th>
                                <th scope="col" class="ch-table-header-cell">Project</th>
                                <th scope="col" class="ch-table-header-cell">Tags</th>
                                <th scope="col" class="ch-table-header-cell">Last Used</th>
                                <th scope="col" class="ch-table-header-cell">Duration</th>
                                <th scope="col" class="ch-table-header-cell text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="ch-table-body">
                            @foreach($savedTimers as $timer)
                            <tr class="ch-table-row">
                                <td class="ch-table-cell">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                                {{ $timer->name }}
                                                @if($timer->jira_key)
                                                <x-jira-issue-tooltip :issueKey="$timer->jira_key">
                                                    <a href="{{ auth()->user()->jira_site_url }}/browse/{{ $timer->jira_key }}"
                                                        target="_blank"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 inline-flex items-center"
                                                        title="View in Jira">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                </x-jira-issue-tooltip>
                                                @endif
                                            </div>
                                            @if($timer->description)
                                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $timer->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="ch-table-cell">
                                    @if($timer->project)
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $timer->project->name }}</div>
                                    @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">No project</span>
                                    @endif
                                </td>
                                <td class="ch-table-cell">
                                    @if($timer->tags->count() > 0)
                                    <div class="ch-tag-list max-w-xs">
                                        @foreach($timer->tags as $tag)
                                        <span class="ch-tag"
                                            style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                            {{ $tag->name }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">No tags</span>
                                    @endif
                                </td>
                                <td class="ch-table-cell text-sm text-gray-500 dark:text-gray-400">
                                    @if($timer->latestTimeLog)
                                    {{ $timer->latestTimeLog->created_at->diffForHumans() }}
                                    @else
                                    Never used
                                    @endif
                                </td>
                                <td class="ch-table-cell text-sm text-gray-500 dark:text-gray-400">
                                    @if($timer->latestTimeLog && $timer->latestTimeLog->duration_minutes)
                                    {{ $this->formatDuration($timer->latestTimeLog->duration_minutes * 60) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="ch-table-cell text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button
                                            wire:click="restartTimer({{ $timer->id }})"
                                            class="ch-btn-primary inline-flex items-center px-2.5 py-1.5 text-xs"
                                            title="Restart timer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="editTimer({{ $timer->id }})"
                                            class="ch-btn-secondary inline-flex items-center px-2.5 py-1.5 text-xs"
                                            title="Edit timer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="deleteTimer({{ $timer->id }})"
                                            wire:confirm="Are you sure you want to delete this timer?"
                                            class="ch-btn-danger inline-flex items-center px-2.5 py-1.5 text-xs"
                                            title="Delete timer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="ch-card">
                <div class="ch-empty-state">
                    <div class="ch-empty-state-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h3 class="ch-empty-state-title">No Saved Timers</h3>
                    <p class="ch-empty-state-description">
                        Start a new timer to track your time. Your stopped timers will appear here.
                    </p>
                </div>
            </div>
            @endif
        </div>

        <!-- Jira Issues List -->
        @php
        $user = auth()->user();
        $hasJiraEnabled = $user->jira_enabled && $user->jira_access_token && $user->jira_cloud_id && $user->jira_site_url;
        @endphp
        @if($hasJiraEnabled)
        <livewire:jira-issues-list />
        @endif
    </div>

    <!-- Include Modals -->
    @include('livewire.timers.modals.long-running-timer-modal')
    @include('livewire.timers.modals.edit-timer-modal')
    @include('livewire.timers.modals.new-timer-modal')
    @include('livewire.timers.modals.restart-timer-modal')

    @push('scripts')
    <script>
        // Use the improved timer manager
        document.addEventListener('DOMContentLoaded', () => {
            initializeTimerManager();
        });

        // Also initialize when Livewire updates occur
        document.addEventListener('livewire:navigated', () => {
            initializeTimerManager();
        });

        function initializeTimerManager() {
            // Wait for TimerManager to be available
            if (typeof window.TimerManager === 'undefined') {
                console.log('Waiting for TimerManager to load...');
                setTimeout(initializeTimerManager, 100);
                return;
            }

            // Stop any existing timer manager
            if (window.globalTimerManager) {
                window.globalTimerManager.stop();
            }

            // Create and initialize the timer manager
            window.globalTimerManager = new window.TimerManager('timers-page');
            window.globalTimerManager.initialize();

            // Log timer elements for debugging
            const timerElements = document.querySelectorAll('.timer-display');
            console.log(`Found ${timerElements.length} timer elements`);

            timerElements.forEach(element => {
                console.log(`Timer element: ${element.id}`, {
                    'data-start': element.dataset.start,
                    'time-format': element.dataset.timeFormat
                });
            });
        }

        // Add animations for timer actions
        document.addEventListener('timerStarted', (event) => {
            // Get event details
            const detail = event.detail || {};
            const wasPaused = detail.wasPaused || false;

            // Flash notification animation
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-500 ease-in-out z-50';
            notification.textContent = wasPaused ? 'Timer resumed successfully' : 'Timer started successfully';
            document.body.appendChild(notification);

            // If we have total duration info, update the display
            if (detail.totalDuration && detail.timerId) {
                // Find the timer element's parent container
                const timerElement = document.getElementById(`timer-${detail.timerId}`);
                if (timerElement) {
                    const parentContainer = timerElement.closest('.flex');
                    if (parentContainer) {
                        const totalDurationElement = parentContainer.querySelector('.text-gray-500');
                        if (totalDurationElement && totalDurationElement.textContent.includes('Today:')) {
                            // Update the total duration text
                            totalDurationElement.textContent = `Today: ${detail.totalDuration}`;
                        }
                    }
                }
            }

            // Ensure the timer manager is reinitialized
            if (window.globalTimerManager) {
                window.globalTimerManager.initialized = false;
                window.globalTimerManager.initialize();
            }

            setTimeout(() => {
                notification.classList.add('opacity-0', 'translate-y-[-10px]');
                setTimeout(() => notification.remove(), 500);
            }, 2000);
        });

        document.addEventListener('timerStopped', () => {
            // Flash notification animation
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-500 ease-in-out z-50';
            notification.textContent = 'Timer stopped successfully';
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('opacity-0', 'translate-y-[-10px]');
                setTimeout(() => notification.remove(), 500);
            }, 2000);
        });

        document.addEventListener('timerPaused', (event) => {
            // Get event details
            const detail = event.detail || {};

            // Flash notification animation
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-yellow-500 text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-500 ease-in-out z-50';
            notification.textContent = 'Timer paused successfully';
            document.body.appendChild(notification);

            // If we have duration info, update the display in the paused timers section
            if ((detail.totalDuration || detail.lastDuration) && detail.timerId) {
                // Wait for the DOM to update with the paused timer
                setTimeout(() => {
                    try {
                        // Find the paused timer in the paused timers section
                        const pausedTimerElements = document.querySelectorAll('.font-mono.text-yellow-600');
                        pausedTimerElements.forEach(element => {
                            // Get all spans in the element
                            const spans = element.querySelectorAll('span');

                            // Update Today duration
                            if (detail.totalDuration && spans.length > 0) {
                                const todaySpan = spans[0];
                                if (todaySpan && todaySpan.textContent.includes('Today:')) {
                                    // Update the total duration text
                                    todaySpan.textContent = `Today: ${detail.totalDuration}`;

                                    // Store the total duration seconds in a data attribute for real-time updates
                                    if (detail.totalDuration.includes(':')) {
                                        const parts = detail.totalDuration.split(':');
                                        let hours = 0,
                                            minutes = 0,
                                            seconds = 0;

                                        if (parts.length === 3) {
                                            [hours, minutes, seconds] = parts.map(Number);
                                        } else if (parts.length === 2) {
                                            [minutes, seconds] = parts.map(Number);
                                        } else if (parts.length === 1) {
                                            seconds = Number(parts[0]);
                                        }

                                        const totalSeconds = hours * 3600 + minutes * 60 + seconds;
                                        element.dataset.totalSeconds = totalSeconds;
                                        element.dataset.lastUpdated = Date.now();
                                    }
                                }
                            }

                            // Update Last duration
                            if (detail.lastDuration && spans.length > 2) {
                                const lastSpan = spans[2];
                                if (lastSpan && lastSpan.textContent.includes('Last:')) {
                                    // Update the last duration text
                                    lastSpan.textContent = `Last: ${detail.lastDuration}`;
                                }
                            }
                        });
                    } catch (error) {
                        console.error('Error updating paused timer display:', error);
                    }
                }, 500); // Give the DOM time to update
            }

            setTimeout(() => {
                notification.classList.add('opacity-0', 'translate-y-[-10px]');
                setTimeout(() => notification.remove(), 500);
            }, 2000);
        });
    </script>
    @endpush
</div>