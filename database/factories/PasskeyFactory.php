<?php

namespace Database\Factories;

use App\Models\Passkey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Passkey>
 */
class PasskeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new()->has(UserProfileFactory::new()),
            'name' => fake()->word(),
            'credential_id' => Str::random(),
            'data' => []
        ];
    }
}
