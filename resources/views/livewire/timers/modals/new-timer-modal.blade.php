@if($showNewTimerModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">New Timer</h3>
            <button wire:click="closeNewTimerModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
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
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200 px-3 py-2"
                    >
                    
                    <!-- Search Existing Timers -->
                    <div class="mt-2">
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live="search"
                                placeholder="Search existing timers..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm transition-all duration-200 px-3 py-2"
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
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs"
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
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200 px-3 py-2"
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
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200 px-3 py-2"
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
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs"
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
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200 px-3 py-2"
                >
                
                @if($recentTags->isNotEmpty())
                    <div class="mt-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Recent tags:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($recentTags as $tag)
                                <button
                                    type="button"
                                    wire:click="$set('tag_input', '{{ $tag_input ? $tag_input . ', ' . $tag->name : $tag->name }}')"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs hover:ring-2 hover:ring-offset-1 hover:ring-indigo-300 transition-all duration-150"
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
            <div class="pt-3 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="closeNewTimerModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
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
@endif