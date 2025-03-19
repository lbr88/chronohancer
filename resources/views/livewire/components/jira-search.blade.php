<div>
    @php
    $user = auth()->user();
    $hasJiraEnabled = $user->jira_enabled && $user->jira_access_token && $user->jira_cloud_id && $user->jira_site_url;
    @endphp

    @if($hasJiraEnabled)
    <div class="relative" x-data="{ open: @entangle('showDropdown') }" @click.away="open = false">
        <div class="relative">
            <input
                type="text"
                id="jiraSearchTerm"
                wire:model.live="searchTerm"
                wire:click="toggleDropdown"
                placeholder="Search Jira issues..."
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        @if($showDropdown && count($searchResults) > 0)
        <div class="absolute z-10 mt-1 w-full bg-white dark:bg-zinc-800 shadow-lg rounded-md py-1 max-h-60 overflow-auto">
            @foreach($searchResults as $issue)
            <div
                wire:key="issue-{{ $issue['key'] }}"
                wire:click="selectIssue('{{ $issue['key'] }}', '{{ addslashes($issue['fields']['summary']) }}')"
                class="px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer">
                <div class="font-medium text-gray-900 dark:text-white">
                    {{ $issue['key'] }}: {{ $issue['fields']['summary'] }}
                </div>
                <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span class="mr-2">{{ $issue['fields']['status']['name'] }}</span>
                    @if(!empty($issue['fields']['labels']))
                    <div class="flex flex-wrap gap-1">
                        @foreach(array_slice($issue['fields']['labels'], 0, 3) as $label)
                        <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                            {{ $label }}
                        </span>
                        @endforeach
                        @if(count($issue['fields']['labels']) > 3)
                        <span class="text-xs text-gray-500 dark:text-gray-400">+{{ count($issue['fields']['labels']) - 3 }} more</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif
</div>