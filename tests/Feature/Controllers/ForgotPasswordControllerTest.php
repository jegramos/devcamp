<?php

use App\Enums\ErrorCode;
use App\Enums\SessionFlashKey;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\ExternalAccountFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    artisan('db:seed');
});

it('can show the forgot password page', function () {
    $response = get(route('auth.password.showForgotPasswordForm'));
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Auth/ForgotPasswordPage')
            ->has('sendResetLinkUrl')
    );
});

it('it can send reset password link notification', /** @throws Throwable */ function () {
    Notification::fake();

    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->create();

    followingRedirects()
        ->from(route('auth.password.showForgotPasswordForm'))
        ->post(route('auth.password.sendResetLink'), ['email' => $user->email])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Auth/ForgotPasswordPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('will not send the notification if email not found or user is disabled', /** @throws Throwable */ function () {
    Notification::fake();

    // User is inactive
    $inactiveUser = UserFactory::new()
        ->inactive()
        ->has(UserProfileFactory::new())
        ->create();

    post(route('auth.password.sendResetLink'), ['email' => $inactiveUser->email]);
    Notification::assertNotSentTo($inactiveUser, ResetPasswordNotification::class);

    $nonExistentEmail = fake()->unique()->safeEmail();
    post(route('auth.password.sendResetLink'), ['email' => $nonExistentEmail]);
    Notification::assertNotSentTo($inactiveUser, ResetPasswordNotification::class);
});

it('will send an error if user is from external provider', /** @throws Throwable */ function () {
    Notification::fake();

    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(ExternalAccountFactory::new())
        ->create([
            'password' => null
        ]);

    followingRedirects()
        ->from(route('auth.password.showForgotPasswordForm'))
        ->post(route('auth.password.sendResetLink'), ['email' => $user->email])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Auth/ForgotPasswordPage')
                ->has('errors.' . ErrorCode::BAD_REQUEST->value)
        );

    Notification::assertNotSentTo($user, ResetPasswordNotification::class);
});

it('can show reset password page', /** @throws Throwable */ function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(ExternalAccountFactory::new())
        ->create([
            'password' => null
        ]);

    $token = App::make('auth.password.broker')->createToken($user);

    $uri = route('auth.password.showResetPasswordForm') . '?email=' . $user->email . '&token=' . $token;
    $response = get($uri);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Auth/ResetPasswordPage')
            ->has('resetPasswordUrl')
    );
});

it('can reset password', /** @throws Throwable */ function () {
    $user = UserFactory::new()->has(UserProfileFactory::new())->create();

    $token = App::make('auth.password.broker')->createToken($user);
    $newPassword = fake()->password(10) . 'Jji1';
    $payload = [
        'token' => $token,
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ];

    followingRedirects()
        ->from(route('auth.password.showResetPasswordForm'))
        ->patch(route('auth.password.resetPassword'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Auth/LoginPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    $user->refresh();
    assertTrue(Hash::check($newPassword, $user->password));
});

it('can send error if the link is invalid', /** @throws Throwable */ function () {

    $user = UserFactory::new()->has(UserProfileFactory::new())->create();
    $token = App::make('auth.password.broker')->createToken($user);
    $newPassword = fake()->password(10) . 'Jji1';

    // Email is incorrect
    $payload = [
        'token' => $token,
        'email' => 'invalid-email',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ];

    followingRedirects()
        ->patch(route('auth.password.resetPassword'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('errors.' . ErrorCode::BAD_REQUEST->value)
        );

    // Email is incorrect
    $payload = [
        'token' => $token,
        'email' => 'incorrect-email',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ];

    followingRedirects()
        ->patch(route('auth.password.resetPassword'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('errors.' . ErrorCode::BAD_REQUEST->value)
        );

    // Token is incorrect
    $payload = [
        'token' => 'incorrect-token',
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ];

    followingRedirects()
        ->patch(route('auth.password.resetPassword'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('errors.' . ErrorCode::BAD_REQUEST->value)
        );
});
