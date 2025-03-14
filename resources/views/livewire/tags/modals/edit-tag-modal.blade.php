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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
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
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
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