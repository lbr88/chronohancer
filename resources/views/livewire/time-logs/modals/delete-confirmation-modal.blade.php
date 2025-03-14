@if($confirmingDelete)
<div class="ch-modal-overlay">
    <div class="ch-modal-container">
        <div class="ch-modal-header">
            <h2 class="ch-modal-title">Confirm Delete</h2>
        </div>
        <p class="mb-4 dark:text-gray-300">Are you sure you want to delete this time log? This action cannot be undone.</p>
        <div class="ch-modal-footer">
            <button type="button" wire:click="cancelDelete" class="ch-btn-secondary">
                Cancel
            </button>
            <button type="button" wire:click="deleteTimeLog({{ $confirmingDelete }})" class="ch-btn-danger">
                Delete
            </button>
        </div>
    </div>
</div>
@endif