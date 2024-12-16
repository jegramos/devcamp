<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;
use Log;

class RecaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('services.google.recaptcha.enabled')) {
            Log::debug('RecaptchaRule not enabled: ' . $value);
            return;
        }

        if (empty($value)) {
            $fail('Please confirm that you are not a robot.');
        }

        $endpoint = config('services.google.recaptcha.url');
        $secret = config('services.google.recaptcha.secret_key');

        try {
            $response = Http::asForm()->post($endpoint, ['secret' => $secret, 'response' => $value])->json();
        } catch (ConnectionException $e) {
            Log::error('RecaptchaRule connection error: ' . $e->getMessage());
            $fail('There was a reCAPTCHA connection issue.');
        }

        if (!isset($response['success']) || !$response['success']) {
            $fail('The reCAPTCHA failed.');
        }
    }
}
