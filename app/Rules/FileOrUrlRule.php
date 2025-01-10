<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class FileOrUrlRule implements ValidationRule
{
    private int $maxFileSize;
    private int $maxUrlLength;
    private array $mimeTypes;

    public function __construct(array $mimeTypes = [], int $maxUrlLength = 2048, int $maxFileSize = 2048)
    {
        $this->mimeTypes = $mimeTypes;
        $this->maxFileSize = $maxFileSize;
        $this->maxUrlLength = $maxUrlLength;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!($value instanceof UploadedFile) && !is_string($value)) {
            $validator = Validator::make(
                [$attribute => $value],
                [$attribute => ['url:https', 'max:' . $this->maxUrlLength]]
            );

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));
            }

            $fail('The :attribute must be a valid and secured URL.');
        }

        if (is_string($value) && strlen($value) > $this->maxUrlLength) {
            $fail('The :attribute must not be longer than ' . $this->maxUrlLength . ' characters.');
        }

        if ($value instanceof UploadedFile) {
            $validator = Validator::make(
                [$attribute => $value],
                [$attribute => ['file', 'max:' . $this->maxFileSize, 'mimes:' . implode(',', $this->mimeTypes)]]
            );

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));
            }
        }
    }
}
