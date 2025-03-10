<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timers(): BelongsToMany
    {
        return $this->belongsToMany(Timer::class);
    }

    public function timeLogs(): BelongsToMany
    {
        return $this->belongsToMany(TimeLog::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    // Static method to find or create a tag by name for a user
    public static function findOrCreateForUser(string $name, int $userId, ?string $color = null): self
    {
        return static::firstOrCreate(
            ['name' => $name, 'user_id' => $userId],
            ['color' => $color ?? '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT)]
        );
    }
}
