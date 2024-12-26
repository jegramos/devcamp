<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeTheme extends Model
{
    protected $fillable = [
        'name',
        'page'
    ];

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class, 'theme_id', 'id');
    }

    public static function default(): static
    {
        return static::query()->first();
    }
}
