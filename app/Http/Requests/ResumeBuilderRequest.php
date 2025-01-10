<?php

namespace App\Http\Requests;

use App\Enums\SocialNetwork;
use App\Rules\DbVarcharMaxLengthRule;
use App\Rules\FileOrUrlRule;
use App\Rules\StringOrImageRule;
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
            'titles.*' => ['required', 'string', 'max:150', 'distinct'],
            'experiences' => ['required', 'array', 'max:10'],
            'experiences.*' => ['required', 'string', 'max:150', 'distinct'],
            'socials' => ['required', 'array', 'max:5'],
            'socials.*.name' => ['required', new Enum(SocialNetwork::class), 'distinct'],
            'socials.*.url' => ['required', 'string', 'url:https', 'max:2048'],
            'theme_id' => ['required', 'integer', 'exists:resume_themes,id'],
            'tech_expertise' => ['array', 'max:100'],
            'tech_expertise.*.proficiency' => ['required', 'string', 'in:active,familiar'],
            'tech_expertise.*.name' => ['required', 'string', 'max:150', 'distinct'],
            'tech_expertise.*.description' => ['required', 'string', 'max:150'],
            'tech_expertise.*.logo' => ['nullable', 'string', 'url:https', 'max:255'],
            'projects' => ['array', 'max:6'],
            'projects.*.title' => ['required', 'string', 'max:150', 'distinct'],
            'projects.*.description' => ['required', 'string', 'max:500'],
            'projects.*.cover' => ['nullable', new StringOrImageRule()],
            'projects.*.links' => ['array', 'max:3'],
            'projects.*.links.*.name' => ['required', 'string', 'max:150'],
            'projects.*.links.*.url' => ['required', 'string', 'url:https', 'max:2048'],
            'work_timeline' => ['nullable', 'array'],
            'work_timeline.downloadable' => ['nullable', new FileOrUrlRule(['pdf'], 5000)],
            'work_timeline.history' => ['array', 'max:50'],
            'work_timeline.history.*.title' => ['required', 'string', 'max:150'],
            'work_timeline.history.*.description' => ['required', 'string', 'max:5000'],
            'work_timeline.history.*.period' => ['required', 'array', 'max:2'],
            'work_timeline.history.*.period.*' => ['required', 'date_format:Y-m-d'],
            'work_timeline.history.*.company' => ['string', 'required', new DbVarcharMaxLengthRule()],
            'work_timeline.history.*.logo' => ['nullable', 'string', 'url:https', 'max:2048'],
            'work_timeline.history.*.tags' => ['nullable', 'array', 'max:25'],
            'work_timeline.history.*.tags.*' => ['required', 'string', 'max:50'],
            'services' => ['array', 'max:6'],
            'services.*.title' => ['required', 'string', 'max:150', 'distinct'],
            'services.*.description' => ['required', 'string', 'max:500'],
            'services.*.tags' => ['nullable', 'array', 'max:5'],
            'services.*.tags.*' => ['required', 'string', 'max:50'],
            'services.*.logo' => ['nullable', 'string', 'url:https', 'max:2048'],
            'contact.show' => ['required', 'boolean'],
            'contact.availability_status' => ['required', 'string', new DbVarcharMaxLengthRule()]
        ];
    }
}
