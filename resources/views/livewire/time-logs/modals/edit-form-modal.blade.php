@if($editingTimeLog)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Edit Time Log</h2>
        <form wire:submit.prevent="updateTimeLog" class="space-y-4">
            <div>
                <label for="edit_timer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer</label>
                <div class="mt-1 p-2 border rounded-md border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-zinc-800 text-gray-800 dark:text-gray-300">
                    @php
                    $timerName = "No Timer";
                    if ($editingTimeLog) {
                    $timeLog = \App\Models\TimeLog::find($editingTimeLog);
                    if ($timeLog && $timeLog->timer) {
                    $timerName = $timeLog->timer->name;
                    }
                    }
                    @endphp
                    {{ $timerName }}
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Timer cannot be changed when editing a time log. To change both timer and project, create a new time log instead.
                    </div>
                </div>
            </div>
            <div>
                <label for="edit_project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                <div class="mt-1 p-2 border rounded-md border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-zinc-800 text-gray-800 dark:text-gray-300">
                    @php
                    $projectName = "No Project";
                    if ($editingTimeLog) {
                    $timeLog = \App\Models\TimeLog::find($editingTimeLog);
                    if ($timeLog && $timeLog->timer && $timeLog->timer->project) {
                    $projectName = $timeLog->timer->project->name;
                    } else {
                    $defaultProject = \App\Models\Project::findOrCreateDefault(auth()->id(), app('current.workspace')->id);
                    $projectName = $defaultProject->name;
                    }
                    }
                    @endphp
                    {{ $projectName }}
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Project is associated with the timer and cannot be changed here
                    </div>
                </div>
            </div>
            <div>
                <label for="edit_selected_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                <input type="date" wire:model.live="selected_date" id="edit_selected_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
                @error('selected_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                @if($selected_date)
                @php
                $remainingMinutes = $this->getRemainingTimeForDate($selected_date);
                @endphp
                <div class="mt-1 text-xs {{ $remainingMinutes > 0 ? ($remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400') : 'text-green-500 dark:text-green-400' }}">
                    @if($remainingMinutes > 0)
                    <span class="font-medium">Missing to reach 7h 24m:</span> {{ $this->formatRemainingTime($remainingMinutes) }}
                    @else
                    <span class="font-medium">7h 24m target reached for this day!</span>
                    @endif
                </div>
                @endif
            </div>
            <div>
                <label for="edit_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (optional)</label>
                <textarea wire:model="description" id="edit_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2"></textarea>
            </div>
            <div>
                @livewire('components.time-input', [
                'value' => $duration_minutes,
                'name' => 'duration_minutes',
                'inputId' => 'edit_duration_minutes',
                'label' => 'Duration',
                'showPresets' => true,
                'showIncrementButtons' => true,
                'helpText' => 'Enter duration in minutes, HH:MM, or format like "3h5m"',
                ], key('edit-form-duration-input'))
                @error('duration_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                @livewire('components.tag-selector', ['selectedTags' => $selectedTags], key('edit-form-tag-selector'))
            </div>
            <div class="flex justify-between">
                <button type="button" wire:click="confirmDelete({{ $editingTimeLog }})" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Delete
                </button>
                <div class="flex space-x-3">
                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Update Time Log
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif