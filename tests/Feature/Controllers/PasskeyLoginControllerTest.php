<?php

use App\Enums\ErrorCode;
use App\Enums\SessionFlashKey;
use App\Models\User;
use App\Services\PasskeyService;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\PasskeyFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;
use Mockery\MockInterface;
use Symfony\Component\Uid\Uuid;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\partialMock;

beforeEach(function () {
    artisan('db:seed');

    // Mock the PasskeyService
    partialMock(PasskeyService::class, function (MockInterface $mock) {
        $publicKeyCredential = new PublicKeyCredential(
            type: 'public-key',
            rawId: 'test-raw-id',
            response: mock(AuthenticatorAssertionResponse::class)
        );

        $publicKeyCredentialSource = new PublicKeyCredentialSource(
            publicKeyCredentialId: $publicKeyCredential->rawId,
            type: $publicKeyCredential->type,
            transports: ['hybrid', 'internal'],
            attestationType: 'none',
            trustPath: EmptyTrustPath::create(),
            aaguid: Uuid::v4(),
            credentialPublicKey: Str::random(),
            userHandle: fake()->word(),
            counter: 0,
        );

        $mock
            ->shouldReceive('deserialize')
            ->andReturn($publicKeyCredential, $publicKeyCredentialSource)
            ->shouldReceive('verifyPublicKeyCredential')
            ->andReturn($publicKeyCredentialSource);
    });
});

it('can login via passkey', function () {
    /** @var User $user */
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new()->passkeyLoginEnabled())
        ->has(PasskeyFactory::new()->setCredentialId('test-raw-id'))
        ->create();

    $passkeyService = resolve(PasskeyService::class);
    $passkeyAuthenticateOptions = $passkeyService->createAuthenticateOptions();

    $payload = ['answer' => json_encode(['test' => fake()->word()])];
    followingRedirects()
        ->from(route('auth.login.authenticate'))
        ->withSession([SessionFlashKey::CMS_PASSKEY_AUTHENTICATE_OPTIONS->value => $passkeyAuthenticateOptions])
        ->post(route('passkeys.login'), $payload);

    assertAuthenticatedAs($user);
});

it('returns an error if the account is deactivated', function () {
    UserFactory::new()
        ->inactive()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new()->passkeyLoginEnabled())
        ->has(PasskeyFactory::new()->setCredentialId('test-raw-id'))
        ->create();

    $passkeyService = resolve(PasskeyService::class);
    $passkeyAuthenticateOptions = $passkeyService->createAuthenticateOptions();
    $payload = ['answer' => json_encode(['test' => fake()->word()])];

    followingRedirects()
        ->from(route('auth.login.showForm'))
        ->withSession([SessionFlashKey::CMS_PASSKEY_AUTHENTICATE_OPTIONS->value => $passkeyAuthenticateOptions])
        ->post(route('passkeys.login'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Auth/LoginPage')
                ->has('errors.' . ErrorCode::ACCOUNT_DEACTIVATED->value)
        );
});
