<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;
use Validator;

class EmailRule implements ValidationRule
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
            [$attribute => ['email:rfc', 'unique:users,email']]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }

    public function exclude(): static
    {
        return $this;
    }
}
