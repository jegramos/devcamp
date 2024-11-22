<?php

namespace App\Http\Requests;

use App\Rules\PasswordRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $routeName = $this->route()->getName();
        return match ($routeName) {
            'password.showSendLinkForm' => $this->getShowSendLinkFormRules(),
            'password.resetPassword' => $this->getResetPasswordRules(),
            default => [],
        };
    }

    private function getShowSendLinkFormRules(): array
    {
        return [
            'email' => ['required', 'email:rfc']
        ];
    }

    private function getResetPasswordRules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', new PasswordRule()],
        ];
    }
}
