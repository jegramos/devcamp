<?php

use App\Enums\Gender;
use App\Enums\SessionFlashKey;
use App\Models\User;
use App\Notifications\ConfirmEmailUpdateNotification;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    artisan('db:seed');
});

it('can show the profile page', function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->create();

    actingAs($user);

    $response = get(route('profile.index'));
    $response->assertOk();

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Account/ProfilePage')
            ->has('profile')
            ->has('countryOptions')
            ->has('updateProfileUrl')
            ->has('checkAvailabilityBaseUrl')
            ->has('uploadProfilePictureUrl')
            ->has('sendEmailUpdateConfirmationUrl')
            ->has('changePasswordUrl')
    );
});

it('can update the profile of the logged in user', function () {
    $user = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->create();

    actingAs($user);

    $updatePayload = [
        'given_name' => fake()->firstName(),
        'username' => fake()->unique()->username(),
        'mobile_number' => '+639064647210',
        'family_name' => fake()->lastName(),
        'gender' => fake()->randomElement(Gender::toArray()),
        'birthday' => fake()->date(),
        'country_id' => DB::table('countries')->inRandomOrder()->first()->id,
        'address_line_1' => fake()->streetAddress(),
        'address_line_2' => fake()->streetAddress(),
        'address_line_3' => fake()->streetAddress(),
        'city_municipality' => fake()->city(),
        'province_state_county' => fake()->city(),
        'postal_code' => fake()->postcode(),
    ];

    $response = patch(route('profile.update'), $updatePayload);
    $response->assertSessionHas(SessionFlashKey::CMS_SUCCESS->value);

    /** @var User $user */
    $user = $user->load('userProfile');
    expect($user->userProfile->mobile_number)->toBe($updatePayload['mobile_number'])
        ->and($user->username)->toBe($updatePayload['username'])
        ->and($user->userProfile->gender->value)->toBe($updatePayload['gender'])
        ->and($user->userProfile->given_name)->toBe($updatePayload['given_name'])
        ->and($user->userProfile->family_name)->toBe($updatePayload['family_name'])
        ->and($user->userProfile->country_id)->toBe($updatePayload['country_id'])
        ->and($user->userProfile->address_line_1)->toBe($updatePayload['address_line_1'])
        ->and($user->userProfile->address_line_2)->toBe($updatePayload['address_line_2'])
        ->and($user->userProfile->address_line_3)->toBe($updatePayload['address_line_3'])
        ->and($user->userProfile->city_municipality)->toBe($updatePayload['city_municipality'])
        ->and($user->userProfile->province_state_county)->toBe($updatePayload['province_state_county'])
        ->and($user->userProfile->postal_code)->toBe($updatePayload['postal_code']);
});

it('can validate if username is already taken', function () {
    $username = fake()->unique()->username();
    UserFactory::new()
        ->has(UserProfileFactory::new())
        ->create([
            'username' => $username,
        ]);

    $user = UserFactory::new()->has(UserProfileFactory::new())->create();
    actingAs($user);

    $updatePayload = [
        'given_name' => fake()->firstName(),
        'username' => $username,
        'mobile_number' => '+639064647210',
        'family_name' => fake()->lastName(),
        'gender' => fake()->randomElement(Gender::toArray()),
        'birthday' => fake()->date(),
        'country_id' => DB::table('countries')->inRandomOrder()->first()->id,
        'address_line_1' => fake()->streetAddress(),
        'address_line_2' => fake()->streetAddress(),
        'address_line_3' => fake()->streetAddress(),
        'city_municipality' => fake()->city(),
        'province_state_county' => fake()->city(),
        'postal_code' => fake()->postcode(),
    ];

    followingRedirects()
        ->from(route('profile.index'))
        ->patch(route('profile.update'), $updatePayload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/ProfilePage')
                ->has('errors.username')
        );
});

it('can validate mobile number if already taken', function () {
    $mobileNumber = '+639064647210';
    UserFactory::new()
        ->has(UserProfileFactory::new()->state(['mobile_number' => $mobileNumber]))
        ->create();

    $user = UserFactory::new()->has(UserProfileFactory::new())->create();
    actingAs($user);

    $updatePayload = [
        'given_name' => fake()->firstName(),
        'username' => fake()->unique()->username(),
        'mobile_number' => '+639064647210',
        'family_name' => fake()->lastName(),
        'gender' => fake()->randomElement(Gender::toArray()),
        'birthday' => fake()->date(),
        'country_id' => DB::table('countries')->inRandomOrder()->first()->id,
        'address_line_1' => fake()->streetAddress(),
        'address_line_2' => fake()->streetAddress(),
        'address_line_3' => fake()->streetAddress(),
        'city_municipality' => fake()->city(),
        'province_state_county' => fake()->city(),
        'postal_code' => fake()->postcode(),
    ];

    followingRedirects()
        ->from(route('profile.index'))
        ->patch(route('profile.update'), $updatePayload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/ProfilePage')
                ->has('errors.mobile_number')
        );
});

it("can upload the user's profile picture", function () {
    $user = UserFactory::new()->has(UserProfileFactory::new())->create();
    actingAs($user);

    $file = UploadedFile::fake()->image('fake_image.jpg', 500, 500);
    followingRedirects()
        ->from(route('profile.index'))
        ->post(route('profile.uploadProfilePicture'), ['photo' => $file])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/ProfilePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );
});

it('can request an email update confirmation notification', /** @throws Throwable */ function () {
    Notification::fake();

    /** @var User $user */
    $user = UserFactory::new()->has(UserProfileFactory::new())->create();
    actingAs($user);

    $newEmail = fake()->unique()->safeEmail();
    followingRedirects()
        ->from(route('profile.index'))
        ->post(route('profile.sendEmailUpdateConfirmation'), ['email' => $newEmail])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/ProfilePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    Notification::assertSentOnDemand(ConfirmEmailUpdateNotification::class);
});

it('can confirm the email update notification', function () {
    $user = UserFactory::new()->has(UserProfileFactory::new())->create();

    // The user can verify without logging in
    $newEmail = fake()->unique()->safeEmail();
    $actionUrl = (new ConfirmEmailUpdateNotification($user, $newEmail))->actionUrl;

    followingRedirects()
        ->get($actionUrl)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Auth/LoginPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_EMAIL_UPDATE_CONFIRMED->value, null)
        );

    $user->refresh();
    assertEquals($newEmail, $user->email);

    // The user can do it when logged in
    actingAs($user);

    $newEmail = fake()->unique()->safeEmail();
    $actionUrl = (new ConfirmEmailUpdateNotification($user, $newEmail))->actionUrl;

    followingRedirects()
        ->from(route('profile.index'))
        ->get($actionUrl)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/ProfilePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_EMAIL_UPDATE_CONFIRMED->value, null)
        );

    $user->refresh();
    assertEquals($newEmail, $user->email);
});

it('can change password', function () {
    $oldPassword = fake()->password() . 'Jj1!';
    $user = UserFactory::new()->has(UserProfileFactory::new())->create([
        'password' => Hash::make($oldPassword)
    ]);
    actingAs($user);

    $newPassword = fake()->password . 'Jj1!';

    followingRedirects()
        ->from(route('profile.index'))
        ->patch(route('profile.changePassword'), [
            'old_password' => $oldPassword,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/ProfilePage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    $user->refresh();
    assertTrue(Hash::check($newPassword, $user->password));
});

it('can reject password change if old password is incorrect', function () {
    $oldPassword = fake()->password() . 'Jj1!';
    $user = UserFactory::new()->has(UserProfileFactory::new())->create([
        'password' => Hash::make($oldPassword)
    ]);
    actingAs($user);

    $newPassword = fake()->password . 'Jj1!';

    followingRedirects()
        ->from(route('profile.index'))
        ->patch(route('profile.changePassword'), [
            'old_password' => 'incorrect-old-password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ])
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Account/ProfilePage')
                ->has('errors.' . 'old_password')
        );
});
