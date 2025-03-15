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
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm pr-10 px-3 py-2"
                >
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
                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium {{ $showMyIssues ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:text-gray-900' }} border border-gray-300 rounded-l-md focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        My Issues
                    </button>
                    <button
                        wire:click="toggleFavoriteFilter"
                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium {{ $showFavoritesOnly ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700 hover:text-gray-900' }} border border-gray-300 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Favorites
                    </button>
                    <button
                        wire:click="toggleDoneIssues"
                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium {{ $showDoneIssues ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:text-gray-900' }} border border-gray-300 rounded-r-md focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    >
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
                    {{ $showFavoritesOnly ? 'No favorite issues yet. Star some issues to see them here.' : 'No issues match your search criteria.' }}
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
                                        <!-- Favorite Toggle -->
                                        <button
                                            wire:click="toggleFavorite('{{ $issue['id'] }}', '{{ $issue['key'] }}', '{{ addslashes($issue['fields']['summary']) }}', '{{ $issue['fields']['status']['name'] }}')"
                                            class="ch-btn-icon-secondary"
                                            title="{{ $favoriteIds->contains($issue['id']) ? 'Remove from favorites' : 'Add to favorites' }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="h-5 w-5 {{ $favoriteIds->contains($issue['id']) ? 'text-yellow-400 fill-current' : 'text-gray-400' }}"
                                                 viewBox="0 0 20 20"
                                                 fill="{{ $favoriteIds->contains($issue['id']) ? 'currentColor' : 'none' }}"
                                                 stroke="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Create Timer -->
                                        <button
                                            wire:click="createTimer('{{ $issue['key'] }}', '{{ addslashes($issue['fields']['summary']) }}', {{ json_encode($issue['fields']['labels'] ?? []) }})"
                                            class="ch-btn-icon-primary"
                                            title="Create timer for this issue"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
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
</div>