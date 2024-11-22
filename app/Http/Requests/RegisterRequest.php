<?php

namespace App\Http\Requests;

use App\Rules\DbVarcharMaxLengthRule;
use App\Rules\PasswordRule;
use App\Rules\RecaptchaRule;
use App\Rules\UsernameRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        $route = $this->route()->getName();

        return match ($route) {
            'auth.register.process' => $this->getProcessRegistrationRules(),
            default => [],
        };
    }

    private function getProcessRegistrationRules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'unique:users,email'],
            'username' => ['required', new UsernameRule(), 'unique:users,username'],
            'password' => ['required', 'confirmed', new PasswordRule()],
            'given_name' => ['required', new DbVarcharMaxLengthRule()],
            'family_name' => ['required', new DbVarcharMaxLengthRule()],
            'country_id' => ['required', 'exists:countries,id'],
            'recaptcha_response_token' => [new RecaptchaRule()]
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'The country field is required.',
            'country_id.exists' => 'Select a valid country.',
        ];
    }
}
