<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;

class UsernameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException('The value must be a string.');
        }

        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => ['string', 'min:3', 'max:50', new AlphaDashDotRule()]]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}
