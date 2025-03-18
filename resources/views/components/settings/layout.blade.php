<div class="flex items-start max-md:flex-col">
    <div class="mr-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>

        <flux:navlist.group :heading="__('Integrations')" class="mt-4">
            @if(env('TEMPO_CLIENT_ID') && env('TEMPO_CLIENT_SECRET'))
            <flux:navlist.item :href="route('settings.integrations.tempo')" :current="request()->routeIs('settings.integrations.tempo')" wire:navigate>{{ __('Tempo') }}</flux:navlist.item>
            @endif

            @if(env('JIRA_CLIENT_ID') && env('JIRA_CLIENT_SECRET'))
            <flux:navlist.item :href="route('settings.integrations.jira')" :current="request()->routeIs('settings.integrations.jira')" wire:navigate>{{ __('Jira') }}</flux:navlist.item>
            @endif

            @if(env('MICROSOFT_CLIENT_ID') && env('MICROSOFT_CLIENT_SECRET'))
            <flux:navlist.item :href="route('settings.integrations.microsoft-calendar')" :current="request()->routeIs('settings.integrations.microsoft-calendar')" wire:navigate>{{ __('Calendar') }}</flux:navlist.item>
            @endif
        </flux:navlist.group>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>