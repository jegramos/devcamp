<?php

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\SessionFlashKey;
use App\Models\AccountSettings;
use App\Models\ExternalAccount;
use App\Models\Passkey;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\VerifyAccountNotification;
use Database\Factories\AccountSettingsFactory;
use Database\Factories\ExternalAccountFactory;
use Database\Factories\PasskeyFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserProfileFactory;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\followingRedirects;
use function PHPUnit\Framework\assertCount;

beforeEach(function () {
    artisan('db:seed');
});

it('can display a paginated list of users', function () {
    UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->count(14)
        ->create();

    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);

    actingAs($admin);

    followingRedirects()
        ->get(route('users.index'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->count('users.data', 12)
                ->has('users.links')
                ->has('users.current_page')
                ->has('users.total')
                ->has('users.per_page')
                ->has('users.next_page_url')
                ->has('users.prev_page_url')
                ->has('users.first_page_url')
                ->has('users.last_page_url')
                ->has('users.last_page_url')
                ->has('users.from')
                ->has('users.to')
        );
});

it('can block users without proper permissions', function () {
    UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->count(3)#
        ->create();

    $regularUser = tap(UserFactory::new()->has(UserProfileFactory::new())->has(AccountSettingsFactory::new())->create(), function (User $user) {
        $user->assignRole(Role::USER); // User roles do not have the view_users permission
    });

    actingAs($regularUser);

    followingRedirects()
        ->get(route('users.index'))
        ->assertStatus(403);
});

it('can search users via name', function (string $searchQuery, int $expectedCount) {
    UserFactory::new()
        ->has(UserProfileFactory::new()->state(['given_name' => 'Jose', 'family_name' => 'Rizal']))
        ->has(AccountSettingsFactory::new())
        ->create();

    UserFactory::new()
        ->has(UserProfileFactory::new()->state(['given_name' => 'Marco', 'family_name' => 'Rizal']))
        ->has(AccountSettingsFactory::new())
        ->create();

    /** @var User $user */
    $user = UserFactory::new()->has(UserProfileFactory::new()->state(['given_name' => 'Jeg', 'family_name' => 'Ramos']))->has(AccountSettingsFactory::new())->create();
    $user->assignRole(Role::ADMIN);

    actingAs($user);

    followingRedirects()
        ->get(route('users.index') . "?q=$searchQuery")
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->count('users.data', $expectedCount)
        );
})->with([
    'last name only' => ['searchQuery' => 'riZal', 'expectedCount' => 2],
    'first name only' => ['searchQuery' => 'JoSE', 'expectedCount' => 1],
    'full name' => ['searchQuery' => 'JoSE Rizal', 'expectedCount' => 1],
    'incomplete full name' => ['searchQuery' => 'JoSE Riz', 'expectedCount' => 1],
]);

it('can filter users via role', function (string $role, int $expectedCount) {
    /** @var User $regularUser */
    $regularUser = UserFactory::new()
        ->has(UserProfileFactory::new()->state(['given_name' => 'Jose', 'family_name' => 'Rizal']))
        ->has(AccountSettingsFactory::new())
        ->create();
    $regularUser->assignRole(Role::USER);

    /** @var User $superUser */
    $superUser = UserFactory::new()
        ->has(UserProfileFactory::new()->state(['given_name' => 'Marco', 'family_name' => 'Rizal']))
        ->has(AccountSettingsFactory::new())
        ->create();
    $superUser->assignRole(Role::SUPER_USER);

    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);

    actingAs($admin);

    followingRedirects()
        ->get(route('users.index') . "?role=$role")
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->count('users.data', $expectedCount)
        );
})->with([
    'last name only' => ['role' => Role::USER->value, 'expectedCount' => 1],
    'first name only' => ['role' => Role::ADMIN->value, 'expectedCount' => 1],
    'full name' => ['role' => Role::SUPER_USER->value, 'expectedCount' => 1],
]);

it('can filter users with verified emails', function () {
    // Create 1 unverified
    UserFactory::new()
        ->unverified()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();

    // Create 1 verified
    UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();

    // Plus +1 verified as the logged-in user
    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);

    actingAs($admin);

    followingRedirects()
        ->get(route('users.index') . "?verified=0")
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->count('users.data', 1)
        );

    followingRedirects()
        ->get(route('users.index') . "?verified=1")
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->count('users.data', 2)
        );
});

it('can filter users via activation status', function () {
    // Create 1 inactive
    UserFactory::new()
        ->inactive()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();

    // Create 1 active
    UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();


    // Plus +1 active as the logged-in user
    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);

    actingAs($admin);

    followingRedirects()
        ->get(route('users.index') . "?active=0")
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->count('users.data', 1)
        );

    followingRedirects()
        ->get(route('users.index') . "?active=1")
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->count('users.data', 2)
        );
});

it('can sort users', function () {
    UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->count(2)
        ->create();

    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);

    actingAs($admin);

    // Defaults to latest first
    followingRedirects()
        ->get(route('users.index'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->where('users.data.0.id', $admin->id)
        );

    // Oldest first
    $user = User::query()->orderBy('created_at')->first();
    followingRedirects()
        ->get(route('users.index') . '?sort=created_at')
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->where('users.data.0.id', $user->id)
        );

    // Latest first
    $user = User::query()->orderByDesc('created_at')->first();
    followingRedirects()
        ->get(route('users.index') . '?sort=-created_at')
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->where('users.data.0.id', $user->id)
        );

    // Last Name ASC
    $profile = UserProfile::query()->orderBy('family_name')->first();
    followingRedirects()
        ->get(route('users.index') . '?sort=family_name')
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->where('users.data.0.id', $profile->user_id)
        );

    // Family Name DESC
    $profile = UserProfile::query()->orderByDesc('family_name')->first();
    followingRedirects()
        ->get(route('users.index') . '?sort=-family_name')
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->where('users.data.0.id', $profile->user_id)
        );

    // Given Name DESC
    $profile = UserProfile::query()->orderByDesc('given_name')->first();
    followingRedirects()
        ->get(route('users.index') . '?sort=-given_name')
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->where('users.data.0.id', $profile->user_id)
        );

    // Given Name ASC
    $profile = UserProfile::query()->orderBy('given_name')->first();
    followingRedirects()
        ->get(route('users.index') . '?sort=given_name')
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->where('users.data.0.id', $profile->user_id)
        );
});

it('can create a user', /** @throws Throwable */ function (array $payload) {
    Notification::fake();

    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);

    actingAs($admin);

    followingRedirects()
        ->from(route('users.index'))
        ->post(route('users.store'), $payload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    assertDatabaseCount('users', 2);

    $user = User::query()->with('userProfile')->where('email', $payload['email'])->first();
    Notification::assertSentTo($user, VerifyAccountNotification::class);

    $roles = $user->roles()->pluck('name')->toArray();
    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->email)->toBe($payload['email'])
        ->and($user->username)->toBe($payload['username'])
        ->and($roles)->toBe($payload['roles'])
        ->and($user->userProfile->mobile_number)->toBe($payload['mobile_number'] ?? null)
        ->and($user->userProfile->gender?->value)->toBe($payload['gender'] ?? null)
        ->and($user->userProfile->birthday?->toDateString())->toBe($payload['birthday'] ?? null)
        ->and($user->userProfile->address_line_1)->toBe($payload['address_line_1'] ?? null)
        ->and($user->userProfile->address_line_2)->toBe($payload['address_line_2'] ?? null)
        ->and($user->userProfile->address_line_3)->toBe($payload['address_line_3'] ?? null)
        ->and($user->userProfile->city_municipality)->toBe($payload['city_municipality'] ?? null)
        ->and($user->userProfile->province_state_county)->toBe($payload['province_state_county'] ?? null)
        ->and($user->userProfile->postal_code)->toBe($payload['postal_code'] ?? null);

})->with([
    'required only' => [
        'payload' => [
            'given_name' => 'Jose',
            'family_name' => 'Rizal',
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
            'roles' => [Role::USER->value, Role::ADMIN->value],
        ]
    ],
    'with optional fields' => [
        'payload' => [
            'given_name' => 'Jose',
            'family_name' => 'Rizal',
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
            'roles' => [Role::USER->value, Role::ADMIN->value],
            'mobile_number' => '+639123456789',
            'gender' => Gender::MALE->value,
            'birthday' => fake()->date(),
            'address_line_1' => '1234 Main St',
            'address_line_2' => 'Apt 123',
            'address_line_3' => 'Apartment 123',
            'city_municipality' => fake()->city(),
            'province_state_county' => fake()->city(),
            'postal_code' => fake()->postcode(),
        ]
    ]
]);

it('can update a user', /** @throws Throwable */ function () {
    $userToBeEdited = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();

    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);
    actingAs($admin);

    $updatePayload = [
        'given_name' => 'Jose',
        'family_name' => 'Rizal',
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'TestPassword123!',
        'password_confirmation' => 'TestPassword123!',
        'roles' => [Role::USER->value, Role::ADMIN->value],
        'mobile_number' => '+639123456789',
        'gender' => Gender::MALE->value,
        'birthday' => fake()->date(),
        'address_line_1' => '1234 Main St',
        'address_line_2' => 'Apt 123',
        'address_line_3' => 'Apartment 123',
        'city_municipality' => fake()->city(),
        'province_state_county' => fake()->city(),
        'postal_code' => fake()->postcode(),
        'active' => false,
        'verified' => false,
    ];

    followingRedirects()
        ->from(route('users.index'))
        ->patch(route('users.update', ['user' => $userToBeEdited]), $updatePayload)
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    $user = User::query()->with('userProfile')->where('email', $updatePayload['email'])->first();

    $roles = $user->roles()->pluck('name')->toArray();
    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->email)->toBe($updatePayload['email'])
        ->and($user->username)->toBe($updatePayload['username'])
        ->and($roles)->toBe($updatePayload['roles'])
        ->and($user->userProfile->mobile_number)->toBe($updatePayload['mobile_number'])
        ->and($user->userProfile->gender?->value)->toBe($updatePayload['gender'])
        ->and($user->userProfile->birthday?->toDateString())->toBe($updatePayload['birthday'])
        ->and($user->userProfile->address_line_1)->toBe($updatePayload['address_line_1'])
        ->and($user->userProfile->address_line_2)->toBe($updatePayload['address_line_2'])
        ->and($user->userProfile->address_line_3)->toBe($updatePayload['address_line_3'])
        ->and($user->userProfile->city_municipality)->toBe($updatePayload['city_municipality'])
        ->and($user->userProfile->province_state_county)->toBe($updatePayload['province_state_county'])
        ->and($user->userProfile->postal_code)->toBe($updatePayload['postal_code'])
        ->and($user->active)->toBe($updatePayload['active'])
        ->and($user->email_verified_at)->toBeNull();
});

it('can delete a user', function () {
    $userToBeDeleted = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->has(PasskeyFactory::new())
        ->has(ExternalAccountFactory::new())
        ->create();

    /** @var User $admin */
    $admin = UserFactory::new()
        ->has(UserProfileFactory::new())
        ->has(AccountSettingsFactory::new())
        ->create();
    $admin->assignRole(Role::ADMIN);
    actingAs($admin);

    followingRedirects()
        ->from(route('users.index'))
        ->delete(route('users.destroy', ['user' => $userToBeDeleted]))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/UserManagementPage')
                ->whereNot('flash.' . SessionFlashKey::CMS_SUCCESS->value, null)
        );

    // Assert the soft-deletion of the user and their relationships
    assertCount(1, User::onlyTrashed()->get());
    assertCount(1, UserProfile::onlyTrashed()->get());
    assertCount(1, ExternalAccount::onlyTrashed()->get());
    assertCount(1, Passkey::onlyTrashed()->get());
    assertCount(1, AccountSettings::onlyTrashed()->get());
});
