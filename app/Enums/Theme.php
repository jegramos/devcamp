<?php

namespace App\Enums;

use App\Traits\Enums\HasToArray;

enum Theme: string
{
    use HasToArray;

    case LIGHT = 'light';
    case DARK = 'dark';
    case AUTO = 'auto';
}
