<?php

namespace App\Http\Requests;

use App\Rules\DbVarcharMaxLengthRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PortfolioInquiryRequest extends FormRequest
{
    /**
      * Get the validation rules that apply to the request.
      *
      * @return array<string, ValidationRule|array|string>
      */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', new DbVarcharMaxLengthRule()],
            'email' => ['required', 'email:rfc'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }
}
