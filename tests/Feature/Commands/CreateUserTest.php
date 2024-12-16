<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed');
});

test('it can create a user via CLI inputs', function () {
    $email = fake()->unique()->safeEmail();
    $username = fake()->unique()->username();
    $givenName = fake()->firstName();
    $familyName = fake()->lastName();
    $password = 'TestPassword123!';
    $roles = [Role::USER->value, Role::ADMIN->value];
    artisan("user:create 
                        --given-name='$givenName' 
                        --family-name='$familyName' 
                        --email='$email'
                        --username='$username'
                        --password='$password'
                        --roles=$roles[0] --roles=$roles[1]")
        ->assertExitCode(0);

    $user = User::query()->first();

    expect($user->email)->toBe($email)
        ->and($user->username)->toBe($username)
        ->and(Hash::check($password, $user->password))->toBeTrue()
        ->and($user->userProfile->full_name)->toBe($givenName . ' ' . $familyName)
        ->and($roles)->toEqual($user->roles->map(fn (\Spatie\Permission\Models\Role $role) => $role->name)->toArray());
});

test('it can create a user in Interactive mode', function () {
    $email = fake()->unique()->safeEmail();
    $username = fake()->unique()->username();
    $givenName = fake()->firstName();
    $familyName = fake()->lastName();
    $password = fake()->password() . 'Jj1!';
    $roles = [Role::USER->value, Role::ADMIN->value];

    artisan("user:create -I")
        ->assertExitCode(0)
        ->expectsQuestion('Given Name', $givenName)
        ->expectsQuestion('Family Name', $familyName)
        ->expectsQuestion('Email', $email)
        ->expectsQuestion('Username', $username)
        ->expectsQuestion('Password', $password)
        ->expectsChoice('Roles', $roles, \Spatie\Permission\Models\Role::all()->pluck('name')->toArray());

    $user = User::query()->first();
    expect($user->email)->toBe($email)
        ->and($user->username)->toBe($username)
        ->and(Hash::check($password, $user->password))->toBeTrue()
        ->and($user->userProfile->full_name)->toBe($givenName . ' ' . $familyName)
        ->and($roles)->toEqual($user->roles->map(fn (\Spatie\Permission\Models\Role $role) => $role->name)->toArray());
});
