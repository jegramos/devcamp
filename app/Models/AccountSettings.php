<?php

namespace App\Models;

use App\Enums\Theme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountSettings extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createWithDefaults(User $user): static
    {
        return static::query()->create([
            'user_id' => $user->id,
            'data' => [
                'theme' => Theme::AUTO->value,
                'passkeys_enabled' => false,
                '2fa_enabled' => false,
            ]
        ]);
    }
}
