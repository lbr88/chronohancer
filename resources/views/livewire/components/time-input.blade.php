<div>
  @if($label)
  <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</label>
  @endif

  <div class="space-y-3">
    <!-- Input field with live preview -->
    <div class="flex items-center space-x-2">
      <div class="relative flex-grow">
        <input
          type="text"
          id="{{ $inputId }}"
          wire:model.live="value"
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2 {{ $class }}"
          placeholder="{{ $placeholder }}">
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
          <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $formattedValue }}
          </span>
        </div>
      </div>

      @if($showIncrementButtons)
      <div class="flex space-x-1">
        <button
          type="button"
          wire:click="addTime(5)"
          class="px-2 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800"
          title="Add 5 minutes">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
        </button>
        <button
          type="button"
          wire:click="addTime(-5)"
          class="px-2 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800"
          title="Subtract 5 minutes">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
          </svg>
        </button>
      </div>
      @endif
    </div>

    @if($showPresets)
    <!-- Preset buttons -->
    <div class="grid grid-cols-4 gap-2">
      <button type="button" wire:click="addTime(5)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
        +5m
      </button>
      <button type="button" wire:click="addTime(15)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
        +15m
      </button>
      <button type="button" wire:click="addTime(30)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
        +30m
      </button>
      <button type="button" wire:click="addTime(60)" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800">
        +1h
      </button>
      <button type="button" wire:click="setTime(30)" class="px-3 py-2 bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-300 dark:hover:bg-indigo-700">
        30m
      </button>
      <button type="button" wire:click="setTime(60)" class="px-3 py-2 bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-300 dark:hover:bg-indigo-700">
        1h
      </button>
      <button type="button" wire:click="setTime(120)" class="px-3 py-2 bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-300 dark:hover:bg-indigo-700">
        2h
      </button>
      <button type="button" wire:click="setTime(444)" class="px-3 py-2 bg-indigo-300 dark:bg-indigo-700 text-indigo-800 dark:text-indigo-200 rounded-md hover:bg-indigo-400 dark:hover:bg-indigo-600 font-medium">
        7h 24m
      </button>
    </div>
    @endif

    @if($helpText)
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $helpText }}</p>
    @else
    <p class="text-xs text-gray-500 dark:text-gray-400">Enter time in minutes, HH:MM, or format like "3h5m"</p>
    @endif

    @if($error)
    <span class="text-red-500 dark:text-red-400 text-xs">{{ $error }}</span>
    @endif
  </div>

  <!-- JavaScript for autocompletion -->
  <script>
    document.addEventListener('livewire:initialized', function() {
      const inputElement = document.getElementById('{{ $inputId }}');
      if (inputElement) {
        inputElement.addEventListener('input', function(e) {
          const value = e.target.value;
          const livewireComponent = window.Livewire.find(
            document.getElementById('{{ $inputId }}').closest('[wire\\:id]').getAttribute('wire:id')
          );

          // Auto-complete for HH:MM format
          if (/^\d+$/.test(value) && value.length >= 2) {
            // If user types 2 or more digits, suggest HH:MM format
            if (value.length === 2 && parseInt(value) <= 23) {
              e.target.value = value + ':';
              livewireComponent.set('value', value + ':');
            }
          }

          // Auto-complete for Xh format
          if (/^\d+h$/.test(value)) {
            // If user types Xh, suggest Xh Ym format
            e.target.value = value + ' ';
            livewireComponent.set('value', value + ' ');
          }

          // Auto-complete for Xh Y format (add 'm' after a space and a digit)
          if (/^\d+h \d+$/.test(value)) {
            e.target.value = value + 'm';
            livewireComponent.set('value', value + 'm');
          }
        });
      }
    });
  </script>
</div>