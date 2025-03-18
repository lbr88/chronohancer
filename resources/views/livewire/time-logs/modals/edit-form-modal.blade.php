@if($editingTimeLog)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Edit Time Log</h2>
        <form wire:submit.prevent="updateTimeLog" class="space-y-4">
            <div>
                <label for="edit_project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                @livewire('components.project-selector', ['projectId' => $project_id], key('edit-form-project-selector'))
                @error('project_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                <label for="edit_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                <div class="flex items-center space-x-2">
                    <input type="text" wire:model="duration_minutes" id="edit_duration_minutes" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
                    @if($duration_minutes)
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        ({{ $this->formatDuration($this->parseDurationString($duration_minutes)) }})
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter duration in minutes or format like "3h5m"</p>
                @error('duration_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                    <label
                        class="inline-flex items-center px-3 py-1 rounded-full cursor-pointer 
                                {{ in_array($tag->id, $selectedTags) ? 'bg-opacity-100' : 'bg-opacity-30' }}"
                        style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                        <input
                            type="checkbox"
                            wire:model="selectedTags"
                            value="{{ $tag->id }}"
                            class="form-checkbox h-4 w-4 mr-1 opacity-0 absolute">
                        <span>{{ $tag->name }}</span>
                    </label>
                    @endforeach
                </div>
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