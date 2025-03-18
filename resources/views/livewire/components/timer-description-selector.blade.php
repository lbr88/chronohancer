<div class="relative" x-data="{ open: @entangle('showDropdown') }" @click.away="$wire.closeDropdown()">
    <div class="relative">
        <textarea
            wire:model.live="description"
            wire:click="toggleDropdown"
            placeholder="{{ $timerId ? 'Select or create a description' : 'Enter a description or select from recent' }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2"
            rows="2"></textarea>
        <div class="absolute top-3 right-0 flex items-center pr-3 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    @if($showDropdown)
    <div class="absolute z-10 mt-1 w-full bg-white dark:bg-zinc-800 shadow-lg rounded-md py-1 max-h-60 overflow-auto">
        @if($descriptions->isEmpty())
        <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
            No descriptions found
        </div>
        @else
        @foreach($descriptions as $desc)
        <div
            wire:key="description-{{ $desc->id }}"
            wire:click="selectDescription({{ $desc->id }}, '{{ addslashes($desc->description) }}')"
            class="px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer">
            <div class="font-medium text-gray-900 dark:text-white">
                {{ \Illuminate\Support\Str::limit($desc->description, 100) }}
            </div>
            <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                @if($desc->timer && $desc->timer->name)
                <span class="mr-2 font-medium">{{ \Illuminate\Support\Str::limit($desc->timer->name, 30) }}</span>
                @endif
                <span>{{ $desc->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @endforeach
        @endif

        @if($createNewDescription)
        <div
            wire:click="createDescription"
            class="px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer border-t border-gray-200 dark:border-gray-700">
            <div class="font-medium text-indigo-600 dark:text-indigo-400">
                Create new description
            </div>
        </div>
        @endif
    </div>
    @endif

    <input type="hidden" name="timer_description_id" wire:model="timerDescriptionId">
</div>