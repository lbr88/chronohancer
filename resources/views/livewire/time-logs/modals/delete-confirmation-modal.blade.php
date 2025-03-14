@if($confirmingDelete)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Confirm Delete</h2>
        <p class="mb-4 dark:text-gray-300">Are you sure you want to delete this time log? This action cannot be undone.</p>
        <div class="flex justify-end space-x-3">
            <button type="button" wire:click="cancelDelete" class="px-4 py-2 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 dark:text-gray-300">
                Cancel
            </button>
            <button type="button" wire:click="deleteTimeLog({{ $confirmingDelete }})" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>
@endif