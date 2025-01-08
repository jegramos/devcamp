<?php

use App\Enums\SessionFlashKey;
use App\Models\User;
use App\Notifications\PortfolioInquiryNotification;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\ResumeFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;

beforeEach(function () {
    artisan('db:seed');
});

it('can send an inquiry', /** @throws Throwable */ function () {
    Notification::fake();

    /** @var User $user */
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    $user->update(['subdomain' => 'test-domain']);

    $payload = [
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'message' => fake()->paragraph(),
    ];

    followingRedirects()
        ->post(route('portfolio.contact', ['account' => $user->subdomain]), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
            ->component('Portfolio/' . $user->resume->theme->page)
            ->whereNot('flash.' . SessionFlashKey::PORTFOLIO_SUCCESS->value, null)
        );

    Notification::assertSentTo($user, PortfolioInquiryNotification::class);
});
