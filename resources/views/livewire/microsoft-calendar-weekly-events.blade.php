<div class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6 mb-6">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-medium dark:text-white">{{ __('Microsoft Calendar Events') }}</h2>
    <div class="flex items-center space-x-2">
      @if($loading)
      <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Loading...') }}</span>
      @endif
      <button
        wire:click="refresh"
        class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center"
        @if($loading) disabled @endif>
        @if($loading)
        <svg class="animate-spin h-4 w-4 mr-1 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        @else
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        @endif
        {{ __('Refresh') }}
      </button>
    </div>
  </div>

  @if($error)
  <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 mb-4" role="alert">
    <p>{{ $error }}</p>

    @if(str_contains($error, 'not enabled'))
    <div class="mt-4">
      <a href="{{ route('settings.integrations.microsoft-calendar') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
        {{ __('Configure Microsoft Calendar Integration') }}
      </a>
    </div>
    @endif

    @if(str_contains($error, 'multiple attempts'))
    <div class="mt-2">
      <button
        wire:click="refresh"
        class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
        {{ __('Try Again') }}
      </button>
    </div>
    @endif
  </div>
  @elseif($loading)
  <div class="flex items-center justify-center py-4">
    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span class="ml-2 text-sm text-gray-500">{{ __('Loading calendar events...') }}</span>

    <!-- Add a timeout message after 5 seconds of loading -->
    <div id="calendar-loading-timeout" class="hidden ml-4">
      <button
        wire:click="refresh"
        class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
        {{ __('Taking too long? Try refreshing') }}
      </button>
    </div>

    <script>
      // Show the timeout message after 5 seconds if still loading
      setTimeout(function() {
        const loadingElement = document.getElementById('calendar-loading-timeout');
        if (loadingElement) {
          loadingElement.classList.remove('hidden');
        }
      }, 5000);
    </script>
  </div>
  @else
  @php
  $hasEvents = false;
  foreach ($weekDays as $day) {
  if (count($day['events']) > 0) {
  $hasEvents = true;
  break;
  }
  }
  @endphp

  @if(!$hasEvents)
  <div class="py-4 text-center text-gray-500 dark:text-gray-400">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
    <p>{{ __('No calendar events found for this week.') }}</p>
  </div>
  @else
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead>
        <tr>
          @foreach($weekDays as $day)
          <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-zinc-800 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider {{ Carbon\Carbon::parse($day['date'])->isToday() ? 'bg-indigo-50 dark:bg-indigo-900' : '' }}">
            <div>{{ $day['day'] }}</div>
            <div>{{ Carbon\Carbon::parse($day['date'])->format('M d') }}</div>
          </th>
          @endforeach
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-gray-700">
        <tr>
          @foreach($weekDays as $day)
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 align-top">
            @if(count($day['events']) > 0)
            <div class="space-y-3">
              @foreach($day['events'] as $event)
              <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-md">
                <div class="font-medium text-gray-900 dark:text-white">
                  {{ $event['subject'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ $event['start'] }} - {{ $event['end'] }}
                  @if($event['location'])
                  <div class="mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $event['location'] }}
                  </div>
                  @endif
                </div>
                <div class="mt-2 flex justify-end">
                  <button
                    wire:click="createTimeLogFromEvent('{{ $day['date'] }}', '{{ addslashes($event['subject']) }}', {{ $event['duration_minutes'] }}, '{{ $event['id'] }}')"
                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900 dark:text-indigo-300 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ __('Log Time') }}
                  </button>
                </div>
              </div>
              @endforeach
            </div>
            @endif
          </td>
          @endforeach
        </tr>
      </tbody>
    </table>
  </div>
  @endif
  @endif
</div>