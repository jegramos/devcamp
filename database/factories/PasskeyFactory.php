<?php

namespace Database\Factories;

use App\Models\Passkey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Uuid;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

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
            'data' => new PublicKeyCredentialSource(
                publicKeyCredentialId: Str::random(),
                type: 'public-key',
                transports: ['hybrid', 'internal'],
                attestationType: 'none',
                trustPath: EmptyTrustPath::create(),
                aaguid: Uuid::v4(),
                credentialPublicKey: Str::random(),
                userHandle: fake()->word(),
                counter: 0,
            )
        ];
    }

    public function setCredentialId(string $id): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => new PublicKeyCredentialSource(
                publicKeyCredentialId: $id,
                type: 'public-key',
                transports: ['hybrid', 'internal'],
                attestationType: 'none',
                trustPath: EmptyTrustPath::create(),
                aaguid: Uuid::v4(),
                credentialPublicKey: Str::random(),
                userHandle: fake()->word(),
                counter: 0,
            )
        ]);
    }
}
