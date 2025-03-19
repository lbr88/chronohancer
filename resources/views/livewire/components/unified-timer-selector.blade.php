<div>
  <!-- Timer Name -->
  <div class="mb-4">
    <label for="timer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timer Name</label>
    <div class="relative">
      <input
        type="text"
        id="timer_name"
        wire:model.live.debounce.300ms="timerName"
        required
        placeholder="What are you working on?"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">

      <!-- Search Existing Timers -->
      <div class="mt-2">
        <div class="relative">
          <input
            type="text"
            wire:model.live="search"
            wire:focus="focusSearch"
            wire:blur="blurSearch"
            placeholder="Search existing timers..."
            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2 text-sm">
          <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        @if(count($existingTimers) > 0)
        <div class="mt-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm max-h-60 overflow-y-auto">
          <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($existingTimers as $timer)
            <li wire:click="useExistingTimer({{ $timer['id'] }})" class="p-3 hover:bg-gray-50 dark:hover:bg-zinc-700 cursor-pointer">
              <div class="flex items-center">
                <span class="block font-medium text-gray-900 dark:text-white">{{ $timer['name'] }}</span>
              </div>
              <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                @if(isset($timer['project']) && $timer['project'])
                <span class="mr-2">{{ $timer['project']['name'] }}</span>
                @endif
                @if(isset($timer['tags']) && count($timer['tags']) > 0)
                <div class="flex flex-wrap gap-1">
                  @foreach(array_slice($timer['tags'], 0, 3) as $tag)
                  <span class="inline-block px-2 py-0.5 text-xs rounded-full"
                    style="background-color: {{ $tag['color'] }}; color: #FFFFFF">
                    {{ $tag['name'] }}
                  </span>
                  @endforeach
                  @if(count($timer['tags']) > 3)
                  <span class="text-xs text-gray-500 dark:text-gray-400">+{{ count($timer['tags']) - 3 }} more</span>
                  @endif
                </div>
                @endif
              </div>
            </li>
            @endforeach
          </ul>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Project Selection (only shown when showProjectSelector is true) -->
  @if($showProjectSelector)
  <div class="mb-4">
    <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
    @livewire('components.project-selector', ['projectId' => $projectId], key('unified-timer-project-selector-' . uniqid()))
  </div>
  @endif

  <!-- Description -->
  <div class="mb-4">
    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
    @livewire('components.timer-description-selector', ['timerId' => $timerId, 'timerDescriptionId' => $timerDescriptionId], key('unified-timer-description-selector-' . uniqid()))
  </div>
</div>