<div class="max-w-7xl mx-auto px-4 py-2 dark:bg-transparent dark:text-white"
    x-data="{
        totalMinutes: {{ $totalDailyMinutes }},
        percentage: {{ $dailyProgressPercentage }},
        remainingTime: '{{ $remainingDailyTime }}',
        activeTimers: {{ json_encode($activeTimers) }},
        startTimes: {},
        showTooltip: false,
        tooltipContent: '',
        tooltipPosition: 0,
        
        init() {
            // Initialize start times for active timers
            this.activeTimers.forEach(timer => {
                this.startTimes[timer.id] = new Date(timer.start_time);
            });
            
            // Start the timer update interval
            if (this.activeTimers.length > 0) {
                this.startTimerUpdates();
            }
            
            // Listen for Livewire events
            window.addEventListener('dailyProgressUpdated', (event) => {
                console.log('dailyProgressUpdated event received:', event.detail);
                
                this.totalMinutes = event.detail.totalMinutes || 0;
                this.percentage = event.detail.percentage || 0;
                this.remainingTime = event.detail.remainingTime || '0m';
                
                // Make sure activeTimers is an array, even if it's undefined in the event
                const activeTimers = Array.isArray(event.detail.activeTimers) ? event.detail.activeTimers : [];
                
                // Reset start times for new timers
                activeTimers.forEach(timer => {
                    if (!this.startTimes[timer.id]) {
                        this.startTimes[timer.id] = new Date(timer.start_time);
                    }
                });
                
                this.activeTimers = activeTimers;
                
                // Start or stop timer updates based on whether we have active timers
                if (this.activeTimers.length > 0 && !this.updateInterval) {
                    this.startTimerUpdates();
                } else if (this.activeTimers.length === 0 && this.updateInterval) {
                    this.stopTimerUpdates();
                }
            });
        },
        
        startTimerUpdates() {
            this.updateInterval = setInterval(() => {
                let additionalMinutes = 0;
                
                // Calculate current duration for each active timer
                this.activeTimers.forEach(timer => {
                    const startTime = this.startTimes[timer.id];
                    if (startTime) {
                        const now = new Date();
                        const diffMs = Math.floor(now - startTime);
                        const diffMinutes = Math.floor(diffMs / 60000);
                        additionalMinutes += Math.floor(diffMinutes);
                    }
                });
                
                // Calculate completed minutes (from server) + active minutes (calculated live)
                const completedMinutes = {{ $totalDailyMinutes - collect($activeTimers)->sum('current_duration') }};
                const totalMinutes = Math.floor(completedMinutes + additionalMinutes);
                
                // Update the display
                this.totalMinutes = totalMinutes;
                this.percentage = Math.min(100, Math.round((totalMinutes / {{ $requiredMinutes }}) * 100));
                
                // Update remaining time
                const remainingMinutes = Math.max(0, {{ $requiredMinutes }} - totalMinutes);
                const hours = Math.floor(remainingMinutes / 60);
                const minutes = Math.floor(remainingMinutes % 60);
                
                if (hours > 0 && minutes > 0) {
                    this.remainingTime = `${hours}h ${minutes}m`;
                } else if (hours > 0) {
                    this.remainingTime = `${hours}h`;
                } else {
                    this.remainingTime = `${minutes}m`;
                }
            }, 1000); // Update every second
        },
        
        stopTimerUpdates() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
                this.updateInterval = null;
            }
        },
        
        formatTime(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = Math.floor(minutes % 60);
            return `${hours}h ${mins}m`;
        }
    }"
    x-init="init()"
    @disconnect="stopTimerUpdates()">
    <div class="flex items-center dark:bg-transparent">
        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mr-3 whitespace-nowrap">
            <span x-text="Math.floor(totalMinutes / 60) + 'h ' + Math.floor(totalMinutes % 60) + 'm'"></span> / 7h24m
        </div>

        <div class="flex-grow relative h-2.5 dark:bg-transparent">
            <!-- Background -->
            <div class="absolute inset-0 bg-gray-200 dark:bg-zinc-700 rounded-full"></div>

            <!-- Progress -->
            <div class="absolute inset-y-0 left-0 bg-indigo-500 dark:bg-indigo-500 rounded-full transition-all duration-1000 ease-linear"
                :style="'width: ' + percentage + '%'"></div>

            <!-- Time log segments -->
            @php
            $totalWidth = 0;
            $segmentColors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500', 'bg-pink-500', 'bg-teal-500', 'bg-orange-500', 'bg-red-500'];
            @endphp

            @foreach($dailyTimeLogs as $index => $log)
            @php
            $segmentWidth = ($log->duration_minutes / $requiredMinutes) * 100;
            $segmentLeft = $totalWidth;
            $totalWidth += $segmentWidth;
            $colorIndex = $index % count($segmentColors);
            $segmentColor = $segmentColors[$colorIndex];

            // Format time for tooltip
            $hours = floor($log->duration_minutes / 60);
            $minutes = floor($log->duration_minutes % 60);
            $duration = ($hours > 0 ? $hours . 'h ' : '') . ($minutes > 0 ? $minutes . 'm' : '');

            // Format tooltip content
            $defaultProject = App\Models\Project::findOrCreateDefault(auth()->id(), app('current.workspace')->id);
            $projectName = $log->timer->project->name;
            $timerName = $log->timer ? $log->timer->name : 'No Timer';
            $description = $log->timer->description ?: 'No description';
            $tooltipContent = "{$projectName} - {$timerName}: {$description} ({$duration})";
            @endphp

            <div
                class="absolute inset-y-0 {{ $segmentColor }} opacity-90 hover:opacity-100 transition-opacity cursor-pointer {{ $index === 0 ? 'rounded-l-full' : '' }} {{ $index === count($dailyTimeLogs) - 1 ? 'rounded-r-full' : '' }}"
                style="left: {{ $segmentLeft > 100 ? 100 : $segmentLeft }}%; width: {{ $segmentWidth > (100 - $segmentLeft) ? (100 - $segmentLeft) : $segmentWidth }}%;"
                @mouseenter="showTooltip = true; tooltipContent = '{{ $tooltipContent }}'; tooltipPosition = $event.offsetX + ($event.target.offsetWidth / 2);"
                @mouseleave="showTooltip = false"></div>
            @endforeach

            <!-- Active timer segment (pulsing) -->
            @if(count($activeTimers) > 0)
            @php
            $activeTimerWidth = (collect($activeTimers)->sum('current_duration') / $requiredMinutes) * 100;
            $activeTimerLeft = $totalWidth;
            @endphp
            <div
                class="absolute inset-y-0 bg-red-500 opacity-90 transition-opacity cursor-pointer animate-pulse rounded-r-full"
                style="left: {{ $activeTimerLeft > 100 ? 100 : $activeTimerLeft }}%; width: {{ $activeTimerWidth > (100 - $activeTimerLeft) ? (100 - $activeTimerLeft) : $activeTimerWidth }}%;"
                @mouseenter="showTooltip = true; tooltipContent = 'Active timer(s)'; tooltipPosition = $event.offsetX + ($event.target.offsetWidth / 2);"
                @mouseleave="showTooltip = false"></div>
            @endif

            <!-- Tooltip -->
            <div
                x-show="showTooltip"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="absolute bottom-full mb-1 px-2 py-1 bg-white dark:bg-zinc-800 text-xs font-medium text-gray-900 dark:text-white rounded shadow-lg whitespace-nowrap z-10"
                :style="'left: ' + tooltipPosition + 'px; transform: translateX(-50%);'"
                x-text="tooltipContent"></div>
        </div>

        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 ml-3 whitespace-nowrap">
            <template x-if="percentage < 100">
                <span x-text="remainingTime + ' remaining'"></span>
            </template>
            <template x-if="percentage >= 100">
                <span>Completed!</span>
            </template>
        </div>
    </div>
</div>