<?php

namespace App\Models;

use App\Services\PasskeyService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webauthn\PublicKeyCredentialSource;

class Passkey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'data',
        'credential_id'
    ];

    protected function data(): Attribute
    {
        $passkeyService = resolve(PasskeyService::class);
        return new Attribute(
            get: fn (string $value) => $passkeyService->deserialize($value, PublicKeyCredentialSource::class),
            set: fn (PublicKeyCredentialSource $value) => [
                'credential_id' => $value->publicKeyCredentialId,
                'data' => $passkeyService->serialize($value)
            ]
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
