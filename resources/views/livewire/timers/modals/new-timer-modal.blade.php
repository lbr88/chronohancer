@if($showNewTimerModal)
<div class="ch-modal-overlay">
    <div class="ch-modal-container">
        <div class="ch-modal-header">
            <h3 class="ch-modal-title">New Timer</h3>
            <button wire:click="closeNewTimerModal" class="ch-modal-close-btn">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="startTimer" class="ch-form">
            <!-- Unified Timer Selector -->
            <div class="ch-form-group">
                @livewire('components.unified-timer-selector', [
                'timerId' => null,
                'timerDescriptionId' => $timerDescriptionId,
                'projectId' => $project_id
                ], key('new-timer-unified-selector'))
            </div>

            <!-- Jira Issues Search Component -->
            @livewire('components.jira-search', [], key('new-timer-jira-search'))

            <!-- Tags -->
            <div class="ch-form-group">
                <label class="ch-label">Tags</label>
                @livewire('components.tag-selector', [], key('new-timer-tag-selector'))
            </div>

            <!-- Start Button -->
            <div class="ch-modal-footer">
                <button
                    type="button"
                    wire:click="closeNewTimerModal"
                    class="ch-btn-secondary">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="ch-btn-primary flex justify-center items-center">
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