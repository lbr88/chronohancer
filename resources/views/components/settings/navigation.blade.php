<div class="mb-6 flex space-x-4 border-b border-zinc-200 dark:border-zinc-700">
    <a href="{{ route('settings.profile') }}" @class([ 'border-b-2 px-1 pb-4 text-sm font-medium' , 'border-primary-600 text-primary-600'=> request()->routeIs('settings.profile'),
        'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => !request()->routeIs('settings.profile'),
        ])>
        {{ __('Profile') }}
    </a>

    <a href="{{ route('settings.appearance') }}" @class([ 'border-b-2 px-1 pb-4 text-sm font-medium' , 'border-primary-600 text-primary-600'=> request()->routeIs('settings.appearance'),
        'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => !request()->routeIs('settings.appearance'),
        ])>
        {{ __('Appearance') }}
    </a>

    <a href="{{ route('settings.password') }}" @class([ 'border-b-2 px-1 pb-4 text-sm font-medium' , 'border-primary-600 text-primary-600'=> request()->routeIs('settings.password'),
        'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => !request()->routeIs('settings.password'),
        ])>
        {{ __('Password') }}
    </a>

    @if(env('TEMPO_CLIENT_ID') && env('TEMPO_CLIENT_SECRET'))
    <a href="{{ route('settings.integrations.tempo') }}" @class([ 'border-b-2 px-1 pb-4 text-sm font-medium' , 'border-primary-600 text-primary-600'=> request()->routeIs('settings.integrations.tempo'),
        'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => !request()->routeIs('settings.integrations.tempo'),
        ])>
        {{ __('Tempo') }}
    </a>
    @endif

    @if(env('JIRA_CLIENT_ID') && env('JIRA_CLIENT_SECRET'))
    <a href="{{ route('settings.integrations.jira') }}" @class([ 'border-b-2 px-1 pb-4 text-sm font-medium' , 'border-primary-600 text-primary-600'=> request()->routeIs('settings.integrations.jira'),
        'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => !request()->routeIs('settings.integrations.jira'),
        ])>
        {{ __('Jira') }}
    </a>
    @endif

    @if(env('MICROSOFT_CLIENT_ID') && env('MICROSOFT_CLIENT_SECRET'))
    <a href="{{ route('settings.integrations.microsoft-calendar') }}" @class([ 'border-b-2 px-1 pb-4 text-sm font-medium' , 'border-primary-600 text-primary-600'=> request()->routeIs('settings.integrations.microsoft-calendar'),
        'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => !request()->routeIs('settings.integrations.microsoft-calendar'),
        ])>
        {{ __('Calendar') }}
    </a>
    @endif
</div>