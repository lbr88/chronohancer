<div>
    <h1 class="text-2xl font-semibold mb-4">Time Logs</h1>
    
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
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
                <button wire:click="switchView('list')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-l-lg hover:bg-gray-100">
                    List View
                </button>
                <button wire:click="switchView('weekly')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'weekly' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-r-lg hover:bg-gray-100">
                    Weekly Summary
                </button>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center space-x-2">
            <!-- Time Format Selector -->
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button wire:click="setTimeFormat('human')" type="button" class="px-3 py-1 text-xs font-medium {{ $timeFormat === 'human' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-l-lg hover:bg-gray-100">
                    3h 40m
                </button>
                <button wire:click="setTimeFormat('hm')" type="button" class="px-3 py-1 text-xs font-medium {{ $timeFormat === 'hm' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 hover:bg-gray-100">
                    HH:MM
                </button>
                <button wire:click="setTimeFormat('hms')" type="button" class="px-3 py-1 text-xs font-medium {{ $timeFormat === 'hms' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-r-lg hover:bg-gray-100">
                    HH:MM:SS
                </button>
            </div>
            
            <!-- Filter Toggle Button -->
            <button wire:click="toggleFilters" class="px-3 py-1 text-xs font-medium border rounded-md hover:bg-gray-100 flex items-center">
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
    <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="searchQuery" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" wire:model.debounce.300ms="searchQuery" id="searchQuery"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    placeholder="Search description or project...">
            </div>
            
            <div>
                <label for="filterProject" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select wire:model="filterProject" id="filterProject" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="filterTag" class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                <select wire:model="filterTag" id="filterTag" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">All Tags</option>
                    @foreach($allTags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="filterDateFrom" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" wire:model="filterDateFrom" id="filterDateFrom" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="filterDateTo" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" wire:model="filterDateTo" id="filterDateTo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
            </div>
        </div>
        
        <div class="mt-4 flex justify-end">
            <button wire:click="resetFilters" class="px-3 py-1 text-sm font-medium border rounded-md hover:bg-gray-100">
                Reset Filters
            </button>
        </div>
    </div>
    @endif

    @if($view === 'weekly')
        <!-- Weekly Summary View -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <button wire:click="previousWeek" class="px-3 py-1 border rounded-md hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                <h2 class="text-xl font-medium">
                    {{ Carbon\Carbon::parse($startOfWeek)->format('M d') }} - {{ Carbon\Carbon::parse($endOfWeek)->format('M d, Y') }}
                </h2>
                <div class="flex items-center space-x-2">
                    <button
                        wire:click="openQuickTimeModal"
                        class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Quick Time
                    </button>
                    <button wire:click="currentWeek" class="px-3 py-1 border rounded-md hover:bg-gray-100 {{ $currentWeek->isCurrentWeek() ? 'bg-gray-200' : '' }}">
                        Today
                    </button>
                    <button wire:click="nextWeek" class="px-3 py-1 border rounded-md hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                Projects & Tasks
                            </th>
                            @foreach($this->weeklyData['weekDays'] as $day)
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider {{ Carbon\Carbon::parse($day['date'])->isToday() ? 'bg-indigo-50' : '' }}">
                                    <div>{{ $day['day'] }}</div>
                                    <div>{{ Carbon\Carbon::parse($day['date'])->format('M d') }}</div>
                                </th>
                            @endforeach
                            <th scope="col" class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(count($this->weeklyData['projects']) > 0)
                            @foreach($this->weeklyData['projects'] as $project)
                                <!-- Project Row -->
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 flex items-center justify-between">
                                        <span>{{ $project['name'] }}</span>
                                        <button
                                            wire:click="openQuickTimeModal('{{ now()->format('Y-m-d') }}', {{ $project['id'] }})"
                                            class="text-gray-400 hover:text-indigo-600"
                                            title="Add quick time log for {{ $project['name'] }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                    </td>
                                    @foreach($this->weeklyData['weekDays'] as $day)
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500"></td>
                                    @endforeach
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold">
                                        {{ $this->formatDuration($project['total']) }}
                                    </td>
                                </tr>
                                
                                <!-- Timer Rows -->
                                @foreach($project['timers'] as $timer)
                                    <tr>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $timer['name'] }}
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
                                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm {{ $timer['daily'][$day['date']] > 0 ? 'bg-indigo-50' : '' }}">
                                                @if($timer['daily'][$day['date']] > 0)
                                                    <div class="flex items-center justify-center space-x-1">
                                                        <div class="group relative">
                                                            <button
                                                                wire:click="findAndEditTimeLog('{{ $day['date'] }}', {{ $project['id'] }}, {{ $timer['id'] ?: 'null' }})"
                                                                class="text-indigo-600 hover:text-indigo-900 font-medium"
                                                                title="{{ !empty($timer['dailyDescriptions'][$day['date']]) ? 'Description: ' . $timer['dailyDescriptions'][$day['date']] : 'No description' }}"
                                                            >
                                                                {{ $this->formatDuration($timer['daily'][$day['date']]) }}
                                                            </button>
                                                            
                                                            @if(!empty($timer['dailyDescriptions'][$day['date']]))
                                                                <div class="absolute z-10 hidden group-hover:block bg-gray-800 text-white text-xs rounded p-2 mt-1 min-w-[150px] max-w-[250px] whitespace-normal">
                                                                    {{ $timer['dailyDescriptions'][$day['date']] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="flex space-x-1">
                                                            <button
                                                                wire:click="findAndEditTimeLog('{{ $day['date'] }}', {{ $project['id'] }}, {{ $timer['id'] ?: 'null' }})"
                                                                class="text-gray-400 hover:text-indigo-600"
                                                                title="Edit time log"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                </svg>
                                                            </button>
                                                            
                                                            @if(!empty($timer['dailyDescriptions'][$day['date']]))
                                                                <span class="text-gray-400" title="Has description: {{ $timer['dailyDescriptions'][$day['date']] }}">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <button
                                                        wire:click="openQuickTimeModal('{{ $day['date'] }}', {{ $project['id'] }}, {{ $timer['id'] ?: 'null' }})"
                                                        class="text-gray-400 hover:text-indigo-600 w-full h-full flex items-center justify-center"
                                                        title="Add quick time log for {{ $timer['name'] }} on {{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                                            {{ $this->formatDuration($timer['total']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            
                            <!-- Grand Total Row -->
                            <tr class="bg-indigo-50 font-bold">
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
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
                                    @endphp
                                    <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
                                        {{ $this->formatDuration($dayTotal) }}
                                    </td>
                                @endforeach
                                <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
                                    {{ $this->formatDuration($this->weeklyData['total']) }}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="{{ count($this->weeklyData['weekDays']) + 2 }}" class="px-6 py-10 text-center text-gray-500">
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
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">All Time Logs</h2>
                        <div class="flex items-center space-x-3">
                            <button
                                wire:click="openQuickTimeModal"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Quick Time
                            </button>
                            <div class="text-sm text-gray-500">
                                {{ count($timeLogs) }} entries
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sortable Headers -->
                    <div class="grid grid-cols-12 gap-4 mb-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="col-span-3 flex items-center cursor-pointer" wire:click="sortBy('start_time')">
                            Date
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
                            Project
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
                        <div class="col-span-4">Description</div>
                        <div class="col-span-1 flex items-center cursor-pointer" wire:click="sortBy('duration')">
                            Time
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
                        <div class="col-span-1 text-right">Actions</div>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($timeLogs as $timeLog)
                            <div class="grid grid-cols-12 gap-4 py-3 border-b items-center hover:bg-gray-50 rounded-lg transition-colors">
                                <div class="col-span-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $timeLog->start_time->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $timeLog->start_time ? $timeLog->start_time->format('H:i') : '?' }} - {{ $timeLog->end_time ? $timeLog->end_time->format('H:i') : '?' }}
                                    </div>
                                </div>
                                
                                <div class="col-span-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $timeLog->project->name }}
                                    </div>
                                    @if($timeLog->timer)
                                        <div class="text-xs text-gray-500">
                                            Timer: {{ $timeLog->timer->name }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500">
                                            Manual Entry
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-span-4">
                                    <div class="text-sm text-gray-600 line-clamp-2">
                                        {{ $timeLog->description ?: 'No description' }}
                                    </div>
                                    @if($timeLog->tags->count() > 0)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($timeLog->tags as $tag)
                                                <span class="px-2 py-0.5 text-xs rounded-full"
                                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-span-1">
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
                            <div class="py-8 text-center text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p>No time logs found matching your criteria.</p>
                                @if($filterProject || $filterTag || $filterDateFrom || $filterDateTo || $searchQuery)
                                    <button wire:click="resetFilters" class="mt-2 text-indigo-600 hover:text-indigo-900 text-sm font-medium">
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
        
        <div id="time-log-form" class="{{ $view === 'list' ? '' : 'lg:col-span-3' }} bg-white shadow-md rounded-lg p-6 transition-colors duration-300">
            <h2 class="text-xl font-semibold mb-4">
                @if($editingTimeLog)
                    Edit Time Log
                @else
                    Create Manual Time Log
                    @if($selected_date && $selected_date != now()->format('Y-m-d'))
                        <span class="text-sm font-normal text-indigo-600">
                            for {{ Carbon\Carbon::parse($selected_date)->format('M d, Y') }}
                        </span>
                    @endif
                @endif
            </h2>
            <form wire:submit.prevent="{{ $editingTimeLog ? 'updateTimeLog' : 'save' }}" class="space-y-4">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700">Project</label>
                    <select wire:model="project_id" id="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select a project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="selected_date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" wire:model="selected_date" id="selected_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('selected_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" wire:model="duration_minutes" id="duration_minutes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @if($duration_minutes)
                            <span class="text-sm text-gray-500">
                                ({{ $this->formatDuration($this->parseDurationString($duration_minutes)) }})
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter duration in minutes or format like "3h5m"</p>
                    @error('duration_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                        <button type="button" wire:click="cancelEdit" class="px-4 py-2 border rounded-md hover:bg-gray-100">
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

    <!-- Edit Form (shown when editing) -->
    @if($editingTimeLog)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
            <h2 class="text-xl font-semibold mb-4">Edit Time Log</h2>
            <form wire:submit.prevent="updateTimeLog" class="space-y-4">
                <div>
                    <label for="edit_project_id" class="block text-sm font-medium text-gray-700">Project</label>
                    <select wire:model="project_id" id="edit_project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select a project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <textarea wire:model="description" id="edit_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div>
                    <label for="edit_duration_minutes" class="block text-sm font-medium text-gray-700">Duration</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" wire:model="duration_minutes" id="edit_duration_minutes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @if($duration_minutes)
                            <span class="text-sm text-gray-500">
                                ({{ $this->formatDuration($this->parseDurationString($duration_minutes)) }})
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter duration in minutes or format like "3h5m"</p>
                    @error('duration_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 border rounded-md hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Update Time Log
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
            <p class="mb-4">Are you sure you want to delete this time log? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button type="button" wire:click="cancelDelete" class="px-4 py-2 border rounded-md hover:bg-gray-100">
                    Cancel
                </button>
                <button type="button" wire:click="deleteTimeLog({{ $confirmingDelete }})" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Time Log Selection Modal -->
    @if($showTimeLogSelectionModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-semibold mb-4">Select Time Log to Edit</h2>
            <p class="text-sm text-gray-600 mb-4">Multiple time logs found for this period. Please select one to edit:</p>
            
            <div class="space-y-2 max-h-80 overflow-y-auto mb-4">
                @foreach($timeLogSelectionOptions as $option)
                    <button
                        wire:click="selectTimeLogToEdit({{ $option['id'] }})"
                        class="w-full text-left p-3 border rounded-md hover:bg-indigo-50 transition-colors"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-medium">{{ $option['description'] }}</div>
                                <div class="text-sm text-gray-500">{{ $option['start_time'] }} - {{ $option['end_time'] }}</div>
                            </div>
                            <div class="text-indigo-600 font-medium">{{ $option['duration'] }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
            
            <div class="flex justify-end">
                <button
                    wire:click="closeTimeLogSelectionModal"
                    class="px-4 py-2 border rounded-md hover:bg-gray-100"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Time Modal -->
    @if($showQuickTimeModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-semibold mb-2">Quick Time Entry</h2>
            @if($quickTimeProjectId)
                <div class="text-sm text-indigo-600 mb-4">
                    @if($quickTimeTimerId)
                        @php
                            $timerName = 'Manual Entry';
                            foreach($quickTimeProjectTimers as $timer) {
                                if($timer->id == $quickTimeTimerId) {
                                    $timerName = $timer->name;
                                    break;
                                }
                            }
                        @endphp
                        For timer: {{ $timerName }}
                    @else
                        For project: {{ collect($projects)->firstWhere('id', $quickTimeProjectId)->name ?? 'Selected Project' }}
                    @endif
                </div>
            @else
                <div class="mb-4"></div>
            @endif
            
            <div class="mb-6">
                <div class="bg-gray-100 p-4 rounded-lg text-center mb-4">
                    <div class="text-3xl font-mono font-bold text-indigo-600">
                        {{ floor($quickTimeDuration / 60) }}:{{ str_pad($quickTimeDuration % 60, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $this->formatDuration($quickTimeDuration) }}
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button type="button" wire:click="addQuickTime(5)" class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                        +5m
                    </button>
                    <button type="button" wire:click="addQuickTime(15)" class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                        +15m
                    </button>
                    <button type="button" wire:click="addQuickTime(30)" class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                        +30m
                    </button>
                    <button type="button" wire:click="setQuickTime(30)" class="px-3 py-2 bg-indigo-200 text-indigo-700 rounded-md hover:bg-indigo-300">
                        30m
                    </button>
                    <button type="button" wire:click="setQuickTime(60)" class="px-3 py-2 bg-indigo-200 text-indigo-700 rounded-md hover:bg-indigo-300">
                        1h
                    </button>
                    <button type="button" wire:click="setQuickTime(120)" class="px-3 py-2 bg-indigo-200 text-indigo-700 rounded-md hover:bg-indigo-300">
                        2h
                    </button>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label for="quick_time_date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" wire:model="quickTimeDate" id="quick_time_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                
                <div>
                    <label for="quick_time_project_id" class="block text-sm font-medium text-gray-700">Project</label>
                    <select wire:model="quickTimeProjectId" id="quick_time_project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select a project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="quick_time_timer_id" class="block text-sm font-medium text-gray-700">Timer</label>
                    <select wire:model="quickTimeTimerId" id="quick_time_timer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Manual Entry</option>
                        @foreach($quickTimeProjectTimers as $timer)
                            <option value="{{ $timer->id }}">{{ $timer->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="quick_time_description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <textarea wire:model="quickTimeDescription" id="quick_time_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" wire:click="closeQuickTimeModal" class="px-4 py-2 border rounded-md hover:bg-gray-100">
                    Cancel
                </button>
                <button type="button" wire:click="saveQuickTime" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Save Time Log
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
