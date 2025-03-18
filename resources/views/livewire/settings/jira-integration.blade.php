<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.navigation />

    @if (session('error'))
    <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/50 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if (session('success'))
    <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/50 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <x-settings.layout :heading="__('Jira Integration')" :subheading="__('Connect your Jira account to get your account ID for Tempo time logs')">
        <div class="my-6 w-full space-y-6">
            @if($isConnected)
            <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/50">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Connected to Jira</h3>
                        <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                            <p>Your Jira account is connected. We only use read access to get your account ID for Tempo time logs.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <label for="jiraEnabled" class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" id="jiraEnabled" wire:model.live="jiraEnabled" class="sr-only">
                            <div class="block bg-gray-200 dark:bg-gray-700 w-14 h-8 rounded-full"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition {{ $jiraEnabled ? 'transform translate-x-6' : '' }}"></div>
                        </div>
                        <div class="ml-3 text-gray-700 dark:text-gray-300 font-medium">
                            {{ __('Enable Jira Integration') }}
                        </div>
                    </label>
                </div>
                <button wire:click="disconnect" type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    {{ __('Disconnect') }}
                </button>
            </div>

            <div class="ch-form-group">
                <label class="ch-label">{{ __('Connected Site') }}</label>
                <div class="ch-input bg-gray-50 dark:bg-gray-800">
                    {{ $jiraSiteUrl }}
                </div>
            </div>
            @else
            <div class="ch-form-group">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Connect to Jira') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Connect your Jira account to get your account ID for Tempo time logs. We only need read access to your Jira account.') }}
                        </p>
                    </div>
                    <button wire:click="connect" type="button" class="ch-btn-primary">
                        {{ __('Connect') }}
                    </button>
                </div>
            </div>
            @endif
        </div>
    </x-settings.layout>
</section>