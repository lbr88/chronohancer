<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class TimerDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'user_id',
        'workspace_id',
        'timer_id',
    ];

    /**
     * Get the validation rules for the timer description.
     *
     * @param  int|null  $ignoreId  ID to ignore in uniqueness check (for updates)
     */
    public static function validationRules(?int $ignoreId = null): array
    {
        $uniqueRule = Rule::unique('timer_descriptions')
            ->where(function ($query) {
                // Scope by current user and workspace
                return $query->where('user_id', auth()->id())
                    ->where('workspace_id', app('current.workspace')->id);
            });

        if ($ignoreId) {
            $uniqueRule->ignore($ignoreId);
        }

        return [
            'description' => [
                'required',
                'string',
                'max:255',
                $uniqueRule->where('timer_id', request('timer_id')),
            ],
            'timer_id' => 'required|exists:timers,id',
        ];
    }

    /**
     * Find an existing timer description or create a new one if it doesn't exist.
     * This helps prevent duplicates at the application level.
     */
    public static function findOrCreateForTimer(array $attributes): self
    {
        // Ensure we have the required attributes
        if (empty($attributes['timer_id']) || empty($attributes['description'])) {
            throw new \InvalidArgumentException('Timer ID and description are required');
        }

        // Log the attributes for debugging
        \Illuminate\Support\Facades\Log::debug('TimerDescription::findOrCreateForTimer', [
            'timer_id' => $attributes['timer_id'],
            'description' => $attributes['description'],
            'user_id' => $attributes['user_id'],
            'workspace_id' => $attributes['workspace_id'],
        ]);

        // Find an existing timer description
        $existingDescription = self::where('timer_id', $attributes['timer_id'])
            ->where('description', $attributes['description'])
            ->where('user_id', $attributes['user_id'])
            ->where('workspace_id', $attributes['workspace_id'])
            ->first();

        if ($existingDescription) {
            \Illuminate\Support\Facades\Log::debug('Found existing timer description', [
                'id' => $existingDescription->id,
                'description' => $existingDescription->description,
            ]);

            return $existingDescription;
        }

        // Create a new timer description
        try {
            $newDescription = self::create($attributes);

            // Verify the description was saved correctly
            $newDescription->refresh();
            \Illuminate\Support\Facades\Log::debug('Created new timer description', [
                'id' => $newDescription->id,
                'description' => $newDescription->description,
                'saved_description_length' => strlen($newDescription->description),
            ]);

            return $newDescription;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating timer description', [
                'error' => $e->getMessage(),
                'description' => $attributes['description'],
            ]);
            throw $e;
        }
    }

    /**
     * Get the timer that owns the description.
     */
    public function timer(): BelongsTo
    {
        return $this->belongsTo(Timer::class);
    }

    /**
     * Get the user that owns the description.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workspace that owns the description.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the time logs associated with this description.
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }
}
