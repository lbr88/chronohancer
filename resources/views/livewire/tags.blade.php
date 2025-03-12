<div>
    <div class="p-6 lg:p-8 max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Tags</h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Manage your tags for better organization</p>
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

        <div class="mb-6 flex justify-end">
            <button
                wire:click="openCreateTagModal"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create New Tag
            </button>
        </div>

        <!-- Tags List Panel -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">All Tags</h2>
                
                <!-- Search Box -->
                <div class="relative max-w-xs">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search tags..."
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
                @forelse($tags as $tag)
                    <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex-1 min-w-0 flex items-center">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full mr-3"
                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                    {{ strtoupper(substr($tag->name, 0, 1)) }}
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $tag->name }}</h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        Used in {{ $tag->timers->count() }} timers,
                                        {{ $tag->timeLogs->count() }} logs,
                                        {{ $tag->projects->count() }} projects
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0 flex items-center space-x-2">
                                <div class="flex space-x-2">
                                    <button
                                        wire:click="editTag({{ $tag->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                    
                                    <button
                                        wire:click="deleteTag({{ $tag->id }})"
                                        wire:confirm="Are you sure you want to delete this tag? It will be removed from all associated timers, logs, and projects."
                                        class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 flex flex-col items-center justify-center text-center">
                        <div class="h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">No Tags Yet</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-md">
                            Create your first tag to better organize your timers and projects.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Edit Tag Modal -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center" style="display: {{ $showEditTagModal ? 'flex' : 'none' }}">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Tag</h3>
                <button wire:click="closeEditTagModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form wire:submit.prevent="saveEditedTag" class="space-y-4">
                <!-- Tag Name -->
                <div>
                    <label for="editingTagName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag Name</label>
                    <input
                        type="text"
                        id="editingTagName"
                        wire:model="editingTagName"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                    @error('editingTagName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Color -->
                <div>
                    <label for="editingTagColor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                    <div class="flex items-center space-x-3 mt-1">
                        <input
                            type="color"
                            wire:model.live="editingTagColor"
                            id="editingTagColor"
                            class="h-10 w-10 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer"
                        >
                        <div class="flex-1">
                            <input
                                type="text"
                                wire:model="editingTagColor"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="#RRGGBB"
                            >
                        </div>
                    </div>
                    <div class="mt-2 flex items-center space-x-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Preview:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm"
                            style="background-color: {{ $editingTagColor }}; color: {{ $this->getContrastColor($editingTagColor) }}">
                            {{ $editingTagName ?: 'Sample Tag' }}
                        </span>
                    </div>
                    @error('editingTagColor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="mt-5 flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="closeEditTagModal"
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
    
    <!-- Create Tag Modal -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center" style="display: {{ $showCreateTagModal ? 'flex' : 'none' }}">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create New Tag</h3>
                <button wire:click="closeCreateTagModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form wire:submit.prevent="save" class="space-y-4">
                <!-- Tag Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Enter tag name"
                    >
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                    <div class="flex items-center space-x-3 mt-1">
                        <input
                            type="color"
                            wire:model.live="color"
                            id="color"
                            class="h-10 w-10 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer"
                        >
                        <div class="flex-1">
                            <input
                                type="text"
                                wire:model="color"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="#RRGGBB"
                            >
                        </div>
                    </div>
                    <div class="mt-2 flex items-center space-x-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Preview:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm"
                            style="background-color: {{ $color }}; color: {{ $this->getContrastColor($color) }}">
                            {{ $name ?: 'Sample Tag' }}
                        </span>
                    </div>
                    @error('color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="mt-5 flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="closeCreateTagModal"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Create Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>