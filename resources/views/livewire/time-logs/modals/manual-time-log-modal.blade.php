@if($editingTimeLog || $showManualTimeLogModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
  <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
    <h2 class="text-xl font-semibold mb-4 dark:text-white">
      @if($editingTimeLog)
      Edit Time Log
      @else
      Create Manual Time Log
      @if($selected_date && is_string($selected_date) && $selected_date != now()->format('Y-m-d'))
      <span class="text-sm font-normal text-indigo-600 dark:text-indigo-400">
        for {{ Carbon\Carbon::parse($selected_date)->format('M d, Y') }}
      </span>
      @endif
      @endif
    </h2>
    <form wire:submit.prevent="{{ $editingTimeLog ? 'updateTimeLog' : 'save' }}" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Selection</label>
        @livewire('components.unified-timer-selector', [
        'timerId' => $timer_id
        ], key('manual-time-log-unified-selector'))
      </div>
      <div>
        <label for="selected_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
        <input type="date" wire:model.live="selected_date" id="selected_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
        @error('selected_date') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror

        @if($selected_date && is_string($selected_date))
        @php
        $remainingMinutes = $this->getRemainingTimeForDate($selected_date);
        @endphp
        @php
        $workspace = app('current.workspace');
        $dailyTarget = $workspace ? $workspace->daily_target_minutes : 0;
        $targetHours = floor($dailyTarget / 60);
        $targetMinutes = $dailyTarget % 60;
        $targetDisplay = $targetHours . 'h' . ($targetMinutes > 0 ? ' ' . $targetMinutes . 'm' : '');
        @endphp
        @if($dailyTarget > 0)
        <div class="mt-1 text-xs {{ $remainingMinutes > 0 ? ($remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400') : 'text-green-500 dark:text-green-400' }}">
          @if($remainingMinutes > 0)
          <span class="font-medium">Missing to reach {{ $targetDisplay }}:</span> {{ $this->formatRemainingTime($remainingMinutes) }}
          @else
          <span class="font-medium">{{ $targetDisplay }} target reached for this day!</span>
          @endif
        </div>
        @else
        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          <span class="font-medium">No daily target set</span>
        </div>
        @endif
        @endif
      </div>
      <div>
        @livewire('components.time-input', [
        'value' => $duration_minutes,
        'name' => 'duration_minutes',
        'inputId' => 'duration_minutes',
        'label' => 'Duration',
        'showPresets' => true,
        'showIncrementButtons' => true,
        'helpText' => 'Enter duration in minutes, HH:MM, or format like "3h5m"',
        ], key('manual-time-log-duration-input'))
        @error('duration_minutes') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
        @livewire('components.tag-selector', ['selectedTags' => $selectedTags], key('manual-time-log-tag-selector'))
      </div>
      <div class="flex justify-between">
        <button type="button" wire:click="{{ $editingTimeLog ? 'cancelEdit' : 'closeManualTimeLogModal' }}" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
          Cancel
        </button>
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          {{ $editingTimeLog ? 'Update Time Log' : 'Create Time Log' }}
        </button>
      </div>
    </form>
  </div>
</div>
@endif