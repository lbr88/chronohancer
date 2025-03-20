<div>
  <!-- Include Modals -->
  @include('livewire.time-logs.modals.edit-form-modal')
  @include('livewire.time-logs.modals.delete-confirmation-modal')
  @include('livewire.time-logs.modals.time-log-selection-modal')
  @include('livewire.time-logs.modals.quick-time-modal')
  @include('livewire.time-logs.modals.manual-time-log-modal')
  @if(config('tempo.enabled') && auth()->user()->hasTempoEnabled())
  @include('livewire.time-logs.modals.tempo-worklog-details-modal')
  @endif

  <!-- Delete Confirmation Modal -->
  @if($confirmingDelete)
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
      <h2 class="text-xl font-semibold mb-4 dark:text-white">Confirm Delete</h2>
      <p class="mb-4 text-gray-700 dark:text-gray-300">Are you sure you want to delete this time log? This action cannot be undone.</p>
      <div class="flex justify-end space-x-3">
        <button wire:click="cancelDelete" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
          Cancel
        </button>
        <button wire:click="deleteTimeLog({{ $confirmingDelete }})" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
          Delete Time Log
        </button>
      </div>
    </div>
  </div>
  @endif

  <!-- Time Log Selection Modal -->
  @if($showTimeLogSelectionModal)
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
      <h2 class="text-xl font-semibold mb-4 dark:text-white">Select Time Log to Edit</h2>
      <p class="mb-4 text-gray-700 dark:text-gray-300">Multiple time logs found for this date and timer. Please select which one you want to edit:</p>

      <div class="space-y-2 max-h-60 overflow-y-auto mb-4">
        @foreach($timeLogSelectionOptions as $option)
        <div wire:click="selectTimeLogToEdit({{ $option['id'] }})" class="p-3 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 cursor-pointer">
          <div class="font-medium dark:text-white">{{ $option['description'] }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400 flex justify-between">
            <span>{{ $option['start_time'] }} - {{ $option['end_time'] }}</span>
            <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $option['duration'] }}</span>
          </div>
        </div>
        @endforeach
      </div>

      <div class="flex justify-end">
        <button wire:click="closeTimeLogSelectionModal" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
          Cancel
        </button>
      </div>
    </div>
  </div>
  @endif

  <!-- Tempo Worklog Details Modal -->
  @if($showTempoWorklogDetailsModal)
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
      <h2 class="text-xl font-semibold mb-4 dark:text-white">Tempo Worklog Details</h2>

      @if($tempoWorklogDetails)
      <div class="space-y-4">
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Issue</h3>
          <p class="text-base font-medium dark:text-white">{{ $tempoWorklogDetails['issue']['key'] }} - {{ $tempoWorklogDetails['issue']['summary'] }}</p>
        </div>

        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Time Spent</h3>
          <p class="text-base font-medium dark:text-white">{{ $this->formatDuration($tempoWorklogDetails['timeSpentSeconds'] / 60) }}</p>
        </div>

        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</h3>
          <p class="text-base font-medium dark:text-white">{{ \Carbon\Carbon::parse($tempoWorklogDetails['startDate'])->format('M d, Y') }}</p>
        </div>

        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
          <p class="text-base font-medium dark:text-white">{{ $tempoWorklogDetails['description'] ?: 'No description' }}</p>
        </div>

        @if(isset($tempoWorklogDetails['attributes']) && count($tempoWorklogDetails['attributes']) > 0)
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Attributes</h3>
          <div class="space-y-2">
            @foreach($tempoWorklogDetails['attributes'] as $attribute)
            <div>
              <span class="text-sm font-medium dark:text-white">{{ $attribute['key'] }}:</span>
              <span class="text-sm dark:text-gray-300">{{ $attribute['value'] }}</span>
            </div>
            @endforeach
          </div>
        </div>
        @endif
      </div>
      @else
      <p class="text-gray-700 dark:text-gray-300">Unable to load Tempo worklog details.</p>
      @endif

      <div class="flex justify-end mt-6">
        <button wire:click="closeTempoWorklogDetailsModal" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
          Close
        </button>
      </div>
    </div>
  </div>
  @endif

  <!-- Edit Form Modal -->
  @if($editingTimeLog)
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
      <h2 class="text-xl font-semibold mb-4 dark:text-white">Edit Time Log</h2>
      <form wire:submit.prevent="updateTimeLog" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Selection</label>
          @livewire('components.unified-timer-selector', [
          'timerId' => $timer_id,
          'projectId' => $project_id
          ], key('edit-time-log-unified-selector'))
          @error('project_id') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="selected_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
          <input type="date" wire:model.live="selected_date" id="selected_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
          @error('selected_date') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
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
          ], key('edit-time-log-duration-input'))
          @error('duration_minutes') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
          @livewire('components.tag-selector', ['selectedTags' => $selectedTags], key('edit-time-log-tag-selector'))
        </div>
        <div class="flex justify-between">
          <button type="button" wire:click="cancelEdit" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
            Cancel
          </button>
          <div class="flex space-x-2">
            <button type="button" wire:click="confirmDelete({{ $editingTimeLog }})" class="px-4 py-2 border border-red-300 text-red-700 dark:border-red-700 dark:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20">
              Delete
            </button>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Update Time Log
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  @endif
</div>