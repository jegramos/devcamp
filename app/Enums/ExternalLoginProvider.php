<?php

namespace App\Enums;

use App\Traits\Enums\HasToArray;

enum ExternalLoginProvider: string
{
    use HasToArray;

    case GOOGLE = 'google';
    case GITHUB = 'github';
}
