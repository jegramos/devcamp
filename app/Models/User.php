<?php

namespace App\Models;

use App\Actions\AddSoftDeleteMarkerAction;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

use function Illuminate\Support\defer;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'email',
        'username',
        'password',
        'active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'active' => 'boolean',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (User $user, AddSoftDeleteMarkerAction $addSoftDeleteMarkerAction) {
            DB::transaction(function () use ($user, $addSoftDeleteMarkerAction) {
                $user->email = $addSoftDeleteMarkerAction->execute($user->email);
                $user->saveQuietly();

                // Soft-delete all the relations
                $user->userProfile()->delete();
                $user->externalAccount()->delete();
                $user->passkeys()->delete();
                $user->accountSettings()->delete();
            });
        });
    }

    /**
     * The username attribute should always be in lowercase.
     */
    protected function username(): Attribute
    {
        return Attribute::set(
            fn (string $value) => strtolower($value)
        );
    }

    /**
     * The email attribute should always be in lowercase.
     */
    protected function email(): Attribute
    {
        return Attribute::set(
            fn (string $value) => strtolower($value)
        );
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function externalAccount(): HasOne
    {
        return $this->hasOne(ExternalAccount::class);
    }

    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class);
    }

    public function accountSettings(): HasOne
    {
        return $this->hasOne(AccountSettings::class);
    }

    public function isFromExternalAccount(): bool
    {
        return is_null($this->password);
    }

    public static function getViaUsernameAndPassword(string $username, string $password): ?static
    {
        $user = static::query()->where('username', $username)->first();

        $hasCorrectCreds = $user && Hash::check($password, $user->password);
        if (! $hasCorrectCreds) {
            return null;
        }

        return $user;
    }

    public static function getViaEmailAndPassword(string $email, string $password): ?static
    {
        $user = static::query()->where('email', $email)->first();

        $hasCorrectCreds = $user && Hash::check($password, $user->password);
        if (! $hasCorrectCreds) {
            return null;
        }

        return $user;
    }

    /**
     * Send an email verification notification asynchronously
     */
    public function sendEmailVerificationNotification(): void
    {
        $expirationInMinutes = Config::get('auth.verification.expire', 60);
        defer(function () use ($expirationInMinutes) {
            $this->notify(new VerifyEmailNotification($this, $expirationInMinutes));
        });
    }

    /*
     * Override default password reset notification
     */
    public function sendPasswordResetNotification($token): void
    {
        defer(function () use ($token) {
            $this->notify(new ResetPasswordNotification($token));
        });
    }
}
