<?php

use App\Actions\User\CreateUserAction;
use App\Enums\Gender;
use App\Enums\Role;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed');
});

it('can create a user', /** @throws Throwable */ function () {
    $action = resolve(CreateUserAction::class);
    $gender = fake()->randomElement(Gender::toArray());
    $userInfo = [
        'email' => fake()->unique()->safeEmail(),
        'username' => fake()->unique()->userName(),
        'password' => fake()->password(10),
        'given_name' => fake()->firstName($gender),
        'family_name' => fake()->lastName(),
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

    $createdUser = $action->execute($userInfo);
    expect($createdUser)
        ->toBeInstanceOf(App\Models\User::class)
        ->and($createdUser->email)->toBe($userInfo['email'])
        ->and($createdUser->username)->toBe($userInfo['username'])
        ->and($createdUser->active)->toBe($userInfo['active'])
        ->and($createdUser->email_verified_at)->not->toBeNull()
        ->and($createdUser->roles->pluck('name')->toArray())->toBe($userInfo['roles'])
        ->and($createdUser->userProfile->given_name)->toBe($userInfo['given_name'])
        ->and($createdUser->userProfile->family_name)->toBe($userInfo['family_name'])
        ->and($createdUser->userProfile->mobile_number)->toBe($userInfo['mobile_number'])
        ->and($createdUser->userProfile->birthday->toDateString())->toBe($userInfo['birthday'])
        ->and($createdUser->userProfile->gender->value)->toBe($userInfo['gender'])
        ->and($createdUser->userProfile->address_line_1)->toBe($userInfo['address_line_1'])
        ->and($createdUser->userProfile->address_line_2)->toBe($userInfo['address_line_2'])
        ->and($createdUser->userProfile->address_line_3)->toBe($userInfo['address_line_3'])
        ->and($createdUser->userProfile->city_municipality)->toBe($userInfo['city_municipality'])
        ->and($createdUser->userProfile->province_state_county)->toBe($userInfo['province_state_county'])
        ->and($createdUser->userProfile->postal_code)->toBe($userInfo['postal_code'])
        ->and($createdUser->userProfile->country_id)->toBe($userInfo['country_id'])
        ->and($createdUser->userProfile->profile_picture_path)->toBe($userInfo['profile_picture_path']);
});
