<?php

namespace Database\Factories;

use App\Enums\Gender;
use DB;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        $gender = $this->faker->randomElement(Gender::toArray());
        return [
            'user_id' => UserFactory::new(),
            'given_name' => fake()->firstName($gender),
            'family_name' => fake()->lastName($gender),
            'mobile_number' => fake()->phoneNumber(),
            'gender' => $gender,
            'birthday' => fake()->date(),
            'profile_picture_path' => fake()->filePath(),
            'country_id' => DB::table('countries')->inRandomOrder()->first()->id,
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->streetAddress(),
            'address_line_3' => fake()->streetAddress(),
            'city_municipality' => fake()->city(),
            'province_state_county' => fake()->city(),
            'postal_code' => fake()->postcode(),
        ];
    }
}
