<div>
  <!-- Microsoft Calendar Events (if enabled) -->
  @if(isset($showMicrosoftCalendar) && $showMicrosoftCalendar)
  <livewire:microsoft-calendar-weekly-events :startOfWeek="$startOfWeek" :endOfWeek="$endOfWeek" :key="'weekly_'.$startOfWeek.$endOfWeek" wire:init="$refresh" />
  @endif

  <div class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <button wire:click="$dispatch('previous-week')" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
      </button>
      <h2 class="text-xl font-medium dark:text-white">
        {{ Carbon\Carbon::parse($startOfWeek)->format('M d') }} - {{ Carbon\Carbon::parse($endOfWeek)->format('M d, Y') }}
      </h2>
      <div class="flex items-center space-x-2">
        @php
        $todayDate = now()->format('Y-m-d');
        $remainingMinutes = $this->getRemainingTimeForDate($todayDate);
        @endphp
        <div class="group relative">
          <div class="flex space-x-2">
            <button
              wire:click="openQuickTimeModal"
              class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Quick Time
            </button>

            <button
              wire:click="$dispatch('open-manual-time-log-modal')"
              class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
        <button wire:click="$dispatch('current-week')" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300 {{ $currentWeek->isCurrentWeek() ? 'bg-gray-200 dark:bg-zinc-700' : '' }}">
          Today
        </button>
        <button wire:click="$dispatch('next-week')" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead>
          <tr>
            <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-zinc-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">
              Projects & Tasks
            </th>
            @foreach($weeklyData['weekDays'] as $day)
            <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-zinc-800 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider {{ Carbon\Carbon::parse($day['date'])->isToday() ? 'bg-indigo-50 dark:bg-indigo-900' : '' }}">
              <div class="flex items-center justify-center space-x-1">
                <div>{{ $day['day'] }}</div>
                <button
                  wire:click="openQuickTimeModal('{{ $day['date'] }}')"
                  class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                  title="Add time log for {{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              <div>{{ Carbon\Carbon::parse($day['date'])->format('M d') }}</div>
            </th>
            @endforeach
            <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-zinc-800 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Total
            </th>
          </tr>
          <!-- Totals Row (Moved from bottom to top) -->
          <tr class="bg-indigo-50 dark:bg-indigo-900 font-bold">
            <td class="px-6 py-3 whitespace-nowrap text-right text-sm dark:text-white">
              Total Hours
            </td>
            @foreach($weeklyData['weekDays'] as $day)
            @php
            $dayTotal = 0;
            foreach($weeklyData['projects'] as $project) {
            foreach($project['timers'] as $timer) {
            $dayTotal += $timer['daily'][$day['date']];
            }
            }

            $workspace = app('current.workspace');
            $targetMinutes = $workspace ? $workspace->daily_target_minutes : 0;
            $remainingMinutes = $targetMinutes > 0 ? max(0, $targetMinutes - $dayTotal) : 0;
            $isToday = Carbon\Carbon::parse($day['date'])->isToday();
            $isPast = Carbon\Carbon::parse($day['date'])->isPast();
            $targetMet = $targetMinutes > 0 ? $remainingMinutes == 0 : true;

            // Determine text color class based on requirements
            $textColorClass = '';
            if ($targetMinutes > 0) {
            if ($targetMet) {
            $textColorClass = 'text-green-600';
            } elseif ($isPast && !$isToday) {
            $textColorClass = 'text-red-600';
            }
            }
            @endphp
            <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
              <div class="{{ $textColorClass }}">
                {{ $this->formatDuration($dayTotal) }}
                @if($targetMinutes > 0 && $dayTotal > $targetMinutes)
                <span class="text-green-600 dark:text-green-400">(+{{ $this->formatRemainingTime($dayTotal - $targetMinutes) }})</span>
                @endif
                @if($targetMinutes > 0 && !$targetMet && ($isPast || $isToday))
                <div class="text-xs font-normal {{ $textColorClass }}">
                  Missing: {{ $this->formatRemainingTime($remainingMinutes) }}
                </div>
                @endif
              </div>
            </td>
            @endforeach
            <td class="px-6 py-3 whitespace-nowrap text-center text-sm dark:text-white">
              {{ $this->formatDuration($weeklyData['total']) }}
            </td>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-gray-700">
          @if(count($weeklyData['projects']) > 0)
          @foreach($weeklyData['projects'] as $project)
          <!-- Project Row -->
          <tr class="bg-gray-50 dark:bg-zinc-800 {{ !$project['hasLogs'] ? 'border-t-2 border-indigo-200 dark:border-indigo-800' : '' }}">
            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white flex items-center justify-between">
              <span>
                @if($project['id'] === null)
                @php
                $defaultProject = App\Models\Project::findOrCreateDefault(auth()->id(), app('current.workspace')->id);
                @endphp
                <a href="{{ route('time-logs') }}?view=list&filterProject={{ $defaultProject->id }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                  <span>{{ $defaultProject->name }}</span>
                </a>
                @else
                <a href="{{ route('time-logs') }}?view=list&filterProject={{ $project['id'] }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                  {{ $project['name'] }}
                </a>
                @endif
                @if(!$project['hasLogs'])
                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">(No logs this week)</span>
                @endif
              </span>
              @php
              $todayDate = now()->format('Y-m-d');
              $remainingMinutes = $this->getRemainingTimeForDate($todayDate);
              @endphp
              <div class="group relative">
                <button
                  wire:click="openQuickTimeModal('{{ $todayDate }}', {{ $project['id'] === null ? 'null' : $project['id'] }})"
                  class="text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                  title="Add quick time log for {{ $project['name'] }}">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                </button>
              </div>
            </td>
            @foreach($weeklyData['weekDays'] as $day)
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500"></td>
            @endforeach
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold dark:text-white">
              {{ $this->formatDuration($project['total']) }}
            </td>
          </tr>

          <!-- Timer Rows -->
          @foreach($project['timers'] as $timer)
          <tr class="{{ $timer['total'] == 0 ? 'bg-gray-50/50 dark:bg-zinc-800/50' : '' }}">
            <td class="px-6 py-3 whitespace-nowrap text-sm">
              <div class="flex items-center">
                <div class="ml-4 w-full">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    @if($timer['id'])
                    <a href="{{ route('time-logs') }}?view=list&searchQuery={{ urlencode($timer['originalName']) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                      {{ $timer['originalName'] }}
                    </a>
                    @else
                    <a href="{{ route('time-logs') }}?view=list&searchQuery=Manual Entry" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                      {{ $timer['name'] }}
                    </a>
                    @endif
                    @if($timer['total'] == 0)
                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">(No logs)</span>
                    @endif
                  </div>

                  <!-- List of descriptions -->
                  @if(isset($timer['descriptions']) && count($timer['descriptions']) > 0)
                  <div class="mt-1 ml-4">
                    @foreach($timer['descriptions'] as $descItem)
                    <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">
                      {{ $descItem['description'] }}
                      @if(isset($descItem['count']) && $descItem['count'] > 1)
                      <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $descItem['count'] }} logs, {{ $this->formatDuration($descItem['total_duration']) }})</span>
                      @endif
                    </div>
                    @endforeach
                  </div>
                  @endif

                  @if(count($timer['tags']) > 0)
                  <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($timer['tags'] as $tag)
                    <span class="inline-block px-2 py-0.5 text-xs rounded-full"
                      style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                      {{ $tag->name }}
                    </span>
                    @endforeach
                  </div>
                  @endif
                </div>
              </div>
            </td>
            @foreach($weeklyData['weekDays'] as $day)
            <td class="px-6 py-3 whitespace-nowrap text-center text-sm {{ $timer['daily'][$day['date']] > 0 ? 'bg-indigo-50 dark:bg-indigo-900' : '' }}">
              @if($timer['daily'][$day['date']] > 0)
              <div class="flex items-center justify-center space-x-1">
                <div class="group relative">
                  <button
                    wire:click="findAndEditTimeLog('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }}, '{{ addslashes($timer['dailyDescriptions'][$day['date']]) }}')"
                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium"
                    title="{{ !empty($timer['dailyDescriptions'][$day['date']]) ? 'Descriptions: ' . $timer['dailyDescriptions'][$day['date']] : 'No description' }}">
                    {{ $this->formatDuration($timer['daily'][$day['date']]) }}
                  </button>

                  @if(!empty($timer['dailyDescriptions'][$day['date']]))
                  <div class="absolute z-10 hidden group-hover:block bg-gray-800 dark:bg-black text-white text-xs rounded p-2 mt-1 min-w-[150px] max-w-[300px] whitespace-normal">
                    <div class="font-medium mb-1">Descriptions:</div>
                    @foreach(explode(', ', $timer['dailyDescriptions'][$day['date']]) as $desc)
                    <div class="pl-2">• {{ $desc }}</div>
                    @endforeach
                  </div>
                  @endif
                </div>
                <div class="flex space-x-1">
                  <button
                    wire:click="openQuickTimeModal('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }}, '{{ addslashes($timer['dailyDescriptions'][$day['date']]) }}')"
                    class="text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                    title="Add new time log for {{ $timer['originalName'] }} on {{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </button>

                  <button
                    wire:click="findAndEditTimeLog('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }}, '{{ addslashes($timer['dailyDescriptions'][$day['date']]) }}')"
                    class="text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                    title="Edit time log for {{ $timer['originalName'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  @if(!empty($timer['dailyDescriptions'][$day['date']]))
                  <span class="text-gray-400 dark:text-gray-500" title="Has description: {{ $timer['dailyDescriptions'][$day['date']] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                  </span>
                  @endif
                </div>
              </div>
              @else
              <div class="group relative">
                <button
                  wire:click="openQuickTimeModal('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }})"
                  class="text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 w-full h-full flex items-center justify-center"
                  title="Add quick time log for {{ $timer['originalName'] }} on {{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              @endif
            </td>
            @endforeach
            <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium dark:text-white">
              {{ $this->formatDuration($timer['total']) }}
            </td>
          </tr>
          @endforeach
          @endforeach

          <!-- Grand Total Row moved to the top of the table -->
          @else
          <tr>
            <td colspan="{{ count($weeklyData['weekDays']) + 2 }}" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
              No time logs for this week
            </td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>