<?php

namespace App\Enums;

use App\Enums\Traits\HasToArray;

enum Role: string
{
    use HasToArray;

    case USER = 'user';
    case ADMIN = 'admin';
    case SUPER_USER = 'super_user';
}
