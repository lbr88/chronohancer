<div>
    <!-- Tempo Sync Button -->
    <button
        wire:click="openSyncModal"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        {{ !$tempoConfigured ? 'disabled' : '' }}
        title="{{ !$tempoConfigured ? 'Tempo integration is not configured' : ($isReadOnly ? 'View Tempo sync status (read-only mode)' : 'Sync time logs to Tempo') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
        {{ $isReadOnly ? 'View Tempo Status' : 'Sync to Tempo' }}
    </button>

    <!-- Tempo Sync Modal -->
    <div x-data="{ show: @entangle('showSyncModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ $isReadOnly ? 'Tempo Sync Status' : 'Sync Time Logs to Tempo' }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    @if($isReadOnly)
                                        Tempo integration is in read-only mode. You can view sync status but cannot sync new time logs.
                                    @else
                                        This will sync your time logs to Tempo. Only time logs that haven't been synced yet will be processed.
                                    @endif
                                </p>
                                
                                @if($isReadOnly)
                                    <div class="mt-2 rounded-md bg-yellow-50 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">Read-only mode is enabled</h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>
                                                        To enable syncing, set <code>TEMPO_READ_ONLY=false</code> in your .env file.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($syncStatus === 'idle')
                                <div class="mt-4 space-y-4">
                                    <div class="flex items-center">
                                        <input id="sync-all" type="checkbox" wire:model.live="syncAll" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="sync-all" class="ml-2 block text-sm text-gray-900">
                                            Sync all time logs
                                        </label>
                                    </div>

                                    @if (!$syncAll)
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="date-from" class="block text-sm font-medium text-gray-700">From</label>
                                                <input type="date" id="date-from" wire:model="dateFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </div>
                                            <div>
                                                <label for="date-to" class="block text-sm font-medium text-gray-700">To</label>
                                                <input type="date" id="date-to" wire:model="dateTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="bg-gray-50 p-3 rounded-md">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-700">Time logs to sync:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $logsToSync }}</span>
                                        </div>
                                        <div class="flex justify-between mt-1">
                                            <span class="text-sm text-gray-700">Already synced:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $syncedLogs }}</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($syncStatus === 'syncing')
                                <div class="mt-4">
                                    <div class="text-sm text-gray-700 mb-2">
                                        Syncing time logs to Tempo... ({{ $processedLogs }} of {{ $totalLogs }})
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $syncProgress }}%"></div>
                                    </div>
                                </div>
                            @elseif ($syncStatus === 'completed')
                                <div class="mt-4">
                                    <div class="rounded-md {{ $syncResults['failed'] > 0 ? 'bg-yellow-50 p-4' : 'bg-green-50 p-4' }}">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                @if ($syncResults['failed'] > 0)
                                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium {{ $syncResults['failed'] > 0 ? 'text-yellow-800' : 'text-green-800' }}">
                                                    {{ $syncResults['message'] }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>

                                    @if (count($syncErrors) > 0)
                                        <div class="mt-4">
                                            <h4 class="text-sm font-medium text-gray-900">Errors:</h4>
                                            <div class="mt-2 max-h-40 overflow-y-auto">
                                                <ul class="divide-y divide-gray-200">
                                                    @foreach ($syncErrors as $error)
                                                        <li class="py-2">
                                                            <div class="text-sm text-gray-900">{{ $error['date'] }} - {{ $error['description'] }}</div>
                                                            <div class="text-xs text-red-600">{{ $error['message'] }}</div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    @if ($syncStatus === 'idle')
                        <button
                            wire:click="startSync"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                            {{ $logsToSync === 0 || $isReadOnly ? 'disabled' : '' }}
                            title="{{ $isReadOnly ? 'Syncing is disabled in read-only mode' : ($logsToSync === 0 ? 'No time logs to sync' : 'Start syncing time logs to Tempo') }}"
                        >
                            {{ $isReadOnly ? 'Syncing Disabled' : 'Start Sync' }}
                        </button>
                    @elseif ($syncStatus === 'completed')
                        <button wire:click="closeSyncModal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    @endif
                    
                    @if ($syncStatus === 'idle')
                        <button wire:click="closeSyncModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>