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
            <div class="bg-gray-100 dark:bg-zinc-800 p-4 rounded-lg text-center mb-4">
                <div class="text-3xl font-mono font-bold text-indigo-600 dark:text-indigo-400">
                    {{ floor($quickTimeDuration / 60) }}:{{ str_pad($quickTimeDuration % 60, 2, '0', STR_PAD_LEFT) }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $this->formatDuration($quickTimeDuration) }}
                </div>

                @php
                $remainingMinutes = $this->getRemainingTimeForDate($quickTimeDate);
                @endphp

                @if($remainingMinutes > 0)
                <div class="mt-2 text-sm {{ $remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400' }}">
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
                <div class="mt-2 text-sm text-green-500 dark:text-green-400">
                    <span class="font-medium">7h 24m target reached for today!</span>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-4 gap-2 mb-4">
                <button type="button" wire:click="addQuickTime(5)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
                    +5m
                </button>
                <button type="button" wire:click="addQuickTime(15)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
                    +15m
                </button>
                <button type="button" wire:click="addQuickTime(30)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
                    +30m
                </button>
                <button type="button" wire:click="addQuickTime(60)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
                    +1h
                </button>
                <button type="button" wire:click="setQuickTime(30)" class="px-3 py-2 bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-300 dark:hover:bg-indigo-700">
                    30m
                </button>
                <button type="button" wire:click="setQuickTime(60)" class="px-3 py-2 bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-300 dark:hover:bg-indigo-700">
                    1h
                </button>
                <button type="button" wire:click="setQuickTime(120)" class="px-3 py-2 bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-300 dark:hover:bg-indigo-700">
                    2h
                </button>
                <button type="button" wire:click="setQuickTime(444)" class="px-3 py-2 bg-indigo-300 dark:bg-indigo-700 text-indigo-800 dark:text-indigo-200 rounded-md hover:bg-indigo-400 dark:hover:bg-indigo-600 font-medium">
                    7h 24m
                </button>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label for="quick_time_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                <input type="date" wire:model="quickTimeDate" id="quick_time_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
            </div>

            <div>
                <label for="quick_time_project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                @livewire('components.project-selector', ['projectId' => $quickTimeProjectId], key('quick-time-project-selector'))
            </div>

            <div>
                <label for="quick_time_timer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer</label>
                <select wire:model="quickTimeTimerId" id="quick_time_timer_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
                    <option value="">Manual Entry</option>
                    @foreach($quickTimeProjectTimers as $timer)
                    <option value="{{ $timer->id }}">{{ $timer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="quick_time_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (optional)</label>
                <textarea wire:model="quickTimeDescription" id="quick_time_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2"></textarea>
            </div>

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