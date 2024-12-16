<?php

use App\Enums\SessionFlashKey;
use App\Models\User;
use App\Services\PasskeyService;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;
use Mockery\MockInterface;
use Symfony\Component\Uid\Uuid;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\partialMock;
use function PHPUnit\Framework\assertCount;

beforeEach(function () {
    artisan('db:seed');

    // Mock the PasskeyService
    partialMock(PasskeyService::class, function (MockInterface $mock) {
        $publicKeyCredential = new stdclass();
        $publicKeyCredential->response = mock(AuthenticatorAttestationResponse::class);
        $publicKeyCredentialSource = mock(PublicKeyCredentialSource::class);

        $publicKeyCredentialSource->publicKeyCredentialId = Str::random();
        $publicKeyCredentialSource->type = 'public-key';
        $publicKeyCredentialSource->transports = ['hybrid', 'internal'];
        $publicKeyCredentialSource->attestationType = 'none';
        $publicKeyCredentialSource->trustPath = EmptyTrustPath::create();
        $publicKeyCredentialSource->aaguid = Uuid::v4();
        $publicKeyCredentialSource->counter = 0;
        $publicKeyCredentialSource->userHandle = fake()->word();
        $publicKeyCredentialSource->backupStatus = true;
        $publicKeyCredentialSource->uvInitialized = true;
        $publicKeyCredentialSource->backupEligible = true;
        $publicKeyCredentialSource->credentialPublicKey = Str::random();
        $publicKeyCredentialSource->otherUI = null;

        $mock
            ->shouldReceive('deserialize')
            ->andReturn($publicKeyCredential)
            ->shouldReceive('registerPublicKeyCredential')
            ->andReturn($publicKeyCredentialSource);
    });
});

it('can create a passkey', /** @throws Throwable */ function () {
    /** @var User $user */
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    actingAs($user);

    $payload = [
        'name' => fake()->word(),
        'passkey' => json_encode(['test' => fake()->word()]),
    ];

    $passkeyService = resolve(PasskeyService::class);
    $passkeyRegisterOptions = $passkeyService->createRegisterOptions($user);
    followingRedirects()
        ->from(route('accountSettings.index'))
        ->withSession([SessionFlashKey::CMS_PASSKEY_REGISTER_OPTIONS->value => $passkeyRegisterOptions])
        ->post(route('passkeys.store'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/AccountSettingsPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    assertCount(1, $user->passkeys()->get('id'));
});

it('can delete a passkey', /** @throws Throwable */ function () {
    /** @var User $user */
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    actingAs($user);

    $payload = [
        'name' => fake()->word(),
        'passkey' => json_encode(['test' => fake()->word()]),
    ];

    $passkeyService = resolve(PasskeyService::class);
    $passkeyRegisterOptions = $passkeyService->createRegisterOptions($user);

    followingRedirects()
        ->from(route('accountSettings.index'))
        ->withSession([SessionFlashKey::CMS_PASSKEY_REGISTER_OPTIONS->value => $passkeyRegisterOptions])
        ->post(route('passkeys.store'), $payload);

    $passkey = $user->passkeys()->get()->first();
    followingRedirects()
        ->from(route('accountSettings.index'))
        ->delete(route('passkeys.destroy', ['passkey' => $passkey->id]))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/AccountSettingsPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    assertCount(0, $user->passkeys()->get('id'));
});
