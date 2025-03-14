@if($confirmingBulkDelete)
<div class="ch-modal-overlay">
    <div class="ch-modal-container">
        <div class="ch-modal-header">
            <h2 class="ch-modal-title">Confirm Bulk Delete</h2>
        </div>
        <p class="mb-4 dark:text-gray-300">Are you sure you want to delete {{ count($selectedTimeLogs) }} selected time {{ count($selectedTimeLogs) === 1 ? 'log' : 'logs' }}? This action cannot be undone.</p>
        <div class="ch-modal-footer">
            <button type="button" wire:click="cancelBulkDelete" class="ch-btn-secondary">
                Cancel
            </button>
            <button type="button" wire:click="bulkDeleteTimeLogs" class="ch-btn-danger">
                Delete Selected
            </button>
        </div>
    </div>
</div>
@endif