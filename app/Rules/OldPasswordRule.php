<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Translation\PotentiallyTranslatedString;

class OldPasswordRule implements ValidationRule
{
    private ?string $currentPassword;

    public function __construct(?string $currentPassword)
    {
        $this->currentPassword = $currentPassword;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($this->currentPassword)) {
            return;
        }

        if (! Hash::check($value, $this->currentPassword)) {
            $fail("You have entered an invalid current password.");
        }
    }
}
