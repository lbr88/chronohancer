<div class="mt-8">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Jira Issues</h2>
        <div class="flex items-center gap-4">
            <!-- Search -->
            <div class="relative flex-grow max-w-md">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search issues..."
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm pr-10 px-3 py-2">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <!-- Filter Buttons -->
            <div class="flex gap-2">
                <div class="flex rounded-md shadow-sm" role="group">
                    <button
                        wire:click="toggleMyIssues"
                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium {{ $showMyIssues ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:text-gray-900' }} border border-gray-300 rounded-l-md focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        My Issues
                    </button>
                    <button
                        wire:click="toggleDoneIssues"
                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium {{ $showDoneIssues ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:text-gray-900' }} border border-gray-300 rounded-r-md focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Done Issues
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (!$isConfigured)
    <div class="ch-card">
        <div class="ch-empty-state">
            <div class="ch-empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h3 class="ch-empty-state-title">Jira Not Configured</h3>
            <p class="ch-empty-state-description">
                Configure your Jira integration in settings to view and manage issues.
            </p>
        </div>
    </div>
    @elseif ($issues->isEmpty())
    <div class="ch-card">
        <div class="ch-empty-state">
            <div class="ch-empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="ch-empty-state-title">No Issues Found</h3>
            <p class="ch-empty-state-description">
                No issues match your search criteria.
            </p>
        </div>
    </div>
    @else
    <div class="ch-card">
        <div class="overflow-x-auto">
            <table class="ch-table">
                <thead class="ch-table-header">
                    <tr>
                        <th scope="col" class="ch-table-header-cell">Key</th>
                        <th scope="col" class="ch-table-header-cell">Title</th>
                        <th scope="col" class="ch-table-header-cell">Status</th>
                        <th scope="col" class="ch-table-header-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="ch-table-body">
                    @foreach($issues as $issue)
                    <tr class="ch-table-row">
                        <td class="ch-table-cell">
                            <div class="flex items-center gap-1">
                                @if(isset($issue['fields']['parent']))
                                <x-jira-issue-tooltip :issueKey="$issue['fields']['parent']['key']">
                                    <a href="{{ auth()->user()->jira_site_url }}/browse/{{ $issue['fields']['parent']['key'] }}"
                                        target="_blank"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        {{ $issue['fields']['parent']['key'] }}
                                    </a>
                                </x-jira-issue-tooltip>
                                <span class="text-gray-400 dark:text-gray-500">→</span>
                                @endif
                                <x-jira-issue-tooltip :issueKey="$issue['key']">
                                    <a href="{{ auth()->user()->jira_site_url }}/browse/{{ $issue['key'] }}"
                                        target="_blank"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        {{ $issue['key'] }}
                                    </a>
                                </x-jira-issue-tooltip>
                            </div>
                        </td>
                        <td class="ch-table-cell">
                            <div class="text-sm text-gray-900 dark:text-white" title="{{ $issue['fields']['summary'] }}">
                                {{ \Illuminate\Support\Str::limit($issue['fields']['summary'], 60) }}
                            </div>
                        </td>
                        <td class="ch-table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ $issue['fields']['status']['name'] }}
                            </span>
                        </td>
                        <td class="ch-table-cell text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <!-- Favorite status indicator removed as Jira's API doesn't support getting starred items -->

                                <!-- Timer buttons -->
                                @php
                                $hasTimer = $existingTimerIssueKeys->contains($issue['key']);
                                @endphp

                                @if($hasTimer)
                                <!-- Start Timer (only show if timer exists for this issue) -->
                                <button
                                    wire:click="openTimerModal('{{ $issue['key'] }}', '{{ addslashes($issue['fields']['summary']) }}', {{ json_encode($issue['fields']['labels'] ?? []) }}, 'start')"
                                    class="ch-btn-icon-success"
                                    title="Configure and start timer for this issue">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                @else
                                <!-- Create Timer -->
                                <button
                                    wire:click="createTimer('{{ $issue['key'] }}', '{{ addslashes($issue['fields']['summary']) }}', {{ json_encode($issue['fields']['labels'] ?? []) }})"
                                    class="ch-btn-icon-primary"
                                    title="Create timer for this issue">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                <!-- Start Timer -->
                                <button
                                    wire:click="openTimerModal('{{ $issue['key'] }}', '{{ addslashes($issue['fields']['summary']) }}', {{ json_encode($issue['fields']['labels'] ?? []) }}, 'create')"
                                    class="ch-btn-icon-success"
                                    title="Create and configure timer for this issue">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button wire:click="previousPage" wire:loading.attr="disabled" class="ch-btn-secondary">
                        Previous
                    </button>
                    <button wire:click="nextPage" wire:loading.attr="disabled" class="ch-btn-secondary">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ ($page - 1) * $perPage + 1 }}</span>
                            to
                            <span class="font-medium">{{ min($page * $perPage, $total) }}</span>
                            of {{ $total }} results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button wire:click="previousPage" wire:loading.attr="disabled" class="ch-pagination-btn rounded-l-md">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button wire:click="nextPage" wire:loading.attr="disabled" class="ch-pagination-btn rounded-r-md">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Timer Modal -->
    <div x-data="{ show: @entangle('showTimerModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button wire:click="closeTimerModal" type="button" class="bg-white dark:bg-gray-800 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            {{ $timerAction === 'create' ? 'Create Timer' : 'Start Timer' }} for {{ $issueKey }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <!-- Project Selector -->
                            <div>
                                <label for="project" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                                <div class="mt-1">
                                    <livewire:components.project-selector :project-id="$projectId" wire:key="project-selector-{{ now() }}" />
                                </div>
                            </div>

                            <!-- Timer Description Selector -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <div class="mt-1">
                                    <livewire:components.timer-description-selector :timer-id="null" :description="$description" wire:key="timer-description-selector-{{ now() }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button wire:click="submitTimerForm" type="button" class="w-full inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $timerAction === 'create' ? 'Create & Start' : 'Start Timer' }}
                    </button>
                    <button wire:click="closeTimerModal" type="button" class="mt-3 w-full inline-flex items-center justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>