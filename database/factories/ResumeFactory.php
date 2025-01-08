<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\ResumeTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resume>
 */
class ResumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new()->has(UserProfileFactory::new()),
            'name' => fake()->name(),
            'titles' => [],
            'tech_expertise' => [],
            'experiences' => [],
            'socials' => [],
            'theme_id' => ResumeTheme::default()->id,
        ];
    }
}
