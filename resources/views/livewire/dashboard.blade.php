<div>
    <h1 class="text-2xl font-semibold mb-4 dark:text-white">Dashboard</h1>
    
    <!-- Period Selector -->
    <div class="mb-6 flex justify-between items-center">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <button wire:click="setPeriod('day')" type="button" class="px-4 py-2 text-sm font-medium {{ $period === 'day' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-l-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
                Today
            </button>
            <button wire:click="setPeriod('week')" type="button" class="px-4 py-2 text-sm font-medium {{ $period === 'week' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border-t border-b border-r border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700">
                This Week
            </button>
            <button wire:click="setPeriod('month')" type="button" class="px-4 py-2 text-sm font-medium {{ $period === 'month' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border-t border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700">
                This Month
            </button>
            <button wire:click="setPeriod('year')" type="button" class="px-4 py-2 text-sm font-medium {{ $period === 'year' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-r-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
                This Year
            </button>
        </div>
        
        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
        </div>
    </div>
    
    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('time-logs') }}" class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-5 border-l-4 border-indigo-500 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors duration-200">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Time Tracked</p>
                    <p class="text-2xl font-bold dark:text-white">{{ $this->timeDistribution['formattedTotal'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">in {{ count($this->timeDistribution['projects']) }} projects</p>
                </a>
                
                <a href="{{ route('timers') }}" class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-5 border-l-4 border-green-500 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors duration-200">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Timers</p>
                    <p class="text-2xl font-bold dark:text-white">{{ $this->runningTimers->count() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">running now</p>
                </a>
                
                <a href="{{ route('projects') }}" class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-5 border-l-4 border-amber-500 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors duration-200">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Projects</p>
                    <p class="text-2xl font-bold dark:text-white">{{ $projectCount }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">total</p>
                </a>
        
        <a href="{{ route('time-logs') }}" class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-5 border-l-4 border-purple-500 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors duration-200">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tags</p>
                    <p class="text-2xl font-bold dark:text-white">{{ $tagCount }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">for categorizing</p>
                </a>
    </div>

    <!-- Main Dashboard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Time Distribution By Project -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-6 dark:text-white">Time Distribution</h2>
            
            @if(count($this->timeDistribution['projects']) > 0)
                <div class="relative pt-1">
                    <div class="overflow-hidden h-6 mb-2 text-xs flex rounded bg-gray-100 dark:bg-zinc-800">
                        @foreach($this->timeDistribution['projects'] as $project)
                            <div
                                style="width: {{ $project['percentage'] }}%; background-color: {{ $project['color'] }};"
                                class="h-full flex items-center justify-center text-white">
                                @if($project['percentage'] >= 5)
                                    <span class="px-1" style="color: {{ $this->getContrastColor($project['color']) }}">{{ $project['percentage'] }}%</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="space-y-3 mt-4">
                    @foreach($this->timeDistribution['projects'] as $project)
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $project['color'] }}"></div>
                                @if($project['id'] === null)
                                    <a href="{{ route('time-logs', ['searchQuery' => 'No Project']) }}" class="dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ $project['name'] }}</a>
                                @else
                                    <a href="{{ route('time-logs', ['filterProject' => $project['id']]) }}" class="dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ $project['name'] }}</a>
                                @endif
                            </div>
                            <div class="text-gray-600 dark:text-gray-400">
                                {{ $this->formatDuration($project['duration']) }} ({{ $project['percentage'] }}%)
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p>No time data for this period</p>
                    <p class="text-sm mt-1">Start tracking time to see statistics</p>
                </div>
            @endif
        </div>

        <!-- Timers Link -->
        <div class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold dark:text-white">Timers</h2>
                <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 text-xs font-medium px-2.5 py-0.5 rounded">{{ $this->runningTimers->count() }} active</span>
            </div>
            
            <div class="text-center py-6">
                <p class="text-gray-600 dark:text-gray-400 mb-4">Manage your timers and track your time efficiently</p>
                <a href="{{ route('timers') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    Go to Timers
                </a>
            </div>
        </div>

        <!-- Daily Activity -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold dark:text-white">
                    {{ $period === 'year' ? 'Monthly Activity' : 'Daily Activity' }}
                </h2>
                
                @if($period === 'year')
                    <div class="flex items-center space-x-3 text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-1 bg-green-500"></div>
                            <span class="dark:text-gray-300">Met target</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-1 bg-amber-400"></div>
                            <span class="dark:text-gray-300">Below target</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-1 bg-indigo-300"></div>
                            <span class="dark:text-gray-300">No data</span>
                        </div>
                    </div>
                @endif
            </div>
            
            @if(array_sum($this->dailyActivity) > 0)
                @php
                    $maxHeight = max(array_values($this->dailyActivity));
                    
                    // Set workday minutes based on period
                    if ($period !== 'year') {
                        $workdayMinutes = 444; // 7h 24m in minutes
                        $workdayLinePosition = $maxHeight > 0 ? min(100, ($workdayMinutes / $maxHeight) * 100) : 40;
                        $workdayLabel = '7h 24m workday';
                    }
                @endphp
                <div class="w-full h-60">
                    <div class="flex justify-between h-full relative">
                        <!-- 7h 24m hour mark line -->
                        @if($period !== 'year')
                            <!-- Show reference line only for day, week, and month views -->
                            <div class="absolute w-full border-t border-dashed border-red-400 z-10"
                                 style="bottom: {{ min(100, $workdayLinePosition) }}%;">
                                <span class="absolute -top-6 right-0 text-xs text-red-500 font-medium bg-white dark:bg-zinc-800 px-1 rounded shadow-sm">
                                    {{ $workdayLabel }}
                                </span>
                            </div>
                        @endif
                        @foreach($this->dailyActivity as $date => $minutes)
                            @php
                                $heightPercentage = $maxHeight > 0 ? ($minutes / $maxHeight) * 100 : 0;
                                $displayDate = \Carbon\Carbon::parse($date);
                                $isToday = $displayDate->isToday();
                            @endphp
                            <div class="flex flex-col items-center flex-1">
                                <div class="tooltip relative w-full px-1 flex-grow flex flex-col justify-end h-[200px]">
                                    @php
                                        if ($period === 'year') {
                                            $monthEnd = $displayDate->copy()->endOfMonth()->format('Y-m-d');
                                            $filterUrl = route('time-logs', ['filterDateFrom' => $date, 'filterDateTo' => $monthEnd]);
                                        } else {
                                            $filterUrl = route('time-logs', ['filterDateFrom' => $date, 'filterDateTo' => $date]);
                                        }
                                    @endphp
                                    @php
                                        // Calculate threshold for year view based on workdays in the month
                                        $barColor = 'bg-indigo-300';
                                        
                                        if ($period === 'year') {
                                            // Count workdays (Mon-Fri) in the month
                                            $monthStart = $displayDate->copy()->startOfMonth();
                                            $monthEnd = $displayDate->copy()->endOfMonth();
                                            $workdays = 0;
                                            
                                            for ($day = $monthStart; $day->lte($monthEnd); $day->addDay()) {
                                                // 0 = Sunday, 6 = Saturday
                                                if ($day->dayOfWeek !== 0 && $day->dayOfWeek !== 6) {
                                                    $workdays++;
                                                }
                                            }
                                            
                                            // Calculate threshold: 7.4 hours (444 minutes) per workday
                                            $threshold = $workdays * 444;
                                            
                                            // Set color based on whether the month meets the threshold
                                            if ($minutes > 0 && $minutes < $threshold) {
                                                $barColor = 'bg-amber-400'; // Below threshold
                                            } elseif ($minutes >= $threshold) {
                                                $barColor = 'bg-green-500'; // Met or exceeded threshold
                                            }
                                        } elseif ($isToday) {
                                            $barColor = 'bg-indigo-500';
                                        }
                                    @endphp
                                    <a
                                        href="{{ $filterUrl }}"
                                        class="w-full rounded-t {{ $barColor }} hover:bg-indigo-400 group transition-all duration-300"
                                        style="height: {{ $heightPercentage }}%; min-height: {{ $minutes > 0 ? '4px' : '0' }};"
                                    >
                                        @if($period === 'year')
                                            <!-- Always visible data for year view -->
                                            @if($minutes > 0)
                                                <div class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-center text-center p-1 text-xs bg-black bg-opacity-20 rounded-t">
                                                    <span class="font-bold text-white">
                                                        {{ $this->formatDuration($minutes) }}
                                                    </span>
                                                    <span class="text-[10px] text-white">
                                                        / {{ $this->formatDuration($threshold) }}
                                                    </span>
                                                    <span class="text-[9px] text-white opacity-80">
                                                        ({{ $workdays }} days)
                                                    </span>
                                                </div>
                                                
                                                <!-- Additional hover tooltip with difference from target -->
                                                <div class="absolute bottom-full mb-2 hidden group-hover:block w-full">
                                                    <div class="bg-gray-800 text-white text-xs rounded py-1 px-2 text-center mx-auto w-max">
                                                        @php
                                                            $difference = $minutes - $threshold;
                                                            if ($difference > 0) {
                                                                echo '<span class="text-green-400">+' . $this->formatDuration(abs($difference)) . ' above target</span>';
                                                            } elseif ($difference < 0) {
                                                                echo '<span class="text-red-400">-' . $this->formatDuration(abs($difference)) . ' below target</span>';
                                                            } else {
                                                                echo '<span>Exactly on target</span>';
                                                            }
                                                        @endphp
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <!-- Hover tooltip for other views -->
                                            <div class="absolute bottom-full mb-2 hidden group-hover:block w-full">
                                                <div class="bg-gray-800 text-white text-xs rounded py-1 px-2 text-center mx-auto w-max">
                                                    {{ $this->formatDuration($minutes) }}
                                                </div>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                                @if($period === 'year')
                                    @php
                                        $monthEnd = $displayDate->copy()->endOfMonth();
                                        $filterDateTo = $monthEnd->format('Y-m-d');
                                    @endphp
                                    <a href="{{ route('time-logs', ['filterDateFrom' => $date, 'filterDateTo' => $filterDateTo]) }}" class="text-xs text-gray-500 dark:text-gray-400 mt-2 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                        {{ $displayDate->format('M') }}
                                    </a>
                                    <a href="{{ route('time-logs', ['filterDateFrom' => $date, 'filterDateTo' => $filterDateTo]) }}" class="text-xs text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                        {{ $displayDate->format('Y') }}
                                    </a>
                                @else
                                    <a href="{{ route('time-logs', ['filterDateFrom' => $date, 'filterDateTo' => $date]) }}" class="text-xs text-gray-500 dark:text-gray-400 mt-2 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                        {{ $displayDate->format('D') }}
                                    </a>
                                    <a href="{{ route('time-logs', ['filterDateFrom' => $date, 'filterDateTo' => $date]) }}" class="text-xs text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                                        {{ $displayDate->format('d') }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                    <p>No daily data for this period</p>
                    <p class="text-sm mt-1">Track time to see your daily activity</p>
                </div>
            @endif
        </div>

        <!-- Popular Tags & Recent Logs -->
        <div class="bg-white dark:bg-zinc-900 shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">Popular Tags</h2>
            
            @if(count($this->popularTags) > 0)
                <div class="space-y-3">
                    @foreach($this->popularTags as $tag)
                        <div class="flex justify-between items-center">
                            <a
                                href="{{ route('time-logs', ['filterTag' => $tag->id]) }}"
                                class="inline-block px-3 py-1 rounded-full hover:opacity-90 transition-opacity duration-200"
                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}"
                            >
                                {{ $tag->name }}
                            </a>
                            <div class="text-gray-600 dark:text-gray-400 text-sm">
                                {{ $tag->count }} uses
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    <p>No tags used in this period</p>
                </div>
            @endif
            
            <div class="border-t dark:border-zinc-700 mt-6 pt-4">
                <h2 class="text-xl font-semibold mb-4 dark:text-white">Recent Time Logs</h2>
                <div class="space-y-4">
                    @forelse($recentTimeLogs as $timeLog)
                        <div class="border-b dark:border-zinc-700 pb-3">
                            <a href="{{ route('time-logs', ['filterProject' => $timeLog->project_id]) }}" class="font-medium dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ $timeLog->project->name }}</a>
                            @if($timeLog->description)
                                <a href="{{ route('time-logs', ['editId' => $timeLog->id, 'returnToDashboard' => true]) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ $timeLog->description }}</a>
                            @endif
                            <div class="flex justify-between items-center mt-1">
                                <a href="{{ route('time-logs', ['editId' => $timeLog->id, 'returnToDashboard' => true]) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ $this->formatDuration($timeLog->duration_minutes) }}</a>
                                    <a href="{{ route('time-logs', ['editId' => $timeLog->id, 'returnToDashboard' => true]) }}" class="text-xs text-gray-500 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">{{ $timeLog->start_time->format('M d, H:i') }}</a>
                            </div>
                            @if($timeLog->tags->count() > 0)
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($timeLog->tags as $tag)
                                        <a href="{{ route('time-logs', ['filterTag' => $tag->id]) }}"
                                            class="inline-block px-2 py-0.5 text-xs rounded-full hover:opacity-90 transition-opacity duration-200"
                                            style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                            {{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">No time logs yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- No timer scripts needed since we're not displaying running timers on the dashboard -->
</div>
