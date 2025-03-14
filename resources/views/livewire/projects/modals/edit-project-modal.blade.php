<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center" style="display: {{ $showEditProjectModal ? 'flex' : 'none' }}">
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                ></textarea>
                @error('editingProjectDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Color -->
            <div>
                <label for="editingProjectColor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                <div class="flex items-center space-x-3 mt-1">
                    <input
                        type="color"
                        wire:model.live="editingProjectColor"
                        id="editingProjectColor"
                        class="h-10 w-10 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer"
                    >
                    <div class="flex-1">
                        <input
                            type="text"
                            wire:model="editingProjectColor"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                            placeholder="#RRGGBB"
                        >
                    </div>
                </div>
                <div class="mt-2 flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Preview:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm"
                        style="background-color: {{ $editingProjectColor }}; color: {{ $this->getContrastColor($editingProjectColor) }}">
                        {{ $editingProjectName ?: 'Sample Project' }}
                    </span>
                </div>
                @error('editingProjectColor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Tags -->
            <div>
                <label for="editingProjectTagInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                <input
                    type="text"
                    id="editingProjectTagInput"
                    wire:model="editingProjectTagInput"
                    placeholder="Comma-separated tags"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                >
            </div>
            
            <!-- Default Project Checkbox -->
            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="editingProjectIsDefault"
                    wire:model="editingProjectIsDefault"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                >
                <label for="editingProjectIsDefault" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Set as default project
                    <span class="text-yellow-500 ml-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="currentColor" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </span>
                </label>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 ml-6">
                The default project will be used when no project is selected for timers and time logs.
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