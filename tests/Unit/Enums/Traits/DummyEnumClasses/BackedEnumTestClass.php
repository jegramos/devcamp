<?php

namespace Tests\Unit\Enums\Traits\DummyEnumClasses;

use App\Traits\Enums\HasToArray;

enum BackedEnumTestClass: string
{
    use HasToArray;

    case CASE_1 = 'case_1';
    case CASE_2 = 'case_2';
}
