<?php

namespace App\Http\Requests;

use App\Enums\Theme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AccountSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        $routeName = $this->route()->getName();
        return match ($routeName) {
            'accountSettings.store' => $this->getStoreRules(),
            default => [],
        };
    }

    private function getStoreRules(): array
    {
        return [
            'passkeys_enabled' => ['sometimes', 'required', 'boolean'],
            'theme' => ['sometimes', 'required', 'string', new Enum(Theme::class)],
            '2fa_enabled' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
