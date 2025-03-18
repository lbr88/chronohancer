<section class="w-full">
  @include('partials.settings-heading')

  @if (session('error'))
  <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/50 mb-6">
    <div class="flex">
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
      </div>
      <div class="ml-3">
        <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
      </div>
    </div>
  </div>
  @endif

  @if (session('success') || session('status'))
  <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/50 mb-6">
    <div class="flex">
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
      </div>
      <div class="ml-3">
        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') ?? session('status') }}</p>
      </div>
    </div>
  </div>
  @endif

  <x-settings.navigation />

  <x-settings.layout :heading="__('Microsoft Calendar Integration')" :subheading="__('Connect your Microsoft 365 account to access your calendar')">
    <div class="my-6 w-full space-y-6">
      @if($isConnected)
      <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/50">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Connected to Microsoft Calendar</h3>
            <div class="mt-2 text-sm text-green-700 dark:text-green-300">
              <p>Your Microsoft 365 account is connected. You can now access your calendar data.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <label for="microsoftEnabled" class="flex items-center cursor-pointer">
            <div class="relative">
              <input type="checkbox" id="microsoftEnabled" wire:model.live="microsoftEnabled" class="sr-only">
              <div class="block bg-gray-200 dark:bg-gray-700 w-14 h-8 rounded-full"></div>
              <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition {{ $microsoftEnabled ? 'transform translate-x-6' : '' }}"></div>
            </div>
            <div class="ml-3 text-gray-700 dark:text-gray-300 font-medium">
              {{ __('Enable Microsoft Calendar Integration') }}
            </div>
          </label>
        </div>
        <button wire:click="disconnect" type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
          {{ __('Disconnect') }}
        </button>
      </div>
      
      <!-- Calendar Selection -->
      <div class="mt-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Select Calendar') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ __('Choose which calendar you want to use with Chronohancer.') }}
        </p>
        
        @error('calendars')
          <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
        @enderror
        
        <div class="mt-4">
          @if($loadingCalendars)
            <div class="flex items-center justify-center py-4">
              <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="ml-2 text-sm text-gray-500">{{ __('Loading calendars...') }}</span>
            </div>
          @elseif(empty($calendars))
            <div class="py-4 text-sm text-gray-500">
              {{ __('No calendars found.') }}
              <button wire:click="fetchCalendars" class="text-indigo-600 hover:text-indigo-900">{{ __('Refresh') }}</button>
            </div>
          @else
            <div class="space-y-2">
              @foreach($calendars as $calendar)
                <div class="flex items-center p-3 border rounded-md {{ $selectedCalendarId === $calendar['id'] ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                  <div class="flex-1">
                    <div class="font-medium text-gray-900 dark:text-white">{{ $calendar['name'] }}</div>
                  </div>
                  <button
                    wire:click="selectCalendar('{{ $calendar['id'] }}')"
                    class="px-3 py-1 text-xs font-medium rounded-md {{ $selectedCalendarId === $calendar['id'] ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
                  >
                    {{ $selectedCalendarId === $calendar['id'] ? __('Selected') : __('Select') }}
                  </button>
                </div>
              @endforeach
            </div>
            <div class="mt-2 text-right">
              <button wire:click="fetchCalendars" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('Refresh calendars') }}</button>
            </div>
          @endif
        </div>
      </div>

      <div class="mt-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Calendar Access') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ __('Your Microsoft Calendar integration allows Chronohancer to:') }}
        </p>
        <ul class="mt-3 list-disc pl-5 text-sm text-gray-500 dark:text-gray-400 space-y-1">
          <li>{{ __('View your calendar events') }}</li>
        </ul>
      </div>
      @else
      <div class="ch-form-group">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Connect to Microsoft Calendar') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ __('Connect your Microsoft 365 account to access your calendar. This integration allows you to view your calendar events directly from Chronohancer (read-only access).') }}
            </p>
          </div>
          <button wire:click="connect" type="button" class="ch-btn-primary">
            {{ __('Connect') }}
          </button>
        </div>
      </div>
      @endif
    </div>
  </x-settings.layout>
</section>