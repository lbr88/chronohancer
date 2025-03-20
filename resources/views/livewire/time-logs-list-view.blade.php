<div>
  <div class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6">
    <div class="mb-4">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h2 class="text-xl font-semibold dark:text-white">
            Time Logs for {{ $currentWeek->isCurrentWeek() ? 'This Week' : 'Selected Week' }}
          </h2>
          <div class="flex items-center mt-2">
            <button wire:click="$dispatch('previous-week')" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
            <h2 class="text-sm font-medium dark:text-white mx-2">
              {{ Carbon\Carbon::parse($startOfWeek)->format('M d') }} - {{ Carbon\Carbon::parse($endOfWeek)->format('M d, Y') }}
            </h2>
            <button wire:click="$dispatch('current-week')" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300 {{ $currentWeek->isCurrentWeek() ? 'bg-gray-200 dark:bg-zinc-700' : '' }} mx-1">
              Today
            </button>
            <button wire:click="$dispatch('next-week')" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
          <div class="text-sm text-indigo-600 dark:text-indigo-400 mt-1">
            <span class="font-medium">
              @if($filterProject || $filterTag || $filterDateFrom || $filterDateTo || $searchQuery)
              Filtered:
              @else
              {{ $currentWeek->isCurrentWeek() ? 'This week:' : 'Selected week:' }}
              @endif
            </span>
            {{ count($timeLogs) }} entries | Total: {{ $this->formatDuration($totalFilteredDuration) }}
          </div>
        </div>
        <div class="flex items-center space-x-3">
          @php
          $todayDate = now()->format('Y-m-d');
          $remainingMinutes = $this->getRemainingTimeForDate($todayDate);
          @endphp
          <div class="group relative">
            <div class="flex space-x-2">
              <button
                wire:click="$dispatch('open-quick-time-modal')"
                class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Quick Time
              </button>

              <button
                wire:click="$dispatch('open-manual-time-log-modal')"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Manual Log
              </button>
            </div>

            @if($remainingMinutes > 0)
            <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[180px] text-xs text-left">
              @php
              $workspace = app('current.workspace');
              $dailyTarget = $workspace ? $workspace->daily_target_minutes : 0;
              $targetHours = floor($dailyTarget / 60);
              $targetMinutes = $dailyTarget % 60;
              $targetDisplay = $targetHours . 'h' . ($targetMinutes > 0 ? ' ' . $targetMinutes . 'm' : '');
              @endphp
              @if($dailyTarget > 0)
              <div class="{{ $remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400' }} font-medium">
                Missing to reach {{ $targetDisplay }} today: {{ $this->formatRemainingTime($remainingMinutes) }}
              </div>
              @else
              <div class="text-gray-500 dark:text-gray-400 font-medium">
                No daily target set
              </div>
              @endif
            </div>
            @else
            <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[180px] text-xs text-left">
              @php
              $workspace = app('current.workspace');
              $dailyTarget = $workspace ? $workspace->daily_target_minutes : 0;
              $targetHours = floor($dailyTarget / 60);
              $targetMinutes = $dailyTarget % 60;
              $targetDisplay = $targetHours . 'h' . ($targetMinutes > 0 ? ' ' . $targetMinutes . 'm' : '');
              @endphp
              @if($dailyTarget > 0)
              <div class="text-green-500 dark:text-green-400 font-medium">
                {{ $targetDisplay }} target reached for today!
              </div>
              @else
              <div class="text-gray-500 dark:text-gray-400 font-medium">
                No daily target set
              </div>
              @endif
            </div>
            @endif
          </div>
          @if(count($selectedTimeLogs) > 0)
          <button
            wire:click="confirmBulkDelete"
            class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete Selected ({{ count($selectedTimeLogs) }})
          </button>
          @endif
          <div class="text-sm text-gray-500">
            {{ count($timeLogs) }} entries
          </div>
        </div>
      </div>

      <!-- Search Input -->
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
        <input
          type="text"
          wire:model.live.debounce.300ms="searchQuery"
          class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md leading-5 bg-white dark:bg-zinc-800 placeholder-gray-500 dark:placeholder-gray-400 dark:text-white focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          placeholder="Search time logs by description or project...">
      </div>
    </div>

    <!-- Sortable Headers -->
    <div class="grid grid-cols-12 gap-4 mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
      <div class="col-span-1 flex items-center">
        <label class="inline-flex items-center">
          <input type="checkbox" wire:click="toggleSelectAll" {{ $selectAll ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </label>
      </div>
      <div class="col-span-2 flex items-center cursor-pointer" wire:click="sortBy('start_time')">
        DATE
        @if($sortField === 'start_time')
        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
          @if($sortDirection === 'asc')
          <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          @else
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          @endif
        </svg>
        @endif
      </div>
      <div class="col-span-3 flex items-center cursor-pointer" wire:click="sortBy('project')">
        PROJECT
        @if($sortField === 'project')
        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
          @if($sortDirection === 'asc')
          <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          @else
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          @endif
        </svg>
        @endif
      </div>
      <div class="col-span-3">DESCRIPTION</div>
      <div class="col-span-2 flex items-center cursor-pointer" wire:click="sortBy('duration')">
        TIME
        @if($sortField === 'duration')
        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
          @if($sortDirection === 'asc')
          <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          @else
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          @endif
        </svg>
        @endif
      </div>
      <div class="col-span-1 text-right">ACTIONS</div>
    </div>

    <div class="space-y-3">
      @forelse($timeLogs as $timeLog)
      <div class="grid grid-cols-12 gap-4 py-3 border-b dark:border-gray-700 items-center hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors {{ in_array($timeLog->id, $selectedTimeLogs) ? 'bg-indigo-50 dark:bg-indigo-900' : '' }}">
        <div class="col-span-1 flex items-center">
          <label class="inline-flex items-center">
            <input type="checkbox" value="{{ $timeLog->id }}" wire:model.live="selectedTimeLogs" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </label>
        </div>
        <div class="col-span-2">
          <div class="text-sm font-medium text-gray-900 dark:text-white">
            {{ $timeLog->start_time->format('M d, Y') }}
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ $timeLog->start_time ? $timeLog->start_time->format('H:i') : '?' }} - {{ $timeLog->end_time ? $timeLog->end_time->format('H:i') : '?' }}
          </div>
        </div>

        <div class="col-span-3">
          <div class="text-sm font-medium text-gray-900 dark:text-white">
            @if($timeLog->timer && $timeLog->timer->project_id && isset($timeLog->timer->project) && $timeLog->timer->project->trashed())
            <span class="line-through text-gray-500 dark:text-gray-400">{{ $timeLog->timer->project->name }}</span>
            <span class="text-xs text-red-500 dark:text-red-400">(deleted)</span>
            @elseif($timeLog->timer && $timeLog->timer->project_id && isset($timeLog->timer->project))
            <a href="{{ route('time-logs') }}?view=list&filterProject={{ $timeLog->timer->project_id }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
              {{ $timeLog->timer->project->name }}
            </a>
            @else
            @php
            $defaultProject = App\Models\Project::findOrCreateDefault(auth()->id(), app('current.workspace')->id);
            @endphp
            <a href="{{ route('time-logs') }}?view=list&filterProject={{ $defaultProject->id }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
              {{ $defaultProject->name }}
            </a>
            @endif
          </div>
          @if($timeLog->timer)
          <div class="text-xs text-gray-500 dark:text-gray-400">
            Timer:
            <a href="{{ route('time-logs') }}?view=list&searchQuery={{ urlencode($timeLog->timer->name) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
              {{ $timeLog->timer->name }}
            </a>
          </div>
          @else
          <div class="text-xs text-gray-500 dark:text-gray-400">
            <a href="{{ route('time-logs') }}?view=list&searchQuery=Manual Entry" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
              Manual Entry
            </a>
          </div>
          @endif
        </div>

        <div class="col-span-3">
          <div class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
            {{ $timeLog->description ?: 'No description' }}
          </div>
          @if($timeLog->microsoft_event_id)
          <div class="mt-1">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Calendar Event
            </span>
          </div>
          @endif
          @if($timeLog->tags->count() > 0)
          <div class="flex flex-wrap gap-1 mt-1">
            @foreach($timeLog->tags as $tag)
            <span class="px-2.5 py-1 text-xs rounded-full"
              style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
              {{ $tag->name }}
            </span>
            @endforeach
          </div>
          @endif
        </div>

        <div class="col-span-2">
          <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getDurationClass($timeLog->duration_minutes) }}">
              {{ $this->formatDuration($timeLog->duration_minutes) }}
            </span>

            @if(config('tempo.enabled') && auth()->user()->hasTempoEnabled() && $timeLog->isSyncedToTempo())
            <button
              wire:click="$dispatch('view-tempo-worklog-details', { timeLogId: {{ $timeLog->id }} })"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 cursor-pointer"
              title="Synced to Tempo at {{ $timeLog->synced_to_tempo_at ? $timeLog->synced_to_tempo_at->format('M d, Y H:i') : 'Unknown' }}. Click to view details.">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
              </svg>
              Tempo
            </button>
            @endif
          </div>
        </div>

        <div class="col-span-1 text-right">
          <div class="flex justify-end space-x-2">
            <button wire:click="$dispatch('open-edit-modal', { timeLogId: {{ $timeLog->id }} })" class="text-indigo-600 hover:text-indigo-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button wire:click="$dispatch('confirm-delete', { timeLogId: {{ $timeLog->id }} })" class="text-red-600 hover:text-red-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
      @empty
      <div class="py-8 text-center text-gray-500 dark:text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p>No time logs found matching your criteria.</p>
        @if($filterProject || $filterTag || $filterDateFrom || $filterDateTo || $searchQuery)
        <button wire:click="$dispatch('reset-filters')" class="mt-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
          {{ $currentWeek->isCurrentWeek() ? 'Reset to current week' : 'Reset to selected week' }}
        </button>
        @else
        <p class="mt-2 text-sm">Start tracking time to see logs here!</p>
        @endif
      </div>
      @endforelse
    </div>
  </div>

  <!-- Bulk Delete Confirmation Modal -->
  @if($confirmingBulkDelete)
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
      <h2 class="text-xl font-semibold mb-4 dark:text-white">Confirm Bulk Delete</h2>
      <p class="mb-4 text-gray-700 dark:text-gray-300">Are you sure you want to delete {{ count($selectedTimeLogs) }} time logs? This action cannot be undone.</p>
      <div class="flex justify-end space-x-3">
        <button wire:click="cancelBulkDelete" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
          Cancel
        </button>
        <button wire:click="bulkDeleteTimeLogs" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
          Delete Time Logs
        </button>
      </div>
    </div>
  </div>
  @endif
</div>