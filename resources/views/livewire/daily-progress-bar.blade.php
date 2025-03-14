<div class="max-w-7xl mx-auto px-4 py-2">
    <div class="flex items-center">
        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mr-3 whitespace-nowrap">
            {{ floor($totalDailyMinutes / 60) }}h {{ $totalDailyMinutes % 60 }}m / 7h24m
        </div>
        
        <div class="flex-grow relative h-2.5" x-data="{ showTooltip: false, tooltipContent: '', tooltipPosition: 0 }">
            <!-- Background -->
            <div class="absolute inset-0 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
            
            <!-- Progress -->
            <div class="absolute inset-y-0 left-0 bg-indigo-500 dark:bg-indigo-600 rounded-full" style="width: {{ $dailyProgressPercentage }}%"></div>
            
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
                    $minutes = $log->duration_minutes % 60;
                    $duration = ($hours > 0 ? $hours . 'h ' : '') . ($minutes > 0 ? $minutes . 'm' : '');
                    
                    // Format tooltip content
                    $projectName = $log->project ? $log->project->name : 'No Project';
                    $timerName = $log->timer ? $log->timer->name : 'No Timer';
                    $description = $log->description ?: 'No description';
                    $tooltipContent = "{$projectName} - {$timerName}: {$description} ({$duration})";
                @endphp
                
                <div
                    class="absolute inset-y-0 {{ $segmentColor }} opacity-90 hover:opacity-100 transition-opacity cursor-pointer rounded-full"
                    style="left: {{ $segmentLeft }}%; width: {{ $segmentWidth }}%;"
                    @mouseenter="showTooltip = true; tooltipContent = '{{ $tooltipContent }}'; tooltipPosition = $event.target.getBoundingClientRect().left + ($event.target.getBoundingClientRect().width / 2);"
                    @mouseleave="showTooltip = false"
                ></div>
            @endforeach
            
            <!-- Tooltip -->
            <div
                x-show="showTooltip"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="absolute bottom-full mb-1 px-2 py-1 bg-white dark:bg-gray-800 text-xs font-medium text-gray-900 dark:text-white rounded shadow-lg whitespace-nowrap z-10"
                :style="'left: ' + tooltipPosition + 'px; transform: translateX(-50%);'"
                x-text="tooltipContent"
            ></div>
        </div>
        
        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 ml-3 whitespace-nowrap">
            @if($dailyProgressPercentage < 100)
                {{ $remainingDailyTime }} remaining
            @else
                Completed!
            @endif
        </div>
    </div>
</div>
