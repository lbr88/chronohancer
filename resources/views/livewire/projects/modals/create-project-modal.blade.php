<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center" style="display: {{ $showCreateProjectModal ? 'flex' : 'none' }}">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create New Project</h3>
            <button wire:click="closeCreateProjectModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form wire:submit.prevent="save" class="space-y-4">
            <!-- Project Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Name</label>
                <input
                    type="text"
                    id="name"
                    wire:model="name"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                    placeholder="Enter project name"
                >
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea
                    id="description"
                    wire:model="description"
                    rows="3"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                    placeholder="Describe the project"
                ></textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                            placeholder="#RRGGBB"
                        >
                    </div>
                </div>
                <div class="mt-2 flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Preview:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm"
                        style="background-color: {{ $color }}; color: {{ $this->getContrastColor($color) }}">
                        {{ $name ?: 'Sample Project' }}
                    </span>
                </div>
                @error('color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Tags -->
            <div>
                <label for="tag_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                <input
                    type="text"
                    id="tag_input"
                    wire:model.live="tag_input"
                    placeholder="Add comma-separated tags"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                >
                
                <!-- Tag Suggestions -->
                @if(!empty($tagSuggestions) && count($tagSuggestions) > 0)
                    <div class="absolute z-10 mt-1 w-full rounded-lg bg-white dark:bg-gray-700 shadow-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                        <ul class="max-h-60 py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                            @foreach($tagSuggestions as $tag)
                                <li wire:click="selectTag('{{ $tag->name }}')" class="cursor-pointer relative py-2 px-3 hover:bg-indigo-50 dark:hover:bg-gray-600 transition-colors duration-150">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs"
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
            
            <div class="mt-5 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="closeCreateProjectModal"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>