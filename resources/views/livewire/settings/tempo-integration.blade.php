<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Tempo Integration')" :subheading="__('Configure Tempo API integration for syncing time logs')">
        <div class="my-6 w-full space-y-6">
            @if (session()->has('message'))
                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(!auth()->user()->hasJiraEnabled())
                <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/50 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">{{ __('Jira Integration Required') }}</h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-200">
                                <p>{{ __('You must connect your Jira account before enabling Tempo integration. This is required because Tempo needs your Jira account ID to create worklogs. We only use read access to get your account ID - nothing will be synced to Jira.') }}</p>
                                <a href="{{ route('settings.integrations.jira') }}" class="mt-2 inline-flex items-center text-yellow-800 dark:text-yellow-300 hover:underline">
                                    {{ __('Go to Jira Integration Settings') }}
                                    <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <label for="enabled" class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" id="enabled" wire:model.live="enabled" class="sr-only" {{ !auth()->user()->hasJiraEnabled() ? 'disabled' : '' }}>
                            <div class="block bg-gray-200 dark:bg-gray-700 w-14 h-8 rounded-full"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition {{ $enabled ? 'transform translate-x-6' : '' }}"></div>
                        </div>
                        <div class="ml-3 text-gray-700 dark:text-gray-300 font-medium">
                            {{ __('Enable Tempo Integration') }}
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <label for="readOnly" class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" id="readOnly" wire:model.live="readOnly" class="sr-only" {{ !$enabled ? 'disabled' : '' }}>
                        <div class="block bg-gray-200 dark:bg-gray-700 w-14 h-8 rounded-full"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition {{ $readOnly ? 'transform translate-x-6' : '' }}"></div>
                    </div>
                    <div class="ml-3 text-gray-700 dark:text-gray-300 font-medium">
                        {{ __('Read-Only Mode') }}
                    </div>
                </label>
            </div>

            @if($readOnly && $enabled)
                <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">{{ __('Read-only mode is enabled') }}</h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-200">
                                <p>{{ __('In read-only mode, you can view Tempo worklog information but cannot sync new time logs to Tempo.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">
                <div class="flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-4">
                            <button type="button" wire:click="testConnection" class="ch-btn-secondary">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                {{ __('Test Connection') }}
                            </button>

                            @if(auth()->user()->tempo_access_token)
                                <button type="button" wire:click="disconnect" class="ch-btn-danger">
                                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('Disconnect Tempo') }}
                                </button>
                            @endif
                        </div>

                        @if($testStatus)
                            <span class="{{ $testStatus === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $testMessage }}
                            </span>
                        @endif
                    </div>

                    <button type="submit" class="ch-btn-primary">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Save Settings') }}
                    </button>
                </div>

                @if(auth()->user()->hasJiraEnabled())
                    <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                        <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-white">{{ __('Account Connection') }}</h3>
                        
                        @if(auth()->user()->hasTempoEnabled())
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Connected to Tempo') }}</span>
                                </div>
                                <button type="button" wire:click="disconnect" class="ch-btn-danger">
                                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('Disconnect') }}
                                </button>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Not connected to Tempo') }}</span>
                                <button type="button" wire:click="connect" class="ch-btn-primary inline-flex items-center">
                                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('Connect to Tempo') }}
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </form>

            <div class="mt-8 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                <h3 class="mb-3 text-lg font-medium text-zinc-900 dark:text-white">{{ __('Connect to Tempo') }}</h3>
                
                <div class="prose dark:prose-invert prose-sm max-w-none">
                    <p>{{ __('To sync your time logs with Tempo:') }}</p>
                    <ol>
                        <li>{{ __('Click the "Connect to Tempo" button above') }}</li>
                        <li>{{ __('You will be redirected to Tempo to authorize access') }}</li>
                        <li>{{ __('After authorizing, you will be redirected back and can start syncing time logs') }}</li>
                    </ol>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Note: This will only allow access to your own Tempo data and worklogs.') }}</p>
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>