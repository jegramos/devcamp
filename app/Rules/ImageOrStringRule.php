<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Check if the input is an Image File or a string image path (E.g. images/1/projects/cover/filename.jpg)
 */
class ImageOrStringRule implements ValidationRule
{
    private int $maxFileSize;
    private int $maxStringSize;

    public function __construct(int $maxStringSize = 255, int $maxFileSize = 2048)
    {
        $this->maxFileSize = $maxFileSize;
        $this->maxStringSize = $maxStringSize;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!($value instanceof UploadedFile) && !is_string($value)) {
            $fail('The :attribute must be a string or an image.');
        }

        if (is_string($value) && strlen($value) > $this->maxStringSize) {
            $fail('The :attribute must not be longer than ' . $this->maxStringSize . ' characters.');
        }

        if ($value instanceof UploadedFile) {
            $validator = Validator::make(
                [$attribute => $value],
                [$attribute => ['image', 'max:' . $this->maxFileSize]]
            );

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));
            }
        }
    }
}
