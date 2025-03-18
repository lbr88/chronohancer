<div class="w-full">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Calendar Events') }}</h2>
    <button wire:click="loadEvents" class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
      {{ __('Refresh') }}
    </button>
  </div>

  @if($loading)
  <div class="flex justify-center py-6">
    <svg class="h-6 w-6 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
  </div>
  @elseif($error)
  <div class="rounded-md bg-red-50 p-4 dark:bg-red-900/50">
    <div class="flex">
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
      </div>
      <div class="ml-3">
        <p class="text-sm text-red-800 dark:text-red-200">{{ $error }}</p>
      </div>
    </div>
  </div>
  @elseif(empty($events))
  <div class="rounded-md bg-blue-50 p-4 dark:bg-blue-900/50">
    <div class="flex">
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </div>
      <div class="ml-3">
        <p class="text-sm text-blue-800 dark:text-blue-200">{{ __('No events found in your calendar.') }}</p>
      </div>
    </div>
  </div>
  @else
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($events as $event)
    <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
      <div class="flex flex-col h-full">
        <!-- Event Header -->
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-base font-medium text-gray-900 dark:text-white truncate" title="{{ $event['subject'] }}">{{ $event['subject'] }}</h3>
          <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $event['status'] === 'busy' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' }}">
            {{ ucfirst($event['status'] ?: 'free') }}
          </span>
        </div>

        <!-- Event Time -->
        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-1">
          <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
          </svg>
          @if($event['isAllDay'])
          <span>{{ __('All day') }}</span>
          @else
          <span>{{ $event['start']->format('M j, Y g:i A') }} - {{ $event['end']->format('g:i A') }}</span>
          @endif
        </div>

        <!-- Event Details -->
        <div class="flex-grow">
          @if($event['location'])
          <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-1">
            <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
            </svg>
            <span class="truncate" title="{{ $event['location'] }}">{{ $event['location'] }}</span>
          </div>
          @endif

          @if($event['organizer'])
          <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-1">
            <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
            <span class="truncate" title="{{ $event['organizer'] }}">{{ $event['organizer'] }}</span>
          </div>
          @endif
        </div>

        <!-- Log Time Button -->
        <div class="mt-3 flex justify-end">
          <button
            wire:click="createTimeLogFromEvent('{{ $event['id'] }}', '{{ addslashes($event['subject']) }}', {{ $event['duration_minutes'] }})"
            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900 dark:text-indigo-300 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ __('Log Time') }}
          </button>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.dispatch('load-events');
    });
  </script>
</div>