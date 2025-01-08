<?php

namespace App\Enums;

use App\Traits\Enums\HasToArray;

enum Role: string
{
    use HasToArray;

    case USER = 'user';
    case ADMIN = 'admin';
    case SUPER_USER = 'super_user';
}
