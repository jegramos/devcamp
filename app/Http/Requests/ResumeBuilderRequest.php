<?php

namespace App\Http\Requests;

use App\Enums\SocialNetwork;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ResumeBuilderRequest extends FormRequest
{
    public function rules(): array
    {
        $routeName = $this->route()->getName();
        return match ($routeName) {
            'builder.resume.storeSubdomain' => $this->getStoreSubdomainRules(),
            'builder.resume.storeContent' => $this->getStoreContentRules(),
            default => [],
        };
    }

    private function getStoreSubdomainRules(): array
    {
        return [
            'subdomain' => [
                'required',
                'string',
                'max:25',
                'regex:/^[a-zA-Z0-9-]*$/', // Only letters, numbers, and dashes are allowed
                'unique:users,subdomain,' . $this->user()->id . ',id'
            ]
        ];
    }

    private function getStoreContentRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'titles' => ['required', 'array', 'max:15'],
            'titles.*' => ['required', 'string', 'distinct', 'max:150'],
            'experiences' => ['required', 'array', 'max:10'],
            'experiences.*' => ['required', 'string', 'distinct', 'max:150'],
            'socials' => ['required', 'array', 'max:5'],
            'socials.*.name' => [new Enum(SocialNetwork::class)],
            'socials.*.url' => ['required', 'string', 'url:https'],
            'theme_id' => ['required', 'integer', 'exists:resume_themes,id'],
        ];
    }
}
