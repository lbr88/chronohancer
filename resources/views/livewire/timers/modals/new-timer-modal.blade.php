@if($showNewTimerModal)
<div class="ch-modal-overlay">
    <div class="ch-modal-container">
        <div class="ch-modal-header">
            <h3 class="ch-modal-title">New Timer</h3>
            <button wire:click="closeNewTimerModal" class="ch-modal-close-btn">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="startTimer" class="ch-form">
            <!-- Unified Timer Selector -->
            <div class="ch-form-group">
                @livewire('components.unified-timer-selector', [
                'timerId' => null,
                'timerDescriptionId' => $timerDescriptionId,
                'projectId' => $project_id
                ], key('new-timer-unified-selector'))
            </div>

            <!-- Jira Issues Search -->
            @php
            $user = auth()->user();
            $hasJiraEnabled = $user->jira_enabled && $user->jira_access_token && $user->jira_cloud_id && $user->jira_site_url;
            @endphp
            @if($hasJiraEnabled)
            <div class="ch-form-group">
                <label for="jiraSearch" class="ch-label">Search Jira Issues</label>
                <div class="ch-search-input-wrapper">
                    <input
                        type="text"
                        id="jiraSearch"
                        wire:model.live="jiraSearch"
                        placeholder="Search Jira issues..."
                        class="ch-search-input">
                    <div class="ch-search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                @if($jiraIssues->isNotEmpty())
                <div class="ch-dropdown">
                    <ul class="ch-dropdown-list">
                        @foreach($jiraIssues as $issue)
                        <li wire:click="useJiraIssue('{{ $issue['key'] }}', '{{ addslashes($issue['fields']['summary']) }}')" class="ch-dropdown-item">
                            <div class="flex items-center">
                                <span class="block font-medium text-gray-900 dark:text-white">{{ $issue['key'] }}: {{ $issue['fields']['summary'] }}</span>
                            </div>
                            <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <span class="mr-2">{{ $issue['fields']['status']['name'] }}</span>
                                @if(!empty($issue['fields']['labels']))
                                <div class="ch-tag-list">
                                    @foreach(array_slice($issue['fields']['labels'], 0, 3) as $label)
                                    <span class="ch-tag">{{ $label }}</span>
                                    @endforeach
                                    @if(count($issue['fields']['labels']) > 3)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">+{{ count($issue['fields']['labels']) - 3 }} more</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif

            <!-- Tags -->
            <div class="ch-form-group">
                <label class="ch-label">Tags</label>
                @livewire('components.tag-selector', [], key('new-timer-tag-selector'))
            </div>

            <!-- Start Button -->
            <div class="ch-modal-footer">
                <button
                    type="button"
                    wire:click="closeNewTimerModal"
                    class="ch-btn-secondary">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="ch-btn-primary flex justify-center items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Start Timer
                </button>
            </div>
        </form>
    </div>
</div>
@endif