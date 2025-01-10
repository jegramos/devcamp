<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/** @mixin User */
class UserListItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified' => !is_null($this->email_verified_at),
            'active' => $this->active,
            'portfolio_url' => $this->portfolio_url,
            $this->mergeWhen($this->relationLoaded('userProfile'), [
                'given_name' => $this->userProfile->given_name,
                'family_name' => $this->userProfile->family_name,
                'full_name' => $this->userProfile->full_name,
                'birthday' => $this->userProfile->birthday,
                'gender' => $this->userProfile->gender,
                'mobile_number' => $this->userProfile->mobile_number,
                'address_line_1' => $this->userProfile->address_line_1,
                'address_line_2' => $this->userProfile->address_line_2,
                'address_line_3' => $this->userProfile->address_line_3,
                'postal_code' => $this->userProfile->postal_code,
                'city_municipality' => $this->userProfile->city_municipality,
                'province_state_county' => $this->userProfile->province_state_county,
                'country_id' => $this->userProfile->country_id,
                'profile_picture_url' => $this->userProfile->profile_picture_url,
                'name_initials' => $this->userProfile->given_name[0] . '' . $this->userProfile->family_name[0],
            ]),
            $this->mergeWhen($this->relationLoaded('roles'), [
                'roles' => $this->roles->map(function (Role $role) {
                    return ['name' => $role->name, 'label' => Str::title(str_replace('_', ' ', $role->name))];
                }),
            ]),
        ];
    }
}
