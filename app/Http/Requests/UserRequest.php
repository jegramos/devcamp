<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\Role;
use App\Rules\DbVarcharMaxLengthRule;
use App\Rules\InternationalPhoneFormatRule;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserRequest extends FormRequest
{
    public function rules(): array
    {
        $routeName = $this->route()->getName();

        return match ($routeName) {
            'users.index' => $this->getIndexRules(),
            'users.store' => $this->getStoreRules(),
            'users.update' => $this->getUpdateRules(),
            default => [],
        };
    }

    private function getIndexRules(): array
    {
        return [
            'active' => ['boolean'],
            'verified' => ['boolean'],
            'role' => ['string', 'in:' . implode(',', Role::toArray())],
            'sort' => ['in:created_at,-created_at,family_name,-family_name,given_name,-given_name'],
            'q' => ['string', new DbVarcharMaxLengthRule()],
        ];
    }

    public function getStoreRules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'unique:users,email'],
            'username' => ['required', new UsernameRule()],
            'password' => [
                'required',
                'confirmed',
                new PasswordRule(),
            ],
            'given_name' => ['required', new DbVarcharMaxLengthRule()],
            'family_name' => ['required', new DbVarcharMaxLengthRule()],
            'mobile_number' => [
                'nullable',
                new InternationalPhoneFormatRule(),
                'unique:user_profiles,mobile_number',
                'phone:mobile,lenient,international',
            ],
            'gender' => [new Enum(Gender::class), 'nullable'],
            'birthday' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:' . date('Y-m-d')],
            'country_id' => ['nullable', 'exists:countries,id'],
            'address_line_1' => ['nullable', new DbVarcharMaxLengthRule()],
            'address_line_2' => ['nullable', new DbVarcharMaxLengthRule()],
            'address_line_3' => ['nullable', new DbVarcharMaxLengthRule()],
            'city_municipality' => ['nullable', new DbVarcharMaxLengthRule()],
            'province_state_county' => ['nullable', new DbVarcharMaxLengthRule()],
            'postal_code' => ['nullable', new DbVarcharMaxLengthRule()],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'distinct', 'exists:roles,name'],
        ];
    }

    public function getUpdateRules(): array
    {
        $userId = $this->route('user')->id;
        return [
            'email' => ['email:rfc', 'unique:users,email,' . $userId . ',id'],
            'username' => [new UsernameRule(), 'unique:users,username,' . $userId . ',id'],
            'mobile_number' => [
                'nullable',
                new InternationalPhoneFormatRule(),
                'phone:mobile,lenient,international',
                'unique:user_profiles,mobile_number,' . $userId . ',user_id',
            ],
            'password' => [
                'confirmed',
                new PasswordRule(),
            ],
            'active' => ['boolean'],
            'verified' => ['boolean'],
            'given_name' => [new DbVarcharMaxLengthRule()],
            'family_name' => [new DbVarcharMaxLengthRule()],
            'gender' => ['nullable', new Enum(Gender::class)],
            'birthday' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:' . date('Y-m-d')],
            'country_id' => ['nullable', 'exists:countries,id'],
            'address_line_1' => ['nullable', new DbVarcharMaxLengthRule()],
            'address_line_2' => ['nullable', new DbVarcharMaxLengthRule()],
            'address_line_3' => ['nullable', new DbVarcharMaxLengthRule()],
            'city_municipality' => ['nullable', new DbVarcharMaxLengthRule()],
            'province_state_county' => ['nullable', new DbVarcharMaxLengthRule()],
            'postal_code' => ['nullable', new DbVarcharMaxLengthRule()],
            'roles' => ['array'],
            'roles.*' => ['distinct', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            /** @see https://github.com/Propaganistas/Laravel-Phone#validation */
            'mobile_number.phone' => 'The :attribute field format must be a valid mobile number',
        ];
    }
}
