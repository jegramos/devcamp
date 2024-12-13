<?php

namespace Database\Factories;

use App\Enums\Theme;
use App\Models\AccountSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountSettings>
 */
class AccountSettingsFactory extends Factory
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
            'data' => [
                'theme' => fake()->randomElement(Theme::toArray()),
                'passkeys_enabled' => false,
                '2fa_enabled' => false,
            ]
        ];
    }

    public function setTheme(Theme $theme): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => [
                'theme' => $theme->value,
                'passkeys_enabled' => true,
                '2fa_enabled' => false,
            ]
        ]);
    }

    public function passkeyLoginEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => [
                'theme' => Theme::AUTO->value,
                'passkeys_enabled' => true,
                '2fa_enabled' => false,
            ]
        ]);
    }

    public function twoFactorAuthEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => [
                'theme' => Theme::AUTO->value,
                'passkeys_enabled' => false,
                '2fa_enabled' => true,
            ]
        ]);
    }
}
