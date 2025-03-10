<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Timers</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Track your time with timers.</p>
            </div>
        </div>

        <div class="mt-6 max-w-2xl">
            <form wire:submit.prevent="startTimer" class="space-y-4">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="Search existing timers..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    >
                    @if($existingTimers->isNotEmpty())
                        <div class="absolute z-10 mt-1 w-full rounded-md bg-white dark:bg-gray-800 shadow-lg">
                            <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                @foreach($existingTimers as $timer)
                                    <li wire:click="useExistingTimer({{ $timer->id }})" class="cursor-pointer relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                        <div class="flex items-center">
                                            <span class="block truncate">{{ $timer->name }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        required
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    >
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="2"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    ></textarea>
                </div>

                <div class="relative">
                    <label for="project_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project (optional)</label>
                    <input
                        type="text"
                        id="project_name"
                        wire:model.live="project_name"
                        placeholder="Enter project name"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    >
                    @if(!empty($suggestions['projects']) && count($suggestions['projects']) > 0)
                        <div class="absolute z-10 mt-1 w-full rounded-md bg-white dark:bg-gray-800 shadow-lg">
                            <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                @foreach($suggestions['projects'] as $project)
                                    <li wire:click="selectProject({{ $project->id }})" class="cursor-pointer relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                        <div class="flex items-center">
                                            <span class="block truncate">{{ $project->name }}</span>
                                        </div>
                                        @if($project->tags->count() > 0)
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach($project->tags as $tag)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs" style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
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

                <div>
                    <label for="tag_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags (comma-separated)</label>
                    <input
                        type="text"
                        id="tag_input"
                        wire:model="tag_input"
                        placeholder="e.g. important, coding, meeting"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    >
                    @if($recentTags->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($recentTags as $tag)
                                <button
                                    type="button"
                                    wire:click="$set('tag_input', '{{ $tag_input ? $tag_input . ', ' . $tag->name : $tag->name }}')"
                                    class="inline-flex items-center px-2 py-1 rounded text-sm hover:ring-2 hover:ring-offset-2 hover:ring-indigo-500"
                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}"
                                >
                                    {{ $tag->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Start Timer
                    </button>
                </div>
            </form>
        </div>

        @if($runningTimers->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Running Timers</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($runningTimers as $timer)
                        <div class="relative group bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $timer->name }}</h3>
                                    @if($timer->project)
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Project: {{ $timer->project->name }}
                                        </p>
                                    @endif
                                </div>
                                <button
                                    wire:click="stopTimer({{ $timer->id }})"
                                    class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Stop
                                </button>
                            </div>
                            
                            @if($timer->description)
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $timer->description }}</p>
                            @endif

                            @if($timer->tags->count() > 0)
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @foreach($timer->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs" 
                                              style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-2 text-sm font-medium text-gray-900 dark:text-white timer-display" data-start="{{ $timer->latestTimeLog->start_time ?? now() }}">
                                00:00:00
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @push('scripts')
        <script src="{{ asset('js/timer-manager.js') }}"></script>
        <script>
            document.addEventListener('livewire:init', () => {
                window.timersPageTimer = new TimerManager('timers');
                window.timersPageTimer.initialize();
            });
        </script>
        @endpush
    </div>
</div>
