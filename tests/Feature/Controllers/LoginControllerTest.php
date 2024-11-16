<?php

use App\Enums\ErrorCode;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    artisan('db:seed');
});

it('can show the login form', function () {
    $response = get(route('auth.login.showForm'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Auth/LoginPage')
            ->has('registerUrl')
            ->has('authenticateUrl')
    );
});

it('can authenticate a user', function ($username, $email, $password) {
    $payload = ['username' => $username, 'email' => $email, 'password' => $password, 'remember' => false];

    UserFactory::new()
        ->has(UserProfileFactory::new())
        ->create([
            'username' => $username ?? fake()->unique()->userName(),
            'email' => $email ?? fake()->unique()->safeEmail(),
            'password' => $password,
        ]);

    $response = post(route('auth.login.authenticate'), $payload);
    $response->assertRedirect(route('builder.resume.index'));
    assertAuthenticated();
})->with([
    'username and password' => [
        'username' => fake()->unique()->userName(), 'email' => null, 'password' => fake()->password(10)
    ],
    'email and password' => [
        'username' => null, 'email' => fake()->unique()->safeEmail(), 'password' => fake()->password(10)
    ]
]);

it('returns an error if the credentials are invalid', function () {
    $payload = [
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => fake()->password(10),
        'remember' => false,
    ];

    followingRedirects()
        ->post(route('auth.login.authenticate'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('errors.' . ErrorCode::INVALID_CREDENTIALS->value)
        );
});

it('returns an error if the rate limit is exceeded', function () {
    $payload = [
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => fake()->password(10),
        'remember' => false,
    ];

    // We forcefully hit the login throttle limit
    $loginThrottleLimit = 5;
    $tries = 1;
    while ($tries <= $loginThrottleLimit) {
        post(route('auth.login.authenticate'), $payload);
        $tries++;
    }

    followingRedirects()
        ->post(route('auth.login.authenticate'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
            ->has('errors.' . ErrorCode::TOO_MANY_REQUESTS->value)
        );
});
