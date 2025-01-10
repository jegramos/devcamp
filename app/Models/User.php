<?php

namespace App\Models;

use App\Actions\AddSoftDeleteMarkerAction;
use App\Http\QueryFilters\User\ActiveFilter;
use App\Http\QueryFilters\User\RoleFilter;
use App\Http\QueryFilters\User\SearchFilter;
use App\Http\QueryFilters\User\VerifiedFilter;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyAccountNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
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
        'subdomain',
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

    protected $appends = [
        /** @uses portfolioUrl() */
        'portfolio_url',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (User $user) {
            $addSoftDeleteMarkerAction = resolve(AddSoftDeleteMarkerAction::class);
            DB::transaction(function () use ($user, $addSoftDeleteMarkerAction) {
                $user->email = $addSoftDeleteMarkerAction->execute($user->email);
                $user->username = $addSoftDeleteMarkerAction->execute($user->username);
                $user->saveQuietly();

                // Soft-delete all the relations
                $user->userProfile()->delete();
                $user->externalAccount()->delete();
                $user->passkeys()->delete();
                $user->accountSettings()->delete();
                $user->resume()->delete();
            });
        });
    }

    /**
     * @Scope
     */
    public function scopeSorted(Builder $builder, Request $request): Builder
    {
        if (!$request->input('sort')) {
            // Default to latest entry first
            return $builder->orderBy('users.id', 'desc');
        }

        $sortedBy = $request->input('sort');
        $order = $sortedBy[0] === '-' ? 'desc' : 'asc';

        // Remove the leading '-' if it exists
        $sortedBy = $sortedBy[0] === '-' ? substr($sortedBy, 1) : $sortedBy;

        if ($sortedBy === 'created_at') {
            return $builder->orderBy('users.created_at', $order);
        }

        // Sorting via family_name or given_name requires the userProfile relation
        return $builder->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->orderBy('user_profiles.' . $sortedBy, $order);
    }

    /**
     * @Scope
     * Filter scope HTTP query filters
     */
    public function scopeFiltered(Builder $builder): Builder
    {
        return app(Pipeline::class)
            ->send($builder->with('userProfile', 'roles'))
            ->through([
                ActiveFilter::class,
                VerifiedFilter::class,
                RoleFilter::class,
                SearchFilter::class,
            ])
            ->thenReturn();
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

    /**
     * The subdomain attribute should always be in lowercase.
     */
    protected function subdomain(): Attribute
    {
        return Attribute::set(
            fn (?string $value) => is_null($value) ? null : strtolower($value)
        );
    }

    /*
     * Build the Portfolio URL of the user
     */
    protected function portfolioUrl(): Attribute
    {
        return Attribute::get(
            function () {
                if (!$this->subdomain) {
                    return null;
                }

                return \Illuminate\Support\Facades\Request::getScheme() . '://' . $this->subdomain . '.' . Config::get('app.portfolio_subdomain');
            }
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

    public function resume(): HasOne
    {
        return $this->hasOne(Resume::class);
    }

    public function isFromExternalAccount(): bool
    {
        return is_null($this->password);
    }

    public static function getViaUsernameAndPassword(string $username, string $password): ?static
    {
        $user = static::query()->where('username', $username)->first();

        $hasCorrectCreds = $user && Hash::check($password, $user->password);
        if (!$hasCorrectCreds) {
            return null;
        }

        return $user;
    }

    public static function getViaEmailAndPassword(string $email, string $password): ?static
    {
        $user = static::query()->where('email', $email)->first();

        $hasCorrectCreds = $user && Hash::check($password, $user->password);
        if (!$hasCorrectCreds) {
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

    /**
     * Send an account verification notification
     */
    public function sendAccountVerificationNotification(string $temporaryPassword): void
    {
        $expirationInMinutes = Config::get('auth.verification.expire', 60);
        defer(function () use ($temporaryPassword, $expirationInMinutes) {
            $this->notify(new VerifyAccountNotification($this, $temporaryPassword, $expirationInMinutes));
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
