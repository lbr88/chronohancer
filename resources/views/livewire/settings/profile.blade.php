<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.navigation />

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="ch-form-group">
                <label for="name" class="ch-label">{{ __('Name') }}</label>
                <input type="text" id="name" wire:model="name" class="ch-input" required autofocus autocomplete="name">
            </div>

            <div class="ch-form-group">
                <label for="email" class="ch-label">{{ __('Email') }}</label>
                <input type="email" id="email" wire:model="email" class="ch-input" required autocomplete="email">

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Your email address is unverified.') }}

                        <button type="button" wire:click.prevent="resendVerificationNotification" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-green-600 dark:text-green-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="ch-btn-primary w-full">{{ __('Save') }}</button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
