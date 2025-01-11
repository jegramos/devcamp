<?php

use Database\Factories\AccountSettingsFactory;
use Database\Factories\ResumeFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;

beforeEach(function () {
    artisan('db:seed');
});

it('it show an existing accounts portfolio', function () {
    $user = UserFactory::new()
        ->state(['subdomain' => 'test-domain'])
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    $url = 'https://' . $user->subdomain . '.' . Config::get('app.portfolio_subdomain');
    $portfolioPage = $user->resume->theme->page;
    followingRedirects()
        ->get($url)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component("Portfolio/$portfolioPage")
        );

    // Not existing
    $url = 'https://does-not-exist.' . Config::get('app.portfolio_subdomain');
    followingRedirects()
        ->get($url)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('ErrorPage')
        );
});

it('it does not show the portfolio if the account is deactivated', function () {
    $user = UserFactory::new()
        ->inactive()
        ->state(['subdomain' => 'test-domain'])
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    $url = 'https://' . $user->subdomain . '.' . Config::get('app.portfolio_subdomain');
    followingRedirects()
        ->get($url)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('ErrorPage')
        );
});
