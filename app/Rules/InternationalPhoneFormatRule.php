<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class InternationalPhoneFormatRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $errorMessage = 'The :attribute must follow the E.164 international phone number formatting: [+][country code][area code][local phone number]. E.g. +639091122333';
        if (is_null($value)) {
            $fail($errorMessage);
        }

        $validFormat = preg_match("/^\+[0-9]+$/", $value) > 0;
        if (! $validFormat) {
            $fail($errorMessage);
        }
    }
}
