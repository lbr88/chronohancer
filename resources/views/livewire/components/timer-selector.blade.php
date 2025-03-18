<div class="relative" x-data="{ open: @entangle('showDropdown') }" @click.away="$wire.closeDropdown()">
  <div class="relative">
    <input
      type="text"
      wire:model.live="search"
      wire:click="toggleDropdown"
      placeholder="Select or create a timer"
      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
  </div>

  @if($showDropdown)
  <div class="absolute z-10 mt-1 w-full bg-white dark:bg-zinc-800 shadow-lg rounded-md py-1 max-h-60 overflow-auto">
    @if($timers->isEmpty())
    <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
      No timers found
    </div>
    @else
    @foreach($timers as $timer)
    <div
      wire:key="timer-{{ $timer->id }}"
      wire:click="selectTimer({{ $timer->id }}, '{{ addslashes($timer->name) }}')"
      class="px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer">
      <div class="font-medium text-gray-900 dark:text-white">
        {{ $timer->name }}
      </div>
      <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
        @if($timer->project)
        <span class="mr-2 px-2 py-0.5 rounded-full text-xs"
          style="background-color: {{ $timer->project->color ?? '#6B7280' }}; color: {{ $timer->project->color ? '#000000' : '#FFFFFF' }}">
          {{ $timer->project->name }}
        </span>
        @endif
        @if($timer->latestDescription)
        <span class="truncate max-w-xs">{{ \Illuminate\Support\Str::limit($timer->latestDescription->description, 50) }}</span>
        @endif
      </div>
    </div>
    @endforeach
    @endif

    @if($createNewTimer)
    <div
      wire:click="showCreateTimerForm"
      class="px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer border-t border-gray-200 dark:border-gray-700">
      <div class="font-medium text-indigo-600 dark:text-indigo-400">
        Create "{{ $search }}"
      </div>
    </div>
    @endif
  </div>
  @endif

  @if($showNewTimerForm)
  <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-xl p-6 max-w-md w-full">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Create New Timer</h3>

      <div class="mb-4">
        <label for="timerName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Name</label>
        <input type="text" id="timerName" wire:model="timerName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
      </div>

      <div class="mb-4">
        <label for="projectSelector" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
        <livewire:components.project-selector :key="'project-selector-' . uniqid()" />
      </div>

      <div class="flex justify-end space-x-3 mt-6">
        <button wire:click="cancelCreateTimer" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700">
          Cancel
        </button>
        <button wire:click="createTimer" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
          Create Timer
        </button>
      </div>
    </div>
  </div>
  @endif

  <input type="hidden" name="timer_id" wire:model="timerId">
</div>