<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <a href="{{ route('dashboard') }}" class="ml-2 mr-3 flex items-center space-x-2 lg:ml-0" wire:navigate>
            <x-app-logo />
        </a>

        <!-- Workspace Selector -->
        @livewire('workspace-selector')

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navbar.item>

            <flux:navbar.item icon="clock" :href="route('timers')" :current="request()->routeIs('timers')" wire:navigate>
                {{ __('Timers') }}
            </flux:navbar.item>

            <flux:navbar.item icon="calendar" :href="route('time-logs')" :current="request()->routeIs('time-logs')" wire:navigate>
                {{ __('Time Logs') }}
            </flux:navbar.item>

            <flux:navbar.item icon="folder" :href="route('projects')" :current="request()->routeIs('projects')" wire:navigate>
                {{ __('Projects') }}
            </flux:navbar.item>

            <flux:navbar.item icon="tag" :href="route('tags')" :current="request()->routeIs('tags')" wire:navigate>
                {{ __('Tags') }}
            </flux:navbar.item>

            <flux:navbar.item icon="briefcase" :href="route('workspaces')" :current="request()->routeIs('workspaces')" wire:navigate>
                {{ __('Workspaces') }}
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="mr-1.5 space-x-0.5 py-0!">
            <flux:tooltip :content="__('Search')" position="bottom">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Repository')" position="bottom">
                <flux:navbar.item
                    class="h-10 max-lg:hidden [&>div>svg]:size-5"
                    icon="folder-git-2"
                    href="https://github.com/lbr88/chronohancer"
                    target="_blank"
                    :label="__('Repository')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Documentation')" position="bottom">
                <flux:navbar.item
                    class="h-10 max-lg:hidden [&>div>svg]:size-5"
                    icon="book-open-text"
                    href="https://github.com/lbr88/chronohancer"
                    target="_blank"
                    label="Documentation" />
            </flux:tooltip>
        </flux:navbar>

        <!-- Desktop User Menu -->
        <flux:dropdown position="top" align="end">
            <flux:profile
                class="cursor-pointer"
                :initials="auth()->user()->initials()" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar stashable sticky class="lg:hidden border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="ml-1 flex items-center space-x-2" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')">
                <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Time Tracking')">
                <flux:navlist.item icon="clock" :href="route('timers')" :current="request()->routeIs('timers')" wire:navigate>
                    {{ __('Timers') }}
                </flux:navlist.item>

                <flux:navlist.item icon="calendar" :href="route('time-logs')" :current="request()->routeIs('time-logs')" wire:navigate>
                    {{ __('Time Logs') }}
                </flux:navlist.item>

                <flux:navlist.item icon="folder" :href="route('projects')" :current="request()->routeIs('projects')" wire:navigate>
                    {{ __('Projects') }}
                </flux:navlist.item>

                <flux:navlist.item icon="tag" :href="route('tags')" :current="request()->routeIs('tags')" wire:navigate>
                    {{ __('Tags') }}
                </flux:navlist.item>

                <flux:navlist.item icon="briefcase" :href="route('workspaces')" :current="request()->routeIs('workspaces')" wire:navigate>
                    {{ __('Workspaces') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/lbr88/chronohancer" target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://github.com/lbr88/chronohancer" target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>
    </flux:sidebar>

    <!-- Daily Progress Bar with absolute positioning -->
    @php
    $workspace = app('current.workspace');
    $showProgressBar = $workspace && $workspace->daily_target_minutes > 0;
    @endphp

    @if($showProgressBar)
    <div style="position: absolute; top: 56px; left: 0; right: 0; z-index: 10; height: 30px; border-bottom: 1px;" class="dark:bg-zinc-900 dark:border-zinc-700">
        @livewire('daily-progress-bar')
    </div>
    <div style="padding-top: 30px;">
        @else
        <div>
            @endif
            {{ $slot }}
        </div>

        @fluxScripts
        <x-notification />
</body>

</html>