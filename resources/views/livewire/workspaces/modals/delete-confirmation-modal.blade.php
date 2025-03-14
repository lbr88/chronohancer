<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center" style="display: {{ $showDeleteModal ? 'flex' : 'none' }}">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Delete Workspace') }}</h3>
            <button wire:click="$set('showDeleteModal', false)" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="space-y-4">
            <p class="text-gray-700 dark:text-gray-300">{{ __('Are you sure you want to delete this workspace?') }}</p>
            <p class="font-semibold text-gray-900 dark:text-white">{{ $workspaceToDelete?->name }}</p>
            
            @if($isDefaultWorkspace)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 dark:bg-yellow-900/50 dark:border-yellow-600">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400 dark:text-yellow-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                {{ __('You cannot delete your default workspace. Please set another workspace as default first.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-red-50 border-l-4 border-red-400 p-4 dark:bg-red-900/50 dark:border-red-600">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400 dark:text-red-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-200">
                                {{ __('This action cannot be undone. All projects, timers, tags, and time logs in this workspace will be permanently deleted.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="mt-5 flex justify-end space-x-3">
            <button
                type="button"
                wire:click="$set('showDeleteModal', false)"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                {{ __('Cancel') }}
            </button>
            <button
                type="button"
                wire:click="deleteWorkspace"
                wire:loading.attr="disabled"
                @if($isDefaultWorkspace) disabled @endif
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ __('Delete') }}
            </button>
        </div>
    </div>
</div>