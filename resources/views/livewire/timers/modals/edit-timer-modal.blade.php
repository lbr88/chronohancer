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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                >
            </div>
            
            <!-- Description -->
            <div>
                <label for="editingTimerDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea
                    id="editingTimerDescription"
                    wire:model="editingTimerDescription"
                    rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                ></textarea>
            </div>
            
            <!-- Project -->
            <div>
                <label for="editingTimerProjectName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                <input
                    type="text"
                    id="editingTimerProjectName"
                    wire:model="editingTimerProjectName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                >
            </div>
            
            <!-- Time Duration (only shown when editing a time log) -->
            @if($editingTimeLogId)
            <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Edit Time Duration</h4>
                <div>
                    <label for="editingDurationHuman" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                    <input
                        type="text"
                        id="editingDurationHuman"
                        wire:model="editingDurationHuman"
                        placeholder="e.g., 1h 30m"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                    >
                </div>
                
                <!-- Quick Duration Buttons -->
                <div class="mt-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Quick add:</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '15m')"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-150"
                        >
                            15m
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '30m')"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-150"
                        >
                            30m
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '1h')"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-150"
                        >
                            1h
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '1h 30m')"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-150"
                        >
                            1h 30m
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '2h')"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-150"
                        >
                            2h
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '4h')"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-150"
                        >
                            4h
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '8h')"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-150"
                        >
                            8h
                        </button>
                    </div>
                </div>
                
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Enter the total time spent on this task using format like "1h 30m" or "45m".
                </p>
            </div>
            @endif
            
            <div class="mt-5 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="closeEditTimerModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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