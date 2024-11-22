<?php

use App\Enums\SessionFlashKey;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function PHPUnit\Framework\assertNotNull;

beforeEach(function () {
    artisan('db:seed');
});

it('can show the verify email notice page', function () {
    $user = UserFactory::new()
        ->unverified()
        ->has(UserProfileFactory::new())
        ->create();

    actingAs($user);

    $response = get(route('verification.notice'));
    $response->assertSuccessful();
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Auth/VerifyEmailNoticePage')
            ->has('sendEmailVerificationUrl')
            ->has('emailVerificationExpiration')
            ->has('resumeBuilderUrl')
    );
});

it('can resend the verification email notification', /** @throws Throwable */ function () {
    Notification::fake();

    $user = UserFactory::new()
        ->unverified()
        ->has(UserProfileFactory::new())
        ->create();

    actingAs($user);

    followingRedirects()
        ->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Auth/VerifyEmailNoticePage')
                ->has('flash.' . SessionFlashKey::CMS_SUCCESS->value)
        );

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('can verify the email address', function () {
    // It can verify without logging in
    $user = UserFactory::new()
        ->unverified()
        ->has(UserProfileFactory::new())
        ->create();

    $expirationMinutes = Config::get('auth.verification.expire', 60);
    $notification = new VerifyEmailNotification($user, $expirationMinutes);

    followingRedirects()
            ->from(route('verification.notice'))
            ->get($notification->actionUrl)
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                ->whereNot('flash.' . SessionFlashKey::CMS_EMAIL_VERIFIED->value, null)
            );

    $user->refresh();
    assertNotNull($user->email_verified_at);

    // It can verify with a logged-in user
    $user->update(['email_verified_at' => null]);
    actingAs($user);

    followingRedirects()
        ->get($notification->actionUrl)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->whereNot('flash.' . SessionFlashKey::CMS_EMAIL_VERIFIED->value, null)
        );

    $user->refresh();
    assertNotNull($user->email_verified_at);
});
