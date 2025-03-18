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
            <!-- Timer Name -->
            <div class="ch-form-group">
                <label for="name" class="ch-label">Timer Name</label>
                <div class="relative">
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        required
                        placeholder="What are you working on?"
                        class="ch-input">

                    <!-- Search Existing Timers -->
                    <div class="mt-2">
                        <div class="ch-search-input-wrapper">
                            <input
                                type="text"
                                wire:model.live="search"
                                placeholder="Search existing timers..."
                                class="ch-search-input">
                            <div class="ch-search-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        @if($existingTimers->isNotEmpty())
                        <div class="ch-dropdown">
                            <ul class="ch-dropdown-list">
                                @foreach($existingTimers as $timer)
                                <li wire:click="useExistingTimer({{ $timer->id }})" class="ch-dropdown-item">
                                    <div class="flex items-center">
                                        <span class="block font-medium text-gray-900 dark:text-white">{{ $timer->name }}</span>
                                    </div>
                                    <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @if($timer->project)
                                        <span class="mr-2">{{ $timer->project->name }}</span>
                                        @endif
                                        @if($timer->tags->count() > 0)
                                        <div class="ch-tag-list">
                                            @foreach($timer->tags->take(3) as $tag)
                                            <span class="ch-tag"
                                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                {{ $tag->name }}
                                            </span>
                                            @endforeach
                                            @if($timer->tags->count() > 3)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">+{{ $timer->tags->count() - 3 }} more</span>
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

                    <!-- Jira Issues Search -->
                    @if(auth()->user()->hasJiraEnabled())
                    <div class="mt-2">
                        <div class="ch-search-input-wrapper">
                            <input
                                type="text"
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
                </div>
            </div>

            <!-- Description -->
            <div class="ch-form-group">
                <label for="description" class="ch-label">Description</label>
                <textarea
                    id="description"
                    wire:model="description"
                    rows="2"
                    placeholder="Add details about this task (optional)"
                    class="ch-textarea"></textarea>
            </div>

            <!-- Project Selection -->
            <div class="ch-form-group">
                <label for="project_name" class="ch-label">Project</label>
                @livewire('components.project-selector', ['projectId' => $project_id], key('new-timer-project-selector'))
            </div>

            <!-- Tags -->
            <div class="ch-form-group">
                <label for="tag_input" class="ch-label">Tags</label>
                <input
                    type="text"
                    id="tag_input"
                    wire:model="tag_input"
                    placeholder="Add comma-separated tags"
                    class="ch-input">

                @if($recentTags->isNotEmpty())
                <div class="mt-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Recent tags:</p>
                    <div class="ch-tag-list">
                        @foreach($recentTags as $tag)
                        <button
                            type="button"
                            wire:click="$set('tag_input', '{{ $tag_input ? $tag_input . ', ' . $tag->name : $tag->name }}')"
                            class="ch-tag hover:ring-2 hover:ring-offset-1 hover:ring-indigo-300"
                            style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                            {{ $tag->name }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
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