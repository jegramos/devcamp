<?php

use App\Actions\User\CreateUserAction;
use App\Actions\User\SyncExternalAccountAction;
use App\Actions\User\UpdateUserAction;
use App\Enums\ExternalLoginProvider;
use App\Enums\Role;
use App\Exceptions\DuplicateEmailException;
use App\Models\ExternalAccount;
use App\Models\User;
use Database\Factories\ExternalAccountFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    artisan('db:seed');
});

test('it can create a google new user', /** @throws Throwable */ function () {
    $providerUser = Mockery::mock(SocialiteUser::class);
    $providerUser
        ->shouldReceive('user')
        ->shouldReceive('getId')
        ->andReturn(rand(1, 100))
        ->shouldReceive('getEmail')
        ->andReturn('foo@example.com')
        ->shouldReceive('getAvatar')
        ->andReturn(fake()->imageUrl());

    $providerUser->refreshToken = Str::random(40);
    $providerUser->token = Str::random(40);
    $providerUser->user = [
        'given_name' => fake()->firstName(),
        'family_name' => fake()->lastName(),
    ];

    $syncAction = resolve(SyncExternalAccountAction::class);
    $createUserAction = resolve(CreateUserAction::class);
    $updateUserAction = resolve(UpdateUserAction::class);
    $syncAction->execute(
        ExternalLoginProvider::GOOGLE,
        $providerUser,
        $createUserAction,
        $updateUserAction,
        [Role::USER]
    );

    assertDatabaseCount('users', 1);
});

test('it can create a github new user', /** @throws Throwable */ function () {
    $providerUser = Mockery::mock(SocialiteUser::class);
    $providerUser
        ->shouldReceive('user')
        ->shouldReceive('getId')
        ->andReturn(rand(1, 100))
        ->shouldReceive('getEmail')
        ->andReturn('foo@example.com')
        ->shouldReceive('getAvatar')
        ->andReturn(fake()->imageUrl());

    $providerUser->refreshToken = Str::random(40);
    $providerUser->token = Str::random(40);
    $providerUser->user = [
        'name' => fake()->firstName() . ' ' . fake()->lastName(),
    ];

    $syncAction = resolve(SyncExternalAccountAction::class);
    $createUserAction = resolve(CreateUserAction::class);
    $updateUserAction = resolve(UpdateUserAction::class);
    $syncAction->execute(
        ExternalLoginProvider::GITHUB,
        $providerUser,
        $createUserAction,
        $updateUserAction,
        [Role::USER],
    );

    assertDatabaseCount('users', 1);
});

test('it can update an google existing user', /** @throws Throwable */ function () {
    // Create an existing user with an external account
    $user = UserFactory::new()->has(UserProfileFactory::new());

    /** @var ExternalAccount $externalAccount */
    $externalAccount = ExternalAccountFactory::new()
        ->for($user)
        ->create(['provider' => ExternalLoginProvider::GOOGLE])
        ->first();

    $providerUser = Mockery::mock(SocialiteUser::class);
    $providerUser
        ->shouldReceive('getId')
        ->andReturn($externalAccount->provider_id)
        ->shouldReceive('getEmail')
        ->andReturn($externalAccount->user->email)
        ->shouldReceive('getAvatar')
        ->andReturn(fake()->imageUrl());

    $providerUser->refreshToken = Str::random(40);
    $providerUser->token = Str::random(40);
    $providerUser->user = [
        'given_name' => fake()->firstName(),
        'family_name' => fake()->lastName(),
    ];

    $syncAction = resolve(SyncExternalAccountAction::class);
    $createUserAction = resolve(CreateUserAction::class);
    $updateUserAction = resolve(UpdateUserAction::class);
    $updatedUser = $syncAction->execute(
        $externalAccount->provider,
        $providerUser,
        $createUserAction,
        $updateUserAction,
        [Role::USER],
        true
    );

    expect($updatedUser)
        ->toBeInstanceOf(User::class)
        ->and($updatedUser->email)->toBe($externalAccount->user->email)
        ->and($updatedUser->externalAccount->access_token)->toBe($providerUser->token)
        ->and($updatedUser->externalAccount->refresh_token)->toBe($providerUser->refreshToken)
        ->and($updatedUser->userProfile->given_name)->toBe($providerUser->user['given_name'])
        ->and($updatedUser->userProfile->family_name)->toBe($providerUser->user['family_name'])
        ->and($updatedUser->userProfile->profile_picture_path)->toBe($providerUser->getAvatar())
        ->and($updatedUser->email_verified_at)->not()->toBeNull();

    assertDatabaseCount('users', 1);
});

test('it can update an github existing user', /** @throws Throwable */ function (string $name) {
    // Create an existing user with an external account
    $user = UserFactory::new()->has(UserProfileFactory::new())->create();

    /** @var ExternalAccount $externalAccount */
    $externalAccount = ExternalAccountFactory::new()
        ->for($user)
        ->create(['provider' => ExternalLoginProvider::GITHUB])
        ->first();

    $providerUser = Mockery::mock(SocialiteUser::class);
    $providerUser
        ->shouldReceive('user')
        ->shouldReceive('getId')
        ->andReturn($externalAccount->provider_id)
        ->shouldReceive('getEmail')
        ->andReturn($externalAccount->user->email)
        ->shouldReceive('getAvatar')
        ->andReturn(fake()->imageUrl());

    $providerUser->refreshToken = Str::random(40);
    $providerUser->token = Str::random(40);
    $providerUser->user = [
        'name' => $name,
    ];

    $syncAction = resolve(SyncExternalAccountAction::class);
    $createUserAction = resolve(CreateUserAction::class);
    $updateUserAction = resolve(UpdateUserAction::class);
    $updatedUser = $syncAction->execute(
        $externalAccount->provider,
        $providerUser,
        $createUserAction,
        $updateUserAction,
        [Role::USER],
        true
    );

    expect($updatedUser)
        ->toBeInstanceOf(User::class)
        ->and($updatedUser->email)->toBe($externalAccount->user->email)
        ->and($updatedUser->externalAccount->access_token)->toBe($providerUser->token)
        ->and($updatedUser->externalAccount->refresh_token)->toBe($providerUser->refreshToken)
        ->and($updatedUser->userProfile->full_name)->toBe($providerUser->user['name'])
        ->and($updatedUser->userProfile->profile_picture_path)->toBe($providerUser->getAvatar())
        ->and($updatedUser->email_verified_at)->not()->toBeNull();

    assertDatabaseCount('users', 1);
})->with([
    'with a 2-part name' => ['name' => 'Bill Gates'],
    'with a 3-part name' => ['name' => 'Kobe Bean Bryant'],
    'with a 4-part name' => ['name' => ' John Jacobs David Washington'],
]);

test('it can throw a duplicate email exception', /** @throws Throwable */ function () {
    // Create an existing user with an external account
    $user = UserFactory::new()->has(UserProfileFactory::new());

    /** @var ExternalAccount $externalAccount */
    $externalAccount = ExternalAccountFactory::new()
        ->has($user)
        ->create(['provider' => ExternalLoginProvider::GOOGLE])
        ->first();

    $providerUser = Mockery::mock(SocialiteUser::class);
    $providerUser
        ->shouldReceive('user')
        ->shouldReceive('getId')
        ->andReturn(Str::uuid()->toString()) // different external account
        ->shouldReceive('getEmail')
        ->andReturn($externalAccount->user->email) // with the same email as an existing internal user
        ->shouldReceive('getAvatar')
        ->andReturn(fake()->imageUrl());

    $providerUser->refreshToken = Str::random(40);
    $providerUser->token = Str::random(40);
    $providerUser->user = [
        'given_name' => fake()->firstName(),
        'family_name' => fake()->lastName(),
    ];

    $syncAction = resolve(SyncExternalAccountAction::class);
    $createUserAction = resolve(CreateUserAction::class);
    $updateUserAction = resolve(UpdateUserAction::class);
    $syncAction->execute(
        $externalAccount->provider,
        $providerUser,
        $createUserAction,
        $updateUserAction,
        [Role::USER]
    );
})->throws(DuplicateEmailException::class);

test(
    'it will only update the `email` and token fields if `forceUpdate` is set to `false`', /** @throws Throwable */
    function () {

        // Create an existing user with an external account
        /** @var User $user */
        $user = UserFactory::new()->has(UserProfileFactory::new())->create()->load('userProfile');

        /** @var ExternalAccount $externalAccount */
        $externalAccount = ExternalAccountFactory::new()
            ->for($user)
            ->create(['provider' => ExternalLoginProvider::GOOGLE])
            ->first();

        $providerUser = Mockery::mock(SocialiteUser::class);
        $providerUser
            ->shouldReceive('user')
            ->shouldReceive('getId')
            ->andReturn($externalAccount->provider_id)
            ->shouldReceive('getEmail')
            ->andReturn($externalAccount->user->email)
            ->shouldReceive('getAvatar')
            ->andReturn(fake()->imageUrl());

        $providerUser->refreshToken = Str::random(40);
        $providerUser->token = Str::random(40);
        $providerUser->user = [
            'given_name' => fake()->firstName(),
            'family_name' => fake()->lastName(),
        ];

        $syncAction = resolve(SyncExternalAccountAction::class);
        $createUserAction = resolve(CreateUserAction::class);
        $updateUserAction = resolve(UpdateUserAction::class);
        $updatedUser = $syncAction->execute(
            $externalAccount->provider,
            $providerUser,
            $createUserAction,
            $updateUserAction,
            [Role::USER]
        );

        expect($updatedUser)
            ->toBeInstanceOf(User::class)
            ->and($updatedUser->email)->toBe($externalAccount->user->email)// from the provider
            ->and($updatedUser->externalAccount->access_token)->toBe($providerUser->token) // from the provider
            ->and($updatedUser->externalAccount->refresh_token)->toBe($providerUser->refreshToken) // from the provider
            ->and($updatedUser->userProfile->full_name)->toBe($user->userProfile->full_name) // not changed
            ->and($updatedUser->userProfile->profile_picture_path)->toBe($user->userProfile->profile_picture_path) // not changed
            ->and($updatedUser->email_verified_at)->not()->toBeNull();

        assertDatabaseCount('users', 1);
    }
);
