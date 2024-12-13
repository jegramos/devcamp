<?php

use Database\Factories\AccountSettingsFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\post;

beforeEach(function () {
    artisan('db:seed');
});

it('can logout the current authenticated user', function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    actingAs($user);

    $response = post(route('auth.logout.current'));
    $response->assertRedirect();
    assertGuest();
});
