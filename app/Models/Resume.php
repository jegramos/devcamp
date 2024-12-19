<?php

namespace App\Models;

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
        'socials',
        'theme_id',
    ];

    protected $casts = [
        'titles' => 'array',
        'experiences' => 'array',
        'socials' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(ResumeTheme::class, 'theme_id', 'id');
    }
}
