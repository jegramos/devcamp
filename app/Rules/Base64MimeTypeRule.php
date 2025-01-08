<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Base64MimeTypeRule implements ValidationRule
{
    private array $mimeTypes;

    public function __construct(array $mimeTypes)
    {
        $this->mimeTypes = $mimeTypes;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $value)) {
            $fail('The :attribute must be a valid base64 encoded image.');
        }

        $type = explode(';', $value)[0];

        if (!in_array($type, array_map(fn ($m) => 'data:' . $m, $this->mimeTypes))) {
            $types = implode(', ', $this->mimeTypes);
            $fail("The :attribute must be a valid image of type: $types");
        }
    }
}
