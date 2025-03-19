@if($showRestartTimerModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
  <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
    <h2 class="text-xl font-semibold mb-4 dark:text-white">Restart Timer</h2>

    <form wire:submit.prevent="confirmRestartTimer" class="space-y-4">
      <div>
        <label for="timer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Name</label>
        <div class="mt-1 text-gray-900 dark:text-white font-medium">{{ $restartTimerName }}</div>
      </div>

      <div>
        <label for="timer_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        @livewire('components.timer-description-selector', [
          'timerId' => $restartTimerId,
          'timerDescriptionId' => $restartTimerDescriptionId
        ], key('restart-timer-description-selector'))
        
        <script>
          document.addEventListener('description-selected', (event) => {
            // Handle timer description selection events
            if (event.detail && event.detail.description) {
              // Update the parent component's description
              Livewire.dispatch('description-selected', event.detail);
            }
          });
        </script>
      </div>

      <div class="flex justify-between">
        <button type="button" wire:click="closeRestartTimerModal" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
          Cancel
        </button>
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Start Timer
        </button>
      </div>
    </form>
  </div>
</div>
@endif