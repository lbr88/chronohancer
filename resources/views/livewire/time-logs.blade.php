@php
    use App\Models\TimeLog;
@endphp
<div class="timer-page">
    <h1 class="text-2xl font-semibold mb-4 dark:text-white">Time Logs</h1>
    
    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif
    
    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('scroll-to-form', () => {
                const formElement = document.getElementById('time-log-form');
                if (formElement) {
                    formElement.scrollIntoView({ behavior: 'smooth' });
                    // Highlight the form briefly
                    formElement.classList.add('bg-indigo-50');
                    setTimeout(() => {
                        formElement.classList.remove('bg-indigo-50');
                    }, 1500);
                }
            });
        });
    </script>

    <!-- View Switcher and Controls -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
        <div>
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button wire:click="switchView('list')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'list' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-l-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
                    List View
                </button>
                <button wire:click="switchView('weekly')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'weekly' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-r-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
                    Weekly Summary
                </button>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center space-x-2">
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
        </div>
    </div>
    
    <!-- Filters Panel -->
    @if($showFilters)
    <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg mb-6 border border-gray-200 dark:border-gray-700">
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
                    @foreach($allTags as $tag)
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

    @if($view === 'weekly')
        <!-- Weekly Summary View -->
        <div class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <button wire:click="previousWeek" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
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
                        <button
                            wire:click="openQuickTimeModal"
                            class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Quick Time
                        </button>
                        
                        @if($remainingMinutes > 0)
                            <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[180px] text-xs text-left">
                                <div class="{{ $remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400' }} font-medium">
                                    Missing to reach 7h 24m today: {{ $this->formatRemainingTime($remainingMinutes) }}
                                </div>
                            </div>
                        @else
                            <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[180px] text-xs text-left">
                                <div class="text-green-500 dark:text-green-400 font-medium">
                                    7h 24m target reached for today!
                                </div>
                            </div>
                        @endif
                    </div>
                    <button wire:click="currentWeek" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300 {{ $currentWeek->isCurrentWeek() ? 'bg-gray-200 dark:bg-zinc-700' : '' }}">
                        Today
                    </button>
                    <button wire:click="nextWeek" class="px-3 py-1 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
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
                            @foreach($this->weeklyData['weekDays'] as $day)
                                <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-zinc-800 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider {{ Carbon\Carbon::parse($day['date'])->isToday() ? 'bg-indigo-50 dark:bg-indigo-900' : '' }}">
                                    <div class="flex items-center justify-center space-x-1">
                                        <div>{{ $day['day'] }}</div>
                                        <button
                                            wire:click="openQuickTimeModal('{{ $day['date'] }}')"
                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                            title="Add time log for {{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}"
                                        >
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
                            @foreach($this->weeklyData['weekDays'] as $day)
                                @php
                                    $dayTotal = 0;
                                    foreach($this->weeklyData['projects'] as $project) {
                                        foreach($project['timers'] as $timer) {
                                            $dayTotal += $timer['daily'][$day['date']];
                                        }
                                    }
                                    
                                    $targetMinutes = 444; // 7h 24m = 444 minutes
                                    $remainingMinutes = max(0, $targetMinutes - $dayTotal);
                                    $isToday = Carbon\Carbon::parse($day['date'])->isToday();
                                    $isPast = Carbon\Carbon::parse($day['date'])->isPast();
                                    $targetMet = $remainingMinutes == 0;
                                    
                                    // Determine text color class based on requirements
                                    $textColorClass = '';
                                    if ($targetMet) {
                                        $textColorClass = 'text-green-600';
                                    } elseif ($isPast && !$isToday) {
                                        $textColorClass = 'text-red-600';
                                    }
                                @endphp
                                <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
                                    <div class="{{ $textColorClass }}">
                                        {{ $this->formatDuration($dayTotal) }}
                                        @if($dayTotal > $targetMinutes)
                                            <span class="text-green-600 dark:text-green-400">(+{{ $this->formatRemainingTime($dayTotal - $targetMinutes) }})</span>
                                        @endif
                                        @if(!$targetMet && ($isPast || $isToday))
                                            <div class="text-xs font-normal {{ $textColorClass }}">
                                                Missing: {{ $this->formatRemainingTime($remainingMinutes) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm dark:text-white">
                                {{ $this->formatDuration($this->weeklyData['total']) }}
                            </td>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @if(count($this->weeklyData['projects']) > 0)
                            @foreach($this->weeklyData['projects'] as $project)
                                <!-- Project Row -->
                                <tr class="bg-gray-50 dark:bg-zinc-800 {{ !$project['hasLogs'] ? 'border-t-2 border-indigo-200 dark:border-indigo-800' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white flex items-center justify-between">
                                        <span>
                                            @if($project['id'] === null)
                                                <a href="{{ route('time-logs') }}?view=list&searchQuery=No Project" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                    <span>No Project</span>
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
                                                title="Add quick time log for {{ $project['name'] }}"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                            </button>
                                            
                                            @if($remainingMinutes > 0)
                                                <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[150px] text-xs text-left">
                                                    <div class="{{ $remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400' }} font-medium">
                                                        Missing to reach 7h 24m today: {{ $this->formatRemainingTime($remainingMinutes) }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[150px] text-xs text-left">
                                                    <div class="text-green-500 dark:text-green-400 font-medium">
                                                        7h 24m target reached for today!
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    @foreach($this->weeklyData['weekDays'] as $day)
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
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        @if($timer['id'])
                                                            <a href="{{ route('time-logs') }}?view=list&searchQuery={{ urlencode($timer['originalName']) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                                {{ $timer['name'] }}
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
                                                    @if(count($timer['tags']) > 0)
                                                        <div class="flex flex-wrap gap-1 mt-1">
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
                                        @foreach($this->weeklyData['weekDays'] as $day)
                                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm {{ $timer['daily'][$day['date']] > 0 ? 'bg-indigo-50 dark:bg-indigo-900' : '' }}">
                                                @if($timer['daily'][$day['date']] > 0)
                                                    <div class="flex items-center justify-center space-x-1">
                                                        <div class="group relative">
                                                            <button
                                                                wire:click="findAndEditTimeLog('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }}, '{{ addslashes($timer['dailyDescriptions'][$day['date']]) }}')"
                                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium"
                                                                title="{{ !empty($timer['dailyDescriptions'][$day['date']]) ? 'Description: ' . $timer['dailyDescriptions'][$day['date']] : 'No description' }}"
                                                            >
                                                                {{ $this->formatDuration($timer['daily'][$day['date']]) }}
                                                                @php
                                                                    // Count how many time logs exist for this day
                                                                    $timelogCount = 0;
                                                                    $logId = $timer['dailyLogIds'][$day['date']] ?? null;
                                                                    if ($logId) {
                                                                        $description = $timer['description'] ?? '';
                                                                        $timelogCount = TimeLog::where('user_id', auth()->id())
                                                                            ->where('project_id', $project['id'])
                                                                            ->where(function($query) use ($timer) {
                                                                                if ($timer['id']) {
                                                                                    $query->where('timer_id', $timer['id']);
                                                                                } else {
                                                                                    $query->whereNull('timer_id');
                                                                                }
                                                                            })
                                                                            ->whereDate('start_time', $day['date'])
                                                                            ->where(function($query) use ($description) {
                                                                                if (!empty($description)) {
                                                                                    $query->where('description', $description);
                                                                                } else {
                                                                                    $query->where(function($q) {
                                                                                        $q->whereNull('description')
                                                                                          ->orWhere('description', '');
                                                                                    });
                                                                                }
                                                                            })
                                                                            ->count();
                                                                    }
                                                                @endphp
                                                                @if($timelogCount > 1)
                                                                    <span class="text-xs text-indigo-600 ml-1">({{ $timelogCount }})</span>
                                                                @endif
                                                            </button>
                                                            
                                                            @if(!empty($timer['dailyDescriptions'][$day['date']]))
                                                                <div class="absolute z-10 hidden group-hover:block bg-gray-800 dark:bg-black text-white text-xs rounded p-2 mt-1 min-w-[150px] max-w-[250px] whitespace-normal">
                                                                    {{ $timer['dailyDescriptions'][$day['date']] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="flex space-x-1">
                                                            <button
                                                                wire:click="openQuickTimeModal('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }}, '{{ addslashes($timer['dailyDescriptions'][$day['date']]) }}')"
                                                                class="text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                                                                title="Add new time log for {{ $timer['name'] }} on {{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                                </svg>
                                                            </button>
                                                            
                                                            <button
                                                                wire:click="findAndEditTimeLog('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }}, '{{ addslashes($timer['dailyDescriptions'][$day['date']]) }}')"
                                                                class="text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                                                                title="Edit time log"
                                                            >
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
                                                    @php
                                                        $remainingMinutes = $this->getRemainingTimeForDate($day['date']);
                                                    @endphp
                                                    <div class="group relative">
                                                        <button
                                                            wire:click="openQuickTimeModal('{{ $day['date'] }}', {{ $project['id'] === null ? 'null' : $project['id'] }}, {{ $timer['id'] ?: 'null' }}, '{{ addslashes($timer['dailyDescriptions'][$day['date']]) }}')"
                                                            class="text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 w-full h-full flex items-center justify-center"
                                                            title="Add quick time log for {{ $timer['name'] }} on {{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                        
                                                        @if($remainingMinutes > 0)
                                                            <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 min-w-[150px] text-xs text-left">
                                                                <div class="{{ $remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400' }} font-medium">
                                                                    Missing to reach 7h 24m: {{ $this->formatRemainingTime($remainingMinutes) }}
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 min-w-[150px] text-xs text-left">
                                                                <div class="text-green-500 dark:text-green-400 font-medium">
                                                                    7h 24m target reached for this day!
                                                                </div>
                                                            </div>
                                                        @endif
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
                                <td colspan="{{ count($this->weeklyData['weekDays']) + 2 }}" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No time logs for this week
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- List View & Manual Time Log Entry -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @if($view === 'list')
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6">
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-xl font-semibold dark:text-white">All Time Logs</h2>
                                @if($filterProject || $filterTag || $filterDateFrom || $filterDateTo || $searchQuery)
                                    <div class="text-sm text-indigo-600 dark:text-indigo-400 mt-1">
                                        <span class="font-medium">Filtered:</span> {{ count($timeLogs) }} entries | Total: {{ $this->formatDuration($totalFilteredDuration) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center space-x-3">
                                @php
                                    $todayDate = now()->format('Y-m-d');
                                    $remainingMinutes = $this->getRemainingTimeForDate($todayDate);
                                @endphp
                                <div class="group relative">
                                    <button
                                        wire:click="openQuickTimeModal"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Quick Time
                                    </button>
                                    
                                    @if($remainingMinutes > 0)
                                        <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[180px] text-xs text-left">
                                            <div class="{{ $remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400' }} font-medium">
                                                Missing to reach 7h 24m today: {{ $this->formatRemainingTime($remainingMinutes) }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="absolute z-10 hidden group-hover:block bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg p-2 mt-1 right-0 min-w-[180px] text-xs text-left">
                                            <div class="text-green-500 dark:text-green-400 font-medium">
                                                7h 24m target reached for today!
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if(count($selectedTimeLogs) > 0)
                                    <button
                                        wire:click="confirmBulkDelete"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    >
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
                                placeholder="Search time logs by description or project..."
                            >
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
                                        @if($timeLog->project_id && $timeLog->project->trashed())
                                            <span class="line-through text-gray-500 dark:text-gray-400">{{ $timeLog->project->name }}</span>
                                            <span class="text-xs text-red-500 dark:text-red-400">(deleted)</span>
                                        @elseif($timeLog->project_id)
                                            <a href="{{ route('time-logs') }}?view=list&filterProject={{ $timeLog->project_id }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                {{ $timeLog->project->name }}
                                            </a>
                                        @else
                                            <a href="{{ route('time-logs') }}?view=list&searchQuery=No Project" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                No Project
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getDurationClass($timeLog->duration_minutes) }}">
                                        {{ $this->formatDuration($timeLog->duration_minutes) }}
                                    </span>
                                </div>
                                
                                <div class="col-span-1 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="startEdit({{ $timeLog->id }})" class="text-indigo-600 hover:text-indigo-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $timeLog->id }})" class="text-red-600 hover:text-red-900">
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
                                    <button wire:click="resetFilters" class="mt-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                                        Clear filters
                                    </button>
                                @else
                                    <p class="mt-2 text-sm">Start tracking time to see logs here!</p>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
        
        <div id="time-log-form" class="{{ $view === 'list' ? '' : 'lg:col-span-3' }} bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6 transition-colors duration-300">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">
                @if($editingTimeLog)
                    Edit Time Log
                @else
                    Create Manual Time Log
                    @if($selected_date && $selected_date != now()->format('Y-m-d'))
                        <span class="text-sm font-normal text-indigo-600 dark:text-indigo-400">
                            for {{ Carbon\Carbon::parse($selected_date)->format('M d, Y') }}
                        </span>
                    @endif
                @endif
            </h2>
            <form wire:submit.prevent="{{ $editingTimeLog ? 'updateTimeLog' : 'save' }}" class="space-y-4">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                    <select wire:model="project_id" id="project_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
                        <option value="">Select a project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="selected_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input type="date" wire:model.live="selected_date" id="selected_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
                    @error('selected_date') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
                    
                    @if($selected_date)
                        @php
                            $remainingMinutes = $this->getRemainingTimeForDate($selected_date);
                        @endphp
                        <div class="mt-1 text-xs {{ $remainingMinutes > 0 ? ($remainingMinutes < 60 ? 'text-orange-500 dark:text-orange-400' : 'text-blue-500 dark:text-blue-400') : 'text-green-500 dark:text-green-400' }}">
                            @if($remainingMinutes > 0)
                                <span class="font-medium">Missing to reach 7h 24m:</span> {{ $this->formatRemainingTime($remainingMinutes) }}
                            @else
                                <span class="font-medium">7h 24m target reached for this day!</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (optional)</label>
                    <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2"></textarea>
                </div>
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" wire:model="duration_minutes" id="duration_minutes" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
                        @if($duration_minutes)
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                ({{ $this->formatDuration($this->parseDurationString($duration_minutes)) }})
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter duration in minutes or format like "3h5m"</p>
                    @error('duration_minutes') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tags</label>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label 
                                class="inline-flex items-center px-3 py-1 rounded-full cursor-pointer 
                                    {{ in_array($tag->id, $selectedTags) ? 'bg-opacity-100' : 'bg-opacity-30' }}" 
                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}"
                            >
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedTags" 
                                    value="{{ $tag->id }}" 
                                    class="form-checkbox h-4 w-4 mr-1 opacity-0 absolute"
                                >
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-between">
                    @if($editingTimeLog)
                        <button type="button" wire:click="cancelEdit" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
                            Cancel
                        </button>
                    @endif
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $editingTimeLog ? 'Update Time Log' : 'Create Time Log' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Include Modals -->
    @include('livewire.time-logs.modals.edit-form-modal')
    @include('livewire.time-logs.modals.delete-confirmation-modal')
    @include('livewire.time-logs.modals.bulk-delete-confirmation-modal')
    @include('livewire.time-logs.modals.time-log-selection-modal')
    @include('livewire.time-logs.modals.quick-time-modal')
</div>
