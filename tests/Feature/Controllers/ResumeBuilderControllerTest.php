<?php

use App\Enums\SessionFlashKey;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\ResumeFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNull;

beforeEach(function () {
    artisan('db:seed');
});

it('can save subdomain (only letters, numbers, and dashes allowed)', function (string $subdomain, bool $valid) {
    $user = UserFactory::new()
        ->withSubdomain('test-domain')
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    if ($valid) {
        followingRedirects()
            ->from(route('builder.resume.index'))
            ->post(route('builder.resume.storeSubdomain'), ['subdomain' => $subdomain])
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Builder/Resume/ResumePage')
                    ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
            );

        $user->refresh();
        assertEquals($subdomain, $user->subdomain);
        return;
    }

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeSubdomain'), ['subdomain' => $subdomain])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->has('errors.subdomain')
        );

    $user->refresh();
    assertNotEquals($subdomain, $user->subdomain);
})->with([
    'with dash' => ['subdomain' => 'new-domain', 'valid' => true],
    'with number' => ['subdomain' => 'new01domain', 'valid' => true],
    'with special characters' => ['subdomain' => 'test-domain-!test', 'valid' => false],
    'with extra dots' => ['subdomain' => 'juan.delacruz', 'valid' => false],
]);

it('checks if the subdomain is unique', function () {
    $existingSubdomain = 'test-domain';
    UserFactory::new()
        ->withSubdomain($existingSubdomain)
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(ResumeFactory::new())
        ->create();

    actingAs($user);

    followingRedirects()
        ->from(route('builder.resume.index'))
        ->post(route('builder.resume.storeSubdomain'), ['subdomain' => $existingSubdomain])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Builder/Resume/ResumePage')
                ->has('errors.subdomain')
        );

    $user->refresh();
    assertNull($user->subdomain);
});
