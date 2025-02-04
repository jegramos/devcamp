<?php

namespace App\Models;

use App\Services\CloudStorageManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'titles',
        'experiences',
        'projects',
        'tech_expertise',
        'socials',
        'services',
        'theme_id',

        /** @uses Resume::workTimeline() */
        'work_timeline',

        'contact'
    ];

    protected $casts = [
        'titles' => 'array',
        'experiences' => 'array',
        'socials' => 'array',
        'tech_expertise' => 'array',
        'projects' => 'array',
        'services' => 'array',
        'contact' => 'array',
    ];

    protected function workTimeline(): Attribute
    {
        return new Attribute(get: function ($value) {
            if (empty($value)) {
                return [];
            }

            $value = json_decode($value, true);
            if (is_null($value['downloadable'])) {
                return $value;
            }

            $cloudStorage = resolve(CloudStorageManager::class);
            $value['downloadable_url'] = $cloudStorage->generateTmpUrl(
                $value['downloadable'],
                60 * 60 * 24
            );

            return $value;
        }, set: function ($value) {

            if (is_null($value)) {
                return null;
            }

            // Set the defaults
            if (is_array($value) && !isset($value['downloadable'])) {
                $value['downloadable'] = null;
            }

            if (is_array($value) && !isset($value['history'])) {
                $value['history'] = [];
            }

            return json_encode($value);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(ResumeTheme::class, 'theme_id', 'id');
    }
}
