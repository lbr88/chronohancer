@if($confirmingBulkDelete)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Confirm Bulk Delete</h2>
        <p class="mb-4 dark:text-gray-300">Are you sure you want to delete {{ count($selectedTimeLogs) }} selected time {{ count($selectedTimeLogs) === 1 ? 'log' : 'logs' }}? This action cannot be undone.</p>
        <div class="flex justify-end space-x-3">
            <button type="button" wire:click="cancelBulkDelete" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
                Cancel
            </button>
            <button type="button" wire:click="bulkDeleteTimeLogs" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Delete Selected
            </button>
        </div>
    </div>
</div>
@endif