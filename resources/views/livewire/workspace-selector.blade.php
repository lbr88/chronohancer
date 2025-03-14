<div class="relative">
    <flux:dropdown position="bottom" align="start">
        <button
            type="button"
            class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700"
        >
            @if($currentWorkspaceId && count($workspaces) > 0)
                @php
                    $currentWorkspace = $workspaces->firstWhere('id', $currentWorkspaceId);
                @endphp
                @if($currentWorkspace)
                    <span class="flex h-2.5 w-2.5 rounded-full" style="background-color: {{ $currentWorkspace->color }};"></span>
                    <span>{{ $currentWorkspace->name }}</span>
                @else
                    <span>{{ __('Select Workspace') }}</span>
                @endif
            @else
                <span>{{ __('Select Workspace') }}</span>
            @endif
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-gray-400">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <flux:menu>
            <div class="px-1 py-1">
                @forelse($workspaces as $workspace)
                    <flux:menu.item
                        wire:click="switchWorkspace({{ $workspace->id }})"
                        :active="$currentWorkspaceId === $workspace->id"
                    >
                        <div class="flex items-center">
                            <span class="mr-2 h-2.5 w-2.5 rounded-full" style="background-color: {{ $workspace->color }};"></span>
                            <span>{{ $workspace->name }}</span>
                            @if($workspace->is_default)
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">({{ __('Default') }})</span>
                            @endif
                        </div>
                    </flux:menu.item>
                @empty
                    <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No workspaces found') }}
                    </div>
                @endforelse
            </div>
            
            <flux:menu.separator />
            
            <div class="px-1 py-1">
                <flux:menu.item href="{{ route('workspaces') }}" wire:navigate>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-2 h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ __('Manage Workspaces') }}</span>
                    </div>
                </flux:menu.item>
            </div>
        </flux:menu>
    </flux:dropdown>
</div>
