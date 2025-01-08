<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Base64MaxSizeRule implements ValidationRule
{
    private int $maxKilobytes;

    public function __construct(int $maxKilobytes)
    {
        $this->maxKilobytes = $maxKilobytes;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Decode the image
        $decodedImage = base64_decode($value);

        // Get image size in Kilobytes
        $imageSize = strlen($decodedImage) / 1000;

        if ($imageSize > $this->maxKilobytes) {
            $max = $this->maxKilobytes;
            $fail("The :attribute must be less than $max kilobytes.");
        }
    }
}
