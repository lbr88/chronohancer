<div class="p-6 lg:p-8 max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Projects</h1>
        <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Organize your work into projects for better time tracking</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-6 rounded-md" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 mb-6 rounded-md" role="alert">
            <p>Error: {{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Projects List Panel -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">All Projects</h2>
                    
                    <!-- Search Box -->
                    <div class="relative max-w-xs">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search projects..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm pr-10"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($projects as $project)
                        <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-200">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $project->name }}</h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $project->description }}</p>
                                    
                                    @if($project->tags->count() > 0)
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach($project->tags as $tag)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs"
                                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-shrink-0 flex items-center space-x-2">
                                    <div class="flex space-x-2">
                                        <button
                                            wire:click="editProject({{ $project->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        
                                        <button
                                            wire:click="deleteProject({{ $project->id }})"
                                            wire:confirm="Are you sure you want to delete this project?"
                                            class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                        
                                        <a href="{{ route('timers') }}" class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Start Timer
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 flex flex-col items-center justify-center text-center">
                            <div class="h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">No Projects Yet</h3>
                            <p class="text-gray-500 dark:text-gray-400 max-w-md">
                                Create your first project to organize your work and track time more effectively.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Create Project Panel -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create New Project</h2>
            </div>
            
            <div class="p-5">
                <form class="space-y-5">
                    <!-- Project Name -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Name</label>
                        <input
                            type="text"
                            wire:model="name"
                            id="name"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                            placeholder="Enter project name"
                        >
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea
                            wire:model="description"
                            id="description"
                            rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                            placeholder="Describe the project"
                        ></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <label for="tag_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                        <input
                            type="text"
                            id="tag_input"
                            wire:model.live="tag_input"
                            placeholder="Add comma-separated tags"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                        >
                        
                        <!-- Tag Suggestions -->
                        @if(!empty($tagSuggestions) && count($tagSuggestions) > 0)
                            <div class="absolute z-10 mt-1 w-full rounded-lg bg-white dark:bg-gray-700 shadow-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                                <ul class="max-h-60 py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                    @foreach($tagSuggestions as $tag)
                                        <li wire:click="selectTag('{{ $tag->name }}')" class="cursor-pointer relative py-2 px-3 hover:bg-indigo-50 dark:hover:bg-gray-600 transition-colors duration-150">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs"
                                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                    {{ $tag->name }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
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

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button
                            type="button"
                            wire:click="save"
                            class="w-full flex justify-center items-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Project Modal -->
    @if($showEditProjectModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Project</h3>
                <button wire:click="closeEditProjectModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form wire:submit.prevent="saveEditedProject" class="space-y-4">
                <!-- Project Name -->
                <div>
                    <label for="editingProjectName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Name</label>
                    <input
                        type="text"
                        id="editingProjectName"
                        wire:model="editingProjectName"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                    @error('editingProjectName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Description -->
                <div>
                    <label for="editingProjectDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea
                        id="editingProjectDescription"
                        wire:model="editingProjectDescription"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    ></textarea>
                    @error('editingProjectDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Tags -->
                <div>
                    <label for="editingProjectTagInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                    <input
                        type="text"
                        id="editingProjectTagInput"
                        wire:model="editingProjectTagInput"
                        placeholder="Comma-separated tags"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                </div>
                
                <div class="mt-5 flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="closeEditProjectModal"
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
</div>
