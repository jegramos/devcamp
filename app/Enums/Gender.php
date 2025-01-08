<?php

namespace App\Enums;

use App\Traits\Enums\HasToArray;

enum Gender: string
{
    use HasToArray;

    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';
}
