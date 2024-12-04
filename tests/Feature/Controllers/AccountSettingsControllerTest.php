<?php

use App\Enums\SessionFlashKey;
use App\Enums\Theme;
use App\Models\User;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    artisan('db:seed');
});

it('can show the account settings page', function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();

    actingAs($user);

    $response = get(route('accountSettings.index'));
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Account/AccountSettingsPage')
            ->has('storeAccountSettingsUrl')
            ->has('createPasskeyUrl')
            ->has('destroyPasskeyUrl')
            ->has('currentSettings')
            ->has('passkeyRegisterOptions')
            ->has('passkeys')
    );
});

it('can update the app theme', function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new()->setTheme(Theme::LIGHT))
        ->create();

    actingAs($user);
    $oldTheme = $user->accountSettings->currentTheme();

    followingRedirects()
        ->from(route('accountSettings.index'))
        ->post(route('accountSettings.store'), ['theme' => Theme::DARK->value])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/AccountSettingsPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    $user->refresh();

    assertNotEquals($oldTheme, $user->accountSettings->currentTheme());
});

it('can toggle passkey authentication', function () {
    /** @var User $user */
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new()->passkeyLoginEnabled())
        ->create();

    actingAs($user);

    // Disabled passkeys
    followingRedirects()
        ->from(route('accountSettings.index'))
        ->post(route('accountSettings.store'), ['passkeys_enabled' => false])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/AccountSettingsPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );
    $user->refresh();
    assertFalse($user->accountSettings->passkeysEnabled());

    // Enable passkeys
    post(route('accountSettings.store'), ['passkeys_enabled' => true]);
    $user->refresh();
    assertTrue($user->accountSettings->passkeysEnabled());
});

it('can toggle 2FA', function () {
    /** @var User $user */
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new()->twoFactorAuthEnabled())
        ->create();

    actingAs($user);

    // Disabled passkeys
    followingRedirects()
        ->from(route('accountSettings.index'))
        ->post(route('accountSettings.store'), ['2fa_enabled' => false])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/AccountSettingsPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );
    $user->refresh();
    assertFalse($user->accountSettings->twoFactorAuthEnabled());

    // Enable passkeys
    post(route('accountSettings.store'), ['2fa_enabled' => true]);
    $user->refresh();
    assertTrue($user->accountSettings->twoFactorAuthEnabled());
});
