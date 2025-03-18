@if($showQuickTimeModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-semibold mb-2 dark:text-white">Quick Time Entry</h2>
        @if($quickTimeProjectId)
        <div class="text-sm text-indigo-600 dark:text-indigo-400 mb-4">
            @if($quickTimeTimerId)
            @php
            $timerName = 'Manual Entry';
            foreach($quickTimeProjectTimers as $timer) {
            if($timer->id == $quickTimeTimerId) {
            $timerName = $timer->name;
            break;
            }
            }
            @endphp
            For timer: {{ $timerName }}
            @else
            For project: {{ collect($projects)->firstWhere('id', $quickTimeProjectId)->name ?? 'Selected Project' }}
            @endif
        </div>
        @else
        <div class="mb-4"></div>
        @endif

        <div class="mb-6">
            @livewire('components.time-input', [
            'value' => $quickTimeDuration,
            'name' => 'quickTimeDuration',
            'inputId' => 'quick_time_duration',
            'label' => 'Duration',
            'showPresets' => true,
            'showIncrementButtons' => true,
            'helpText' => '',
            ], key('quick-time-duration-input'))

            @php
            $remainingMinutes = $this->getRemainingTimeForDate($quickTimeDate);
            @endphp

            <div class="bg-gray-100 dark:bg-zinc-800 p-3 rounded-lg text-center mt-3">
                @if($remainingMinutes > 0)
                <div class="text-sm {{ $remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400' }}">
                    <span class="font-medium">Missing to reach 7h 24m:</span>
                    {{ $this->formatRemainingTime($remainingMinutes) }}

                    @if($quickTimeDuration > 0 && $quickTimeDuration < $remainingMinutes)
                        <span class="text-gray-500 dark:text-gray-400">
                        ({{ $this->formatRemainingTime($remainingMinutes - $quickTimeDuration) }} after this entry)
                        </span>
                        @elseif($quickTimeDuration >= $remainingMinutes)
                        <span class="text-green-500 dark:text-green-400">
                            (Completed with this entry!)
                        </span>
                        @endif
                </div>
                @else
                <div class="text-sm text-green-500 dark:text-green-400">
                    <span class="font-medium">7h 24m target reached for today!</span>
                </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label for="quick_time_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                <input type="date" wire:model="quickTimeDate" id="quick_time_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Selection</label>
                @livewire('components.unified-timer-selector', [
                'timerId' => $quickTimeTimerId,
                'timerDescriptionId' => $quickTimeTimerDescriptionId,
                'projectId' => $quickTimeProjectId,
                'showProjectSelector' => false
                ], key('quick-time-unified-selector'))
            </div>

            <!-- Project Selection (only shown when no timer is selected) -->
            @if(!$quickTimeTimerId)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                @livewire('components.project-selector', ['projectId' => $quickTimeProjectId], key('quick-time-project-selector'))
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                @livewire('components.tag-selector', ['selectedTags' => $quickTimeSelectedTags], key('quick-time-tag-selector'))
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" wire:click="closeQuickTimeModal" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
                Cancel
            </button>
            <button type="button" wire:click="saveQuickTime" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Save Time Log
            </button>
        </div>
    </div>
</div>
@endif