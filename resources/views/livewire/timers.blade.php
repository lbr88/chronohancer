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
                                                        
                                                        <div class="timer-display font-mono text-indigo-600 dark:text-indigo-400" data-start="{{ $timer->latestTimeLog->start_time ?? now() }}">
                                                            00:00:00
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
                                        
                                        <div class="flex-shrink-0 flex items-center">
                                            <button
                                                wire:click="stopTimer({{ $timer->id }})"
                                                class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
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
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            const timer = new window.TimerManager('timers');
            timer.initialize();
            
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
        });
    </script>
    @endpush
</div>
