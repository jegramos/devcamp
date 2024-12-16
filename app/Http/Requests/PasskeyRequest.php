<?php

namespace App\Http\Requests;

use App\Rules\DbVarcharMaxLengthRule;
use Illuminate\Foundation\Http\FormRequest;

class PasskeyRequest extends FormRequest
{
    public function rules(): array
    {
        $routeName = $this->route()->getName();

        return match ($routeName) {
            'passkeys.store' => $this->getStoreRules(),
            default => [],
        };
    }

    private function getStoreRules(): array
    {
        return [
            'name' => ['required', new DbVarcharMaxLengthRule()],
            'passkey' => ['required', 'json']
        ];
    }
}
