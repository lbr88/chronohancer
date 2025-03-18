<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\JiraService;
use App\Services\MicrosoftGraphService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            // Create default workspace for new users
            $workspace = Workspace::findOrCreateDefault($user->id);

            // Create default "No Project" project for new users
            Project::findOrCreateDefault($user->id);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'time_format',
        'provider',
        'provider_id',
        'avatar',
        'jira_access_token',
        'jira_refresh_token',
        'jira_token_expires_at',
        'jira_cloud_id',
        'jira_site_url',
        'jira_enabled',
        'tempo_access_token',
        'tempo_refresh_token',
        'tempo_token_expires_at',
        'tempo_enabled',
        'microsoft_enabled',
        'microsoft_access_token',
        'microsoft_refresh_token',
        'microsoft_token_expires_at',
        'microsoft_calendar_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'jira_enabled' => 'boolean',
            'jira_token_expires_at' => 'datetime',
            'tempo_enabled' => 'boolean',
            'tempo_token_expires_at' => 'datetime',
            'microsoft_enabled' => 'boolean',
            'microsoft_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Check if Jira integration is enabled and configured.
     */
    public function hasJiraEnabled(): bool
    {
        return $this->jira_enabled &&
            $this->jira_access_token &&
            $this->jira_cloud_id &&
            $this->jira_site_url;
    }

    /**
     * Check if Tempo integration is enabled and configured.
     */
    public function hasTempoEnabled(): bool
    {
        return $this->tempo_enabled &&
            $this->tempo_access_token &&
            $this->tempo_refresh_token;
    }

    /**
     * Get a configured instance of JiraService for this user.
     */
    public function jira(): JiraService
    {
        return app(JiraService::class)->setUser($this);
    }

    /**
     * Disconnect Jira integration.
     */
    public function disconnectJira(): void
    {
        $this->update([
            'jira_enabled' => false,
            'jira_access_token' => null,
            'jira_refresh_token' => null,
            'jira_token_expires_at' => null,
            'jira_cloud_id' => null,
            'jira_site_url' => null,
        ]);
    }

    /**
     * Check if Microsoft Graph integration is enabled and configured.
     */
    public function hasMicrosoftEnabled(): bool
    {
        return $this->microsoft_enabled &&
            $this->microsoft_access_token &&
            $this->microsoft_refresh_token;
    }

    /**
     * Get a configured instance of MicrosoftGraphService for this user.
     */
    public function microsoft(): MicrosoftGraphService
    {
        return app(MicrosoftGraphService::class)->setUser($this);
    }

    // Removed duplicate method

    /**
     * Disconnect Microsoft Graph integration.
     */
    public function disconnectMicrosoft(): void
    {
        $this->update([
            'microsoft_enabled' => false,
            'microsoft_access_token' => null,
            'microsoft_refresh_token' => null,
            'microsoft_token_expires_at' => null,
        ]);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Get the projects for the user.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the default project for the user.
     */
    public function getDefaultProjectAttribute()
    {
        return Project::findOrCreateDefault($this->id);
    }

    /**
     * Get the workspaces for the user.
     */
    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    /**
     * Get the default workspace for the user.
     */
    public function getDefaultWorkspaceAttribute()
    {
        return Workspace::findOrCreateDefault($this->id);
    }

    /**
     * Get the favorite Jira issues for the user.
     */
    public function favoriteJiraIssues(): HasMany
    {
        return $this->hasMany(FavoriteJiraIssue::class);
    }

    /**
     * Find or create a user based on OAuth provider data
     *
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \App\Models\User
     */
    public static function findOrCreateFromSocialite($provider, $providerUser)
    {
        // First try to find user by provider and provider_id
        $user = self::where('provider', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($user) {
            return $user;
        }

        // If not found, try to find by email
        $user = self::where('email', $providerUser->getEmail())->first();

        if ($user) {
            // Update the user with provider details
            $user->update([
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
                'avatar' => $providerUser->getAvatar(),
            ]);

            return $user;
        }

        // Create a new user
        return self::create([
            'name' => $providerUser->getName(),
            'email' => $providerUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
            'avatar' => $providerUser->getAvatar(),
            'email_verified_at' => now(), // Social logins are considered verified
        ]);
    }
}
