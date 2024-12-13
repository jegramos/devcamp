<?php

namespace App\Models;

use App\Enums\ExternalLoginProvider;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ExternalAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'id_token',
        'refresh_token',
    ];

    protected $hidden = [
        /** @uses accessToken */
        'access_token',

        /** @uses idToken */
        'id_token',

        /** @uses refreshToken */
        'refresh_token',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'provider' => ExternalLoginProvider::class,
    ];

    /**
     * Access tokens are encrypted when stored, and decrypted when retrieved
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => is_null($value) ? $value : Crypt::decryptString($value),
            set: fn (?string $value) => is_null($value) ? $value : Crypt::encryptString($value),
        );
    }

    /**
     * ID tokens are encrypted when stored, and decrypted when retrieved
     */
    protected function idToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => is_null($value) ? $value : Crypt::decryptString($value),
            set: fn (?string $value) => is_null($value) ? $value : Crypt::encryptString($value),
        );
    }

    /**
     * Refresh tokens are encrypted when stored, and decrypted when retrieved
     */
    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => is_null($value) ? $value : Crypt::decryptString($value),
            set: fn (?string $value) => is_null($value) ? $value : Crypt::encryptString($value),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
