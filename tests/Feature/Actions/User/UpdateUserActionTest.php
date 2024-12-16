<?php

use App\Actions\User\UpdateUserAction;
use App\Enums\Gender;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed');
});

it('can update a user', /** @throws Throwable */ function () {
    $seededUser = UserFactory::new()->has(UserProfileFactory::new())->create();
    $gender = fake()->randomElement(Gender::toArray());
    $updateData = [
        'email' => 'jegramos.test@gmail.com',
        'username' => fake()->unique()->username(),
        'given_name' => fake()->unique()->firstName(),
        'family_name' => fake()->unique()->lastName(),
        'roles' => [Role::USER->value],
        'active' => true,
        'email_verified_at' => now(),
        'mobile_number' => fake()->phoneNumber(),
        'gender' => $gender,
        'birthday' => fake()->date(),
        'profile_picture_path' => fake()->filePath(),
        'country_id' => DB::table('countries')->first()->id,
        'address_line_1' => fake()->streetName(),
        'address_line_2' => fake()->streetName(),
        'address_line_3' => fake()->streetName(),
        'city_municipality' => fake()->city(),
        'province_state_county' => fake()->city(),
        'postal_code' => fake()->postcode(),
    ];
    $updateUserAction = resolve(UpdateUserAction::class);

    $createdUser = $updateUserAction->execute($seededUser, $updateData);
    expect($createdUser)
        ->toBeInstanceOf(App\Models\User::class)
        ->and($createdUser->email)->toBe($updateData['email'])
        ->and($createdUser->username)->toBe($updateData['username'])
        ->and($createdUser->active)->toBe($updateData['active'])
        ->and($createdUser->email_verified_at)->not->toBeNull()
        ->and($createdUser->roles->pluck('name')->toArray())->toBe($updateData['roles'])
        ->and($createdUser->userProfile->given_name)->toBe($updateData['given_name'])
        ->and($createdUser->userProfile->family_name)->toBe($updateData['family_name'])
        ->and($createdUser->userProfile->mobile_number)->toBe($updateData['mobile_number'])
        ->and($createdUser->userProfile->birthday->toDateString())->toBe($updateData['birthday'])
        ->and($createdUser->userProfile->gender->value)->toBe($updateData['gender'])
        ->and($createdUser->userProfile->address_line_1)->toBe($updateData['address_line_1'])
        ->and($createdUser->userProfile->address_line_2)->toBe($updateData['address_line_2'])
        ->and($createdUser->userProfile->address_line_3)->toBe($updateData['address_line_3'])
        ->and($createdUser->userProfile->city_municipality)->toBe($updateData['city_municipality'])
        ->and($createdUser->userProfile->province_state_county)->toBe($updateData['province_state_county'])
        ->and($createdUser->userProfile->postal_code)->toBe($updateData['postal_code'])
        ->and($createdUser->userProfile->country_id)->toBe($updateData['country_id'])
        ->and($createdUser->userProfile->profile_picture_path)->toBe($updateData['profile_picture_path']);
});

it('can reject non-whitelisted attributes', /** @throws Throwable */ function () {
    $seededUser = UserFactory::new()->has(UserProfileFactory::new())->create();

    // Note, the whitelisted attributes are the fillable keys of
    // the User (except 'password') and UserProfile models, and a 'roles' array
    $updateData = [
        'not_exists' => fake()->firstNameMale(),
        'password' => fake()->password(12),
    ];

    $updateUserAction = resolve(UpdateUserAction::class);
    $updateUserAction->execute($seededUser, $updateData);
})->throws(InvalidArgumentException::class);
