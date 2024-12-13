<?php

use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;

it('can show the registration form', function () {
    $response = get(route('auth.register.showForm'));
    $response->assertOk();
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Auth/RegisterPage')
            ->has('loginUrl')
            ->has('processRegistrationUrl')
            ->has('checkAvailabilityBaseUrl')
            ->has('countryOptions')
            ->has('recaptchaEnabled')
            ->has('recaptchaSiteKey')
            ->has('loginViaGoogleUrl')
            ->has('loginViaGithubUrl')
            ->has('resumeBuilderUrl')
    );
});

describe('with database access access', function () {
    beforeEach(function () {
        artisan('db:seed');
    });

    it('can accept registration without recaptcha', function () {
        config()->set('services.google.recaptcha.enabled', false);

        $username = fake()->unique()->userName();
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(10) . 'Jj1!';
        $givenName = fake()->firstNameMale();
        $familyName = fake()->lastName();
        $country_id = DB::table('countries')->inRandomOrder()->first()->id;
        $payload = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'given_name' => $givenName,
            'family_name' => $familyName,
            'country_id' => $country_id,
        ];

        followingRedirects()
            ->post(route('auth.register.process'), $payload)
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                ->component('Auth/VerifyEmailNoticePage')
            );

        assertAuthenticated();
    });

    it('can enforce captcha', function () {
        config()->set('services.google.recaptcha.enabled', true);

        $username = fake()->unique()->userName();
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(10) . 'Jj1!';
        $givenName = fake()->firstNameMale();
        $familyName = fake()->lastName();
        $country_id = DB::table('countries')->inRandomOrder()->first()->id;

        $payload = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'given_name' => $givenName,
            'family_name' => $familyName,
            'country_id' => $country_id,
            'recaptcha_response_token' => null,
        ];

        followingRedirects()
            ->post(route('auth.register.process'), $payload)
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('errors.' . 'recaptcha_response_token')
            );
    });

    it('can validate recaptcha token if enabled', function () {
        config()->set('services.google.recaptcha.enabled', true);

        $username = fake()->unique()->userName();
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(10) . 'Jj1!';
        $givenName = fake()->firstNameMale();
        $familyName = fake()->lastName();
        $country_id = DB::table('countries')->inRandomOrder()->first()->id;

        $payload = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'given_name' => $givenName,
            'family_name' => $familyName,
            'country_id' => $country_id,
            // The .env.testing has an always 'success' config set for recaptcha
            // see https://developers.google.com/recaptcha/docs/faq
            'recaptcha_response_token' => 'test_token',
        ];

        followingRedirects()
            ->post(route('auth.register.process'), $payload)
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Auth/VerifyEmailNoticePage')
            );

        assertAuthenticated();
    });
});
