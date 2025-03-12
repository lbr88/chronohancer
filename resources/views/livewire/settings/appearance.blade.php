<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div class="space-y-8">
            <!-- Theme Selection -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">{{ __('Theme') }}</h3>
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>
            
            <!-- Time Format Selection -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">{{ __('Time Format') }}</h3>
                
                <div class="space-y-4">
                    <flux:radio.group variant="segmented" wire:model.live="time_format">
                        <flux:radio value="human" icon="clock">{{ __('1h 30m') }}</flux:radio>
                        <flux:radio value="hm" icon="clock">{{ __('01:30') }}</flux:radio>
                        <flux:radio value="hms" icon="clock">{{ __('01:30:45') }}</flux:radio>
                    </flux:radio.group>
                    
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('This setting will be used for all timer displays throughout the application.') }}
                    </p>
                    
                    <x-action-message class="text-sm text-green-600 dark:text-green-400" on="appearance-updated">
                        {{ __('Time format preference saved.') }}
                    </x-action-message>
                </div>
            </div>
        </div>
    </x-settings.layout>
</div>
