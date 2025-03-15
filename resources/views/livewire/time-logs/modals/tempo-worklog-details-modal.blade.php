<div x-data="{ show: @entangle('showTempoWorklogDetailsModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Tempo Worklog Details
                        </h3>
                        <div class="mt-4">
                            @if($tempoWorklogDetails)
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Worklog ID</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $tempoWorklogDetails['tempoWorklogId'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Issue</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            @if(isset($tempoWorklogDetails['issue']))
                                                {{ $tempoWorklogDetails['issue']['key'] ?? 'N/A' }} - {{ $tempoWorklogDetails['issue']['summary'] ?? 'N/A' }}
                                            @else
                                                No issue linked
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Author</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $tempoWorklogDetails['author']['displayName'] ?? 'N/A' }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $tempoWorklogDetails['description'] ?? 'No description' }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Time Spent</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            @php
                                                $seconds = $tempoWorklogDetails['timeSpentSeconds'] ?? 0;
                                                $hours = floor($seconds / 3600);
                                                $minutes = floor(($seconds % 3600) / 60);
                                                $timeSpent = '';
                                                if ($hours > 0) {
                                                    $timeSpent .= $hours . 'h ';
                                                }
                                                if ($minutes > 0 || $hours === 0) {
                                                    $timeSpent .= $minutes . 'm';
                                                }
                                            @endphp
                                            {{ $timeSpent }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $tempoWorklogDetails['startDate'] ?? 'N/A' }} {{ $tempoWorklogDetails['startTime'] ?? '' }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ isset($tempoWorklogDetails['createdAt']) ? \Carbon\Carbon::parse($tempoWorklogDetails['createdAt'])->format('M d, Y H:i') : 'N/A' }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ isset($tempoWorklogDetails['updatedAt']) ? \Carbon\Carbon::parse($tempoWorklogDetails['updatedAt'])->format('M d, Y H:i') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-gray-500 dark:text-gray-400">Unable to load Tempo worklog details.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="closeTempoWorklogDetailsModal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>