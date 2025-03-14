@if($showEditTimerModal)
<div class="ch-modal-overlay">
    <div class="ch-modal-container">
        <div class="ch-modal-header">
            <h3 class="ch-modal-title">Edit Timer</h3>
            <button wire:click="closeEditTimerModal" class="ch-modal-close-btn">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form wire:submit.prevent="saveEditedTimer" class="ch-form">
            <!-- Timer Name -->
            <div class="ch-form-group">
                <label for="editingTimerName" class="ch-label">Timer Name</label>
                <input
                    type="text"
                    id="editingTimerName"
                    wire:model="editingTimerName"
                    required
                    class="ch-input"
                >
            </div>
            
            <!-- Description -->
            <div class="ch-form-group">
                <label for="editingTimerDescription" class="ch-label">Description</label>
                <textarea
                    id="editingTimerDescription"
                    wire:model="editingTimerDescription"
                    rows="2"
                    class="ch-textarea"
                ></textarea>
            </div>
            
            <!-- Project -->
            <div class="ch-form-group">
                <label for="editingTimerProjectName" class="ch-label">Project</label>
                <input
                    type="text"
                    id="editingTimerProjectName"
                    wire:model="editingTimerProjectName"
                    class="ch-input"
                >
            </div>
            
            <!-- Tags -->
            <div class="ch-form-group">
                <label for="editingTimerTagInput" class="ch-label">Tags</label>
                <input
                    type="text"
                    id="editingTimerTagInput"
                    wire:model="editingTimerTagInput"
                    placeholder="Comma-separated tags"
                    class="ch-input"
                >
            </div>
            
            <!-- Time Duration (only shown when editing a time log) -->
            @if($editingTimeLogId)
            <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Edit Time Duration</h4>
                <div class="ch-form-group">
                    <label for="editingDurationHuman" class="ch-label">Duration</label>
                    <input
                        type="text"
                        id="editingDurationHuman"
                        wire:model="editingDurationHuman"
                        placeholder="e.g., 1h 30m"
                        class="ch-input"
                    >
                </div>
                
                <!-- Quick Duration Buttons -->
                <div class="mt-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Quick add:</p>
                    <div class="ch-tag-list">
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '15m')"
                            class="ch-tag bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            15m
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '30m')"
                            class="ch-tag bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            30m
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '1h')"
                            class="ch-tag bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            1h
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '1h 30m')"
                            class="ch-tag bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            1h 30m
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '2h')"
                            class="ch-tag bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            2h
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '4h')"
                            class="ch-tag bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            4h
                        </button>
                        <button
                            type="button"
                            wire:click="$set('editingDurationHuman', '8h')"
                            class="ch-tag bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
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
            
            <div class="ch-modal-footer">
                <button
                    type="button"
                    wire:click="closeEditTimerModal"
                    class="ch-btn-secondary"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="ch-btn-primary"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endif