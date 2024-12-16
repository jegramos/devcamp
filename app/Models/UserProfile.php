<?php

namespace App\Models;

use App\Actions\AddSoftDeleteMarkerAction;
use App\Enums\Gender;
use App\Services\CloudStorageManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class UserProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'given_name',
        'family_name',
        'mobile_number',
        'gender',
        'birthday',
        'profile_picture_path',
        'country_id',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'city_municipality',
        'province_state_county',
        'postal_code',
    ];

    protected $casts = [
        'birthday' => 'date:Y-m-d',
        'gender' => Gender::class,
    ];

    protected $appends = [
        /** @uses fullName() */
        'full_name',

        /** @uses profilePictureUrl() */
        'profile_picture_url',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (UserProfile $userProfile, AddSoftDeleteMarkerAction $addSoftDeleteMarkerAction) {
            if ($userProfile->mobile_number) {
                $userProfile->mobile_number = $addSoftDeleteMarkerAction->execute(
                    $userProfile->mobile_number
                );

                $userProfile->saveQuietly();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => $this->given_name . ' ' . $this->family_name);
    }

    /**
     * Create a profile_picture_url attribute
     */
    protected function profilePictureUrl(): Attribute
    {
        return Attribute::get(function () {
            // If the path is already a valid URL, we return it directly.
            // This happens when the user was created via an external login provider (ex. Github)
            if (Str::isUrl($this->profile_picture_path)) {
                return $this->profile_picture_path;
            }

            if (!$this->profile_picture_path) {
                return null;
            }

            $cloudFileManager = resolve(CloudStorageManager::class);
            return $cloudFileManager->generateTmpUrl($this->profile_picture_path, 60 * 5);
        });
    }
}
