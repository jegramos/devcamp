<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            $this->mergeWhen($this->relationLoaded('userProfile'), [
                'given_name' => $this->userProfile->given_name,
                'family_name' => $this->userProfile->family_name,
                'birthday' => $this->userProfile->birthday,
                'gender' => $this->userProfile->gender,
                'mobile_number' => $this->userProfile->mobile_number,
                'country_id' => $this->userProfile->country_id,
                'city_municipality' => $this->userProfile->city_municipality,
                'province_state_county' => $this->userProfile->province_state_county,
                'postal_code' => $this->userProfile->postal_code,
                'address_line_1' => $this->userProfile->address_line_1,
                'address_line_2' => $this->userProfile->address_line_2,
                'address_line_3' => $this->userProfile->address_line_3,
                'profile_picture_url' => $this->userProfile->profile_picture_url,
            ]),
        ];
    }
}
