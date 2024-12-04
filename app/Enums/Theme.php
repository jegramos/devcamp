<?php

namespace App\Enums;

use App\Enums\Traits\HasToArray;

enum Theme: string
{
    use HasToArray;

    case LIGHT = 'light';
    case DARK = 'dark';
    case AUTO = 'auto';
}
