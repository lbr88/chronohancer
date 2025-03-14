<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center" style="display: {{ $showEditModal ? 'flex' : 'none' }}">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Edit Workspace') }}</h3>
            <button wire:click="$set('showEditModal', false)" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form wire:submit.prevent="updateWorkspace" class="space-y-4">
            <!-- Workspace Name -->
            <div>
                <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
                <input
                    type="text"
                    id="edit-name"
                    wire:model="form.name"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                >
                @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Description -->
            <div>
                <label for="edit-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }}</label>
                <textarea
                    id="edit-description"
                    wire:model="form.description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                ></textarea>
                @error('form.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Color -->
            <div>
                <label for="edit-color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Color') }}</label>
                <div class="flex items-center space-x-3 mt-1">
                    <input
                        type="color"
                        wire:model.live="form.color"
                        id="edit-color"
                        class="h-10 w-10 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer"
                    >
                    <div class="flex-1">
                        <input
                            type="text"
                            wire:model="form.color"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                            placeholder="#RRGGBB"
                        >
                    </div>
                </div>
                <div class="mt-2 flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Preview:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm"
                        style="background-color: {{ $form['color'] }}; color: {{ $form['color'] ? (hexdec(substr($form['color'], 1, 2)) * 0.299 + hexdec(substr($form['color'], 3, 2)) * 0.587 + hexdec(substr($form['color'], 5, 2)) * 0.114) > 186 ? '#000000' : '#ffffff' : '#ffffff' }}">
                        {{ $form['name'] ?: 'Sample Workspace' }}
                    </span>
                </div>
                @error('form.color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Daily Target Time -->
            <div>
                <label for="edit-daily-target-time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Daily Target Time') }}</label>
                <div class="flex items-center space-x-3 mt-1">
                    <input
                        type="text"
                        id="edit-daily-target-time"
                        wire:model.debounce.500ms="dailyTargetTime"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                        placeholder="7h 24m (or 0h 0m for no target)"
                    >
                </div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Format: 7h 24m (hours and minutes)') }}
                </div>
                @error('form.daily_target_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Weekly Target Time -->
            <div>
                <label for="edit-weekly-target-time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Weekly Target Time') }}</label>
                <div class="flex items-center space-x-3 mt-1">
                    <input
                        type="text"
                        id="edit-weekly-target-time"
                        wire:model.debounce.500ms="weeklyTargetTime"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
                        placeholder="37h (or 0h for no target)"
                    >
                </div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Format: 37h 30m (hours and minutes)') }}
                </div>
                @error('form.weekly_target_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <!-- Default Workspace Checkbox -->
            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="edit-is-default"
                    wire:model="form.is_default"
                    @if($form['is_default']) disabled @endif
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                >
                <label for="edit-is-default" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    {{ __('Default Workspace') }}
                </label>
                @error('form.is_default') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="mt-5 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="$set('showEditModal', false)"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    {{ __('Cancel') }}
                </button>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    {{ __('Update') }}
                </button>
            </div>
        </form>
    </div>
</div>