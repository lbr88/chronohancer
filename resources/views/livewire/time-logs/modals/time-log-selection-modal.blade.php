@if($showTimeLogSelectionModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Select Time Log to Edit</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Multiple time logs found for this period. Please select one to edit:</p>
        
        <div class="space-y-2 max-h-80 overflow-y-auto mb-4">
            @foreach($timeLogSelectionOptions as $option)
                <button
                    wire:click="selectTimeLogToEdit({{ $option['id'] }})"
                    class="w-full text-left p-4 border dark:border-gray-700 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900 transition-colors dark:text-white"
                >
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-medium">{{ $option['description'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $option['start_time'] }} - {{ $option['end_time'] }}</div>
                        </div>
                        <div class="text-indigo-600 dark:text-indigo-400 font-medium">{{ $option['duration'] }}</div>
                    </div>
                </button>
            @endforeach
        </div>
        
        <div class="flex justify-end">
            <button
                wire:click="closeTimeLogSelectionModal"
                class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300"
            >
                Cancel
            </button>
        </div>
    </div>
</div>
@endif