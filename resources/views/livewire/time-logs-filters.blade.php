<div>
  <!-- Time Format Selector -->
  <div class="inline-flex rounded-md shadow-sm" role="group">
    <button wire:click="setTimeFormat('human')" type="button" class="px-3 py-1 text-xs font-medium {{ $timeFormat === 'human' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-l-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
      3h 40m
    </button>
    <button wire:click="setTimeFormat('hm')" type="button" class="px-3 py-1 text-xs font-medium {{ $timeFormat === 'hm' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700">
      HH:MM
    </button>
    <button wire:click="setTimeFormat('hms')" type="button" class="px-3 py-1 text-xs font-medium {{ $timeFormat === 'hms' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-r-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
      HH:MM:SS
    </button>
  </div>

  <!-- Filter Toggle Button -->
  <button wire:click="toggleFilters" class="px-3 py-1 text-xs font-medium border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-300 flex items-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
    </svg>
    Filters
    @if($filterProject || $filterTag || $filterDateFrom || $filterDateTo || $searchQuery)
    <span class="ml-1 bg-indigo-600 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">!</span>
    @endif
  </button>

  <!-- Filters Panel -->
  @if($showFilters)
  <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg mb-6 border border-gray-200 dark:border-gray-700 mt-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label for="filterSearchQuery" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
        <input type="text" wire:model.live.debounce.300ms="searchQuery" id="filterSearchQuery"
          class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2"
          placeholder="Search description or project...">
      </div>

      <div>
        <label for="filterProject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project</label>
        <select wire:model.live="filterProject" id="filterProject" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2">
          <option value="">All Projects</option>
          @foreach($projects as $project)
          <option value="{{ $project->id }}">{{ $project->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="filterTag" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tag</label>
        <select wire:model.live="filterTag" id="filterTag" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2">
          <option value="">All Tags</option>
          @foreach($tags as $tag)
          <option value="{{ $tag->id }}">{{ $tag->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div>
          <label for="filterDateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
          <input type="date" wire:model.live="filterDateFrom" id="filterDateFrom" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2">
        </div>
        <div>
          <label for="filterDateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
          <input type="date" wire:model.live="filterDateTo" id="filterDateTo" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2">
        </div>
      </div>
    </div>

    <div class="mt-4 flex justify-end">
      <button wire:click="resetFilters" class="px-3 py-1 text-sm font-medium border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-300">
        Reset Filters
      </button>
    </div>
  </div>
  @endif
</div>