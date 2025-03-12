<div class="timer-page">
    <div class="p-6 lg:p-8 max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Time Tracker</h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Manage your time efficiently with intuitive timers</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Timer Creation Panel -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">New Timer</h2>
                </div>
                
                <div class="p-5">
                    <form wire:submit.prevent="startTimer" class="space-y-5">
                        <!-- Timer Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Name</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="name"
                                    wire:model="name"
                                    required
                                    placeholder="What are you working on?"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                                >
                                
                                <!-- Search Existing Timers -->
                                <div class="mt-2">
                                    <div class="relative">
                                        <input
                                            type="text"
                                            wire:model.live="search"
                                            placeholder="Search existing timers..."
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm transition-all duration-200"
                                        >
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    @if($existingTimers->isNotEmpty())
                                        <div class="absolute z-10 mt-1 w-full rounded-lg bg-white dark:bg-gray-700 shadow-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                                            <ul class="max-h-60 py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                                @foreach($existingTimers as $timer)
                                                    <li wire:click="useExistingTimer({{ $timer->id }})" class="cursor-pointer relative py-2 px-3 hover:bg-indigo-50 dark:hover:bg-gray-600 transition-colors duration-150">
                                                        <div class="flex items-center">
                                                            <span class="block font-medium text-gray-900 dark:text-white">{{ $timer->name }}</span>
                                                        </div>
                                                        <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                            @if($timer->project)
                                                                <span class="mr-2">{{ $timer->project->name }}</span>
                                                            @endif
                                                            @if($timer->tags->count() > 0)
                                                                <div class="flex flex-wrap gap-1">
                                                                    @foreach($timer->tags->take(3) as $tag)
                                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs" 
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
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea
                                id="description"
                                wire:model="description"
                                rows="2"
                                placeholder="Add details about this task (optional)"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                            ></textarea>
                        </div>

                        <!-- Project Selection -->
                        <div class="space-y-2">
                            <label for="project_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="project_name"
                                    wire:model.live="project_name"
                                    placeholder="Select or create a project"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                                
                                @if(!empty($suggestions['projects']) && count($suggestions['projects']) > 0)
                                    <div class="absolute z-10 mt-1 w-full rounded-lg bg-white dark:bg-gray-700 shadow-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                                        <ul class="max-h-60 py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                            @foreach($suggestions['projects'] as $project)
                                                <li wire:click="selectProject({{ $project->id }})" class="cursor-pointer relative py-2 px-3 hover:bg-indigo-50 dark:hover:bg-gray-600 transition-colors duration-150">
                                                    <div class="flex items-center">
                                                        <span class="block font-medium text-gray-900 dark:text-white">{{ $project->name }}</span>
                                                    </div>
                                                    @if($project->tags->count() > 0)
                                                        <div class="mt-1 flex flex-wrap gap-1">
                                                            @foreach($project->tags as $tag)
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs" 
                                                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                                    {{ $tag->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="space-y-2">
                            <label for="tag_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                            <input
                                type="text"
                                id="tag_input"
                                wire:model="tag_input"
                                placeholder="Add comma-separated tags"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                            >
                            
                            @if($recentTags->isNotEmpty())
                                <div class="mt-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Recent tags:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($recentTags as $tag)
                                            <button
                                                type="button"
                                                wire:click="$set('tag_input', '{{ $tag_input ? $tag_input . ', ' . $tag->name : $tag->name }}')"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs hover:ring-2 hover:ring-offset-1 hover:ring-indigo-300 transition-all duration-150"
                                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}"
                                            >
                                                {{ $tag->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Start Button -->
                        <div class="pt-3">
                            <button 
                                type="submit" 
                                class="w-full flex justify-center items-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                            >
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

            <!-- Running Timers Panel -->
            <div class="lg:col-span-2">
                @if($runningTimers->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Running Timers</h2>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $runningTimers->count() }} active</span>
                        </div>
                        
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($runningTimers as $timer)
                                <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-200">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center">
                                                <div class="relative mr-3 flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                                        <div class="h-2 w-2 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $timer->name }}</h3>
                                                    
                                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                                        @if($timer->project)
                                                            <div class="flex items-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                                </svg>
                                                                <span>{{ $timer->project->name }}</span>
                                                            </div>
                                                        @endif
                                                        
                                                        <div
                                                            id="timer-{{ $timer->id }}"
                                                            class="timer-display font-mono text-indigo-600 dark:text-indigo-400"
                                                            data-start="{{ $this->getFormattedStartTimeForJs($timer) }}"
                                                        >
                                                            {{ $this->getTimerDuration($timer) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @if($timer->description)
                                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $timer->description }}
                                                </div>
                                            @endif
                                            
                                            @if($timer->tags->count() > 0)
                                                <div class="mt-3 flex flex-wrap gap-1.5">
                                                    @foreach($timer->tags as $tag)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs" 
                                                            style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                            {{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex-shrink-0 flex items-center space-x-2">
                                            <button
                                                wire:click="cancelTimer({{ $timer->id }})"
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                                title="Cancel timer without saving"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Cancel
                                            </button>
                                            
                                            <button
                                                wire:click="stopAndEditTimer({{ $timer->id }})"
                                                class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                                title="Stop timer and edit details"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Stop & Edit
                                            </button>
                                            
                                            <button
                                                wire:click="stopTimer({{ $timer->id }})"
                                                class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                                                title="Stop timer and save time"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                                </svg>
                                                Stop
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 flex flex-col items-center justify-center text-center h-full">
                        <div class="h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">No Active Timers</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-md">
                            Start a new timer to track your time. You can create multiple timers for different tasks.
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Saved Timers Section -->
        <div class="mt-12">
            <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Saved Timers</h2>
                <div class="flex items-center gap-4">
                    <div class="relative flex-grow max-w-md">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="savedTimersSearch"
                            placeholder="Search timers..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm pr-10"
                        >
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
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-750">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tags</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Used</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($savedTimers as $timer)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $timer->name }}</div>
                                                    @if($timer->description)
                                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $timer->description }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($timer->project)
                                                <div class="text-sm text-gray-900 dark:text-white">{{ $timer->project->name }}</div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">No project</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($timer->tags->count() > 0)
                                                <div class="flex flex-wrap gap-1.5 max-w-xs">
                                                    @foreach($timer->tags as $tag)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs"
                                                            style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                            {{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">No tags</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($timer->latestTimeLog)
                                                {{ $timer->latestTimeLog->created_at->diffForHumans() }}
                                            @else
                                                Never used
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($timer->latestTimeLog && $timer->latestTimeLog->duration_minutes)
                                                @php
                                                    $hours = floor($timer->latestTimeLog->duration_minutes / 60);
                                                    $minutes = $timer->latestTimeLog->duration_minutes % 60;
                                                    $formattedDuration = ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm';
                                                @endphp
                                                {{ $formattedDuration }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button
                                                    wire:click="restartTimer({{ $timer->id }})"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    title="Restart timer"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    wire:click="editTimer({{ $timer->id }})"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    title="Edit timer"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    wire:click="deleteTimer({{ $timer->id }})"
                                                    wire:confirm="Are you sure you want to delete this timer?"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                    title="Delete timer"
                                                >
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
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 flex flex-col items-center justify-center text-center">
                    <div class="h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">No Saved Timers</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md">
                        Start a new timer to track your time. Your stopped timers will appear here.
                    </p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Long Running Timer Modal -->
    @if($showLongRunningTimerModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Unusually Long Timer Detected</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    This timer has been running for a long time (8+ hours or since yesterday).
                    How would you like to handle the end time?
                </p>
            </div>
            
            <div class="space-y-4">
                <!-- Option 1: Custom End Time -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <label class="flex items-center">
                        <input type="radio" name="timerOption" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" checked>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Specify a custom end time</span>
                    </label>
                    <div class="mt-3">
                        <input
                            type="datetime-local"
                            wire:model="customEndTime"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                        >
                        <button
                            wire:click="useCustomEndTime"
                            class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Use Custom Time
                        </button>
                    </div>
                </div>
                
                <!-- Option 2: Actual Hours Worked -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <label class="flex items-center">
                        <input type="radio" name="timerOption" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Enter actual hours worked</span>
                    </label>
                    <div class="mt-3">
                        <input
                            type="number"
                            wire:model="actualHoursWorked"
                            step="0.25"
                            min="0.25"
                            placeholder="Hours worked"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                        >
                        <button
                            wire:click="useActualHoursWorked"
                            class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Use Actual Hours
                        </button>
                    </div>
                </div>
                
                <!-- Option 3: Current Time -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <label class="flex items-center">
                        <input type="radio" name="timerOption" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Use current time (now)</span>
                    </label>
                    <div class="mt-3">
                        <button
                            wire:click="useCurrentTime"
                            class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Use Current Time
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button
                    wire:click="cancelLongRunningTimerStop"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Edit Timer Modal -->
    @if($showEditTimerModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Timer</h3>
                <button wire:click="closeEditTimerModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form wire:submit.prevent="saveEditedTimer" class="space-y-4">
                <!-- Timer Name -->
                <div>
                    <label for="editingTimerName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Name</label>
                    <input
                        type="text"
                        id="editingTimerName"
                        wire:model="editingTimerName"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                </div>
                
                <!-- Description -->
                <div>
                    <label for="editingTimerDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea
                        id="editingTimerDescription"
                        wire:model="editingTimerDescription"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    ></textarea>
                </div>
                
                <!-- Project -->
                <div>
                    <label for="editingTimerProjectName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                    <input
                        type="text"
                        id="editingTimerProjectName"
                        wire:model="editingTimerProjectName"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                </div>
                
                <!-- Tags -->
                <div>
                    <label for="editingTimerTagInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                    <input
                        type="text"
                        id="editingTimerTagInput"
                        wire:model="editingTimerTagInput"
                        placeholder="Comma-separated tags"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                </div>
                
                <div class="mt-5 flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="closeEditTimerModal"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    
    @push('scripts')
    <script>
        // Use the improved timer manager
        document.addEventListener('DOMContentLoaded', () => {
            // Create a page-specific timer manager
            const pageTimerManager = new window.TimerManager('timers-page');
            pageTimerManager.initialize();
            
            // Log all timer elements for debugging
            const timerElements = document.querySelectorAll('.timer-display');
            console.log(`Found ${timerElements.length} timer elements on timers page`);
            
            timerElements.forEach(element => {
                console.log(`Timer element: ${element.id || 'unnamed'}`, {
                    'data-start': element.dataset.start,
                    'parsed-date': new Date(element.dataset.start).toString()
                });
            });
        });
        
        // Add animations for timer actions
        document.addEventListener('timerStarted', () => {
            // Flash notification animation
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-500 ease-in-out z-50';
            notification.textContent = 'Timer started successfully';
            document.body.appendChild(notification);
            
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
    </script>
    @endpush
</div>
