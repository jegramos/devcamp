<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        $routeName = $this->route()->getName();

        return match ($routeName) {
            'auth.login.authenticate' => $this->getAuthenticateRules(),
            default => []
        };
    }

    private function getAuthenticateRules(): array
    {
        return [
            'email' => ['nullable', 'string', 'required_without:username'],
            'username' => ['nullable', 'string', 'required_without:email'],
            'password' => ['required', 'string'],
            'remember' => ['required', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            'email.required_without' => 'Email or username is required',
            'username.required_without' => 'Email or username is required',
        ];
    }
}
