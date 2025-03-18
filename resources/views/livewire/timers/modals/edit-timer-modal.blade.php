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
            <!-- Unified Timer Selector -->
            <div class="ch-form-group">
                @livewire('components.unified-timer-selector', [
                'timerId' => $editingTimerId,
                'timerDescriptionId' => $editingTimerDescriptionId,
                'projectId' => null,
                'timerName' => $editingTimerName,
                'description' => $editingTimerDescription,
                'projectName' => $editingTimerProjectName
                ], key('edit-timer-unified-selector'))
            </div>

            <!-- Tags -->
            <div class="ch-form-group">
                <label for="editingTimerTagInput" class="ch-label">Tags</label>
                <input
                    type="text"
                    id="editingTimerTagInput"
                    wire:model="editingTimerTagInput"
                    placeholder="Comma-separated tags"
                    class="ch-input">
            </div>

            <!-- Time Duration (only shown when editing a time log) -->
            @if($editingTimeLogId)
            <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Edit Time Duration</h4>
                @livewire('components.time-input', [
                'value' => $editingDurationHuman,
                'name' => 'editingDurationHuman',
                'inputId' => 'editingDurationHuman',
                'label' => 'Duration',
                'showPresets' => true,
                'showIncrementButtons' => true,
                'helpText' => 'Enter the total time spent on this task using format like "1h 30m", "1:30", or "90"',
                ], key('edit-timer-duration-input'))
            </div>
            @endif

            <div class="ch-modal-footer">
                <button
                    type="button"
                    wire:click="closeEditTimerModal"
                    class="ch-btn-secondary">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="ch-btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endif