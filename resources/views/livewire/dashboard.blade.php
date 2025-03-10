<div>
    <h1 class="text-2xl font-semibold mb-4">Dashboard</h1>
    
    <!-- Period Selector -->
    <div class="mb-6 flex justify-between items-center">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <button wire:click="setPeriod('week')" type="button" class="px-4 py-2 text-sm font-medium {{ $period === 'week' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-l-lg hover:bg-gray-100">
                This Week
            </button>
            <button wire:click="setPeriod('month')" type="button" class="px-4 py-2 text-sm font-medium {{ $period === 'month' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border-t border-b border-gray-200 hover:bg-gray-100">
                This Month
            </button>
            <button wire:click="setPeriod('year')" type="button" class="px-4 py-2 text-sm font-medium {{ $period === 'year' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-r-lg hover:bg-gray-100">
                This Year
            </button>
        </div>
        
        <div class="text-sm text-gray-600">
            {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
        </div>
    </div>
    
    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-indigo-500">
            <p class="text-sm font-medium text-gray-500">Total Time Tracked</p>
            <p class="text-2xl font-bold">{{ $this->timeDistribution['formattedTotal'] }}</p>
            <p class="text-sm text-gray-500 mt-1">in {{ count($this->timeDistribution['projects']) }} projects</p>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-green-500">
            <p class="text-sm font-medium text-gray-500">Active Timers</p>
            <p class="text-2xl font-bold">{{ $this->runningTimers->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">running now</p>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-amber-500">
            <p class="text-sm font-medium text-gray-500">Projects</p>
            <p class="text-2xl font-bold">{{ $projectCount }}</p>
            <p class="text-sm text-gray-500 mt-1">total</p>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-purple-500">
            <p class="text-sm font-medium text-gray-500">Tags</p>
            <p class="text-2xl font-bold">{{ $tagCount }}</p>
            <p class="text-sm text-gray-500 mt-1">for categorizing</p>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Time Distribution By Project -->
        <div class="lg:col-span-2 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-6">Time Distribution</h2>
            
            @if(count($this->timeDistribution['projects']) > 0)
                <div class="relative pt-1">
                    <div class="overflow-hidden h-6 mb-2 text-xs flex rounded bg-gray-100">
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
                                <span>{{ $project['name'] }}</span>
                            </div>
                            <div class="text-gray-600">
                                {{ $this->formatDuration($project['duration']) }} ({{ $project['percentage'] }}%)
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No time data for this period</p>
                    <p class="text-sm mt-1">Start tracking time to see statistics</p>
                </div>
            @endif
        </div>

        <!-- Running Timers -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Running Timers</h2>
            
            <div class="space-y-4">
                @forelse($this->runningTimers as $timer)
                    <div class="border border-indigo-100 rounded-lg p-4 bg-indigo-50">
                        <div class="flex justify-between mb-2">
                            <h3 class="font-semibold">{{ $timer->name }}</h3>
                            <span class="text-sm text-indigo-600 timer-display" data-start="{{ $timer->created_at->toIso8601String() }}">00:00:00</span>
                        </div>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Project:</span> {{ $timer->project->name }}
                        </p>
                        @if($timer->tags->count() > 0)
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($timer->tags as $tag)
                                    <span class="inline-block px-2 py-0.5 text-xs rounded-full" 
                                        style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-500">
                        <p>No active timers</p>
                        <p class="text-sm mt-1">Start a timer to track your time</p>
                        <a href="{{ route('timers') }}" class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 rounded mt-3 text-sm hover:bg-indigo-200">
                            Start Timer
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Daily Activity -->
        <div class="lg:col-span-2 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-6">Daily Activity</h2>
            
            @if(array_sum($this->dailyActivity) > 0)
                <div class="w-full h-60">
                    <div class="flex justify-between h-full">
                        @foreach($this->dailyActivity as $date => $minutes)
                            @php
                                $maxHeight = max(array_values($this->dailyActivity));
                                $height = $maxHeight > 0 ? ($minutes / $maxHeight) * 100 : 0;
                                $displayDate = \Carbon\Carbon::parse($date);
                                $isToday = $displayDate->isToday();
                            @endphp
                            <div class="flex flex-col items-center flex-1 justify-end">
                                <div class="tooltip relative w-full px-1">
                                    <div 
                                        class="h-[{{ $height }}%] w-full rounded-t {{ $isToday ? 'bg-indigo-500' : 'bg-indigo-300' }} hover:bg-indigo-400 group"
                                        style="min-height: {{ $minutes > 0 ? '4px' : '0' }};"
                                    >
                                        <div class="absolute bottom-full mb-2 hidden group-hover:block w-full">
                                            <div class="bg-gray-800 text-white text-xs rounded py-1 px-2 text-center mx-auto w-max">
                                                {{ $this->formatDuration($minutes) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    {{ $displayDate->format('D') }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $displayDate->format('d') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-10 text-gray-500">
                    <p>No daily data for this period</p>
                    <p class="text-sm mt-1">Track time to see your daily activity</p>
                </div>
            @endif
        </div>

        <!-- Popular Tags & Recent Logs -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Popular Tags</h2>
            
            @if(count($this->popularTags) > 0)
                <div class="space-y-3">
                    @foreach($this->popularTags as $tag)
                        <div class="flex justify-between items-center">
                            <span 
                                class="inline-block px-3 py-1 rounded-full" 
                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}"
                            >
                                {{ $tag->name }}
                            </span>
                            <div class="text-gray-600 text-sm">
                                {{ $tag->count }} uses
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-gray-500">
                    <p>No tags used in this period</p>
                </div>
            @endif
            
            <div class="border-t mt-6 pt-4">
                <h2 class="text-xl font-semibold mb-4">Recent Time Logs</h2>
                <div class="space-y-4">
                    @forelse($recentTimeLogs as $timeLog)
                        <div class="border-b pb-3">
                            <h3 class="font-medium">{{ $timeLog->project->name }}</h3>
                            @if($timeLog->description)
                                <p class="text-sm text-gray-600">{{ $timeLog->description }}</p>
                            @endif
                            <div class="flex justify-between items-center mt-1">
                                <p class="text-sm text-gray-600">{{ $this->formatDuration($timeLog->duration_minutes) }}</p>
                                <p class="text-xs text-gray-500">{{ $timeLog->start_time->format('M d, H:i') }}</p>
                            </div>
                            @if($timeLog->tags->count() > 0)
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($timeLog->tags as $tag)
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full" 
                                            style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">No time logs yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            function updateAllTimers() {
                const timerDisplays = document.querySelectorAll('.timer-display[data-start]');
                timerDisplays.forEach(display => {
                    const startTime = new Date(display.getAttribute('data-start'));
                    const now = new Date();
                    const diff = Math.floor((now - startTime) / 1000);
                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;
                    const displayText = 
                        (hours < 10 ? '0' + hours : hours) + ':' +
                        (minutes < 10 ? '0' + minutes : minutes) + ':' +
                        (seconds < 10 ? '0' + seconds : seconds);
                    display.textContent = displayText;
                });
            }
            updateAllTimers();
            setInterval(updateAllTimers, 1000);
        });
    </script>
</div>
