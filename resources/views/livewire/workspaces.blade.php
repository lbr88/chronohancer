<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">{{ __('Workspaces') }}</h1>
                    <flux:button wire:click="openCreateModal">
                        {{ __('Create Workspace') }}
                    </flux:button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-zinc-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Name') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Description') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Color') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Default') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($workspaces as $workspace)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="h-3 w-3 rounded-full mr-2" style="background-color: {{ $workspace->color }};"></span>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $workspace->name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $workspace->description ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $workspace->color }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($workspace->is_default)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                {{ __('Default') }}
                                            </span>
                                        @else
                                            <flux:button size="xs" wire:click="setAsDefault({{ $workspace->id }})">
                                                {{ __('Set as Default') }}
                                            </flux:button>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <flux:button size="xs" wire:click="openEditModal({{ $workspace->id }})">
                                            {{ __('Edit') }}
                                        </flux:button>
                                        <flux:button size="xs" wire:click="openDeleteModal({{ $workspace->id }})" :disabled="$workspace->is_default" class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                        {{ __('No workspaces found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $workspaces->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.workspaces.modals.create-workspace-modal')
    @include('livewire.workspaces.modals.edit-workspace-modal')
    @include('livewire.workspaces.modals.delete-confirmation-modal')
</div>
