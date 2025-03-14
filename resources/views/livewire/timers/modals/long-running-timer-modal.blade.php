@if($showLongRunningTimerModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Unusually Long Timer Detected</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                This timer has been running for a long time (8+ hours or since yesterday).
                How would you like to handle the end time?
            </p>
        </div>
        
        <div class="space-y-4">
            <!-- Option 1: Custom End Time -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <label class="flex items-center">
                    <input type="radio" name="timerOption" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" checked>
                    <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Specify a custom end time</span>
                </label>
                <div class="mt-3">
                    <input
                        type="datetime-local"
                        wire:model="customEndTime"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm px-3 py-2"
                    >
                    <button
                        wire:click="useCustomEndTime"
                        class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Use Custom Time
                    </button>
                </div>
            </div>
            
            <!-- Option 2: Actual Hours Worked -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <label class="flex items-center">
                    <input type="radio" name="timerOption" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                    <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Enter actual hours worked</span>
                </label>
                <div class="mt-3">
                    <input
                        type="number"
                        wire:model="actualHoursWorked"
                        step="0.25"
                        min="0.25"
                        placeholder="Hours worked"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm px-3 py-2"
                    >
                    <button
                        wire:click="useActualHoursWorked"
                        class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Use Actual Hours
                    </button>
                </div>
            </div>
            
            <!-- Option 3: Current Time -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <label class="flex items-center">
                    <input type="radio" name="timerOption" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                    <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Use current time (now)</span>
                </label>
                <div class="mt-3">
                    <button
                        wire:click="useCurrentTime"
                        class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Use Current Time
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-6 flex justify-end">
            <button
                wire:click="cancelLongRunningTimerStop"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Cancel
            </button>
        </div>
    </div>
</div>
@endif