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

  <!-- Edit Form Modal is included from edit-form-modal.blade.php -->
</div>