<?php

namespace App\Actions\User;

use App\Enums\Role;
use App\Models\AccountSettings;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * This action class handles the creation of a new user, including authentication data (`users` table),
 * user profile fields (`user_profiles` table), and role assignments. It performs all operations within a
 * database transaction to ensure data integrity.
 *
 * Example:
 * <code>
 * $userInfo = [
 *     'email' => $request->validated('email'),
 *     'username' => $request->validated('username'),
 *     'password' => $request->validated('password'),
 *     'given_name' => $request->validated('given_name'),
 *     'family_name' => $request->validated('family_name'),
 *     'email_verified_at': now(),
 * ];
 *
 * $user = $createUserAction->execute($userInfo); // Returns an Eloquent User Model
 * </code>
 */
readonly class CreateUserAction
{
    /**
     * @throws Throwable
     */
    public function execute(array $data): User
    {
        $whitelistedProperties = $this->getWhitelistedProperties();
        $nonWhitelistedKeys = Arr::except($data, $whitelistedProperties);
        if (!empty($nonWhitelistedKeys)) {
            $invalidKeys = implode(', ', array_keys($nonWhitelistedKeys));
            $validKeys = implode(', ', $whitelistedProperties);
            throw new InvalidArgumentException("The keys `$invalidKeys` are not allowed. The whitelisted properties are `$validKeys`.");
        }

        return DB::transaction(function () use ($data, $whitelistedProperties) {
            $filteredData = Arr::only($data, $whitelistedProperties);
            $user = User::query()->create(Arr::only($filteredData, $this->getUserWhiteListedProperties()));
            $user->userProfile()->create(Arr::only($filteredData, $this->getUserProfileWhitelistProperties()));

            $userRoles = empty($data['roles']) ? [Role::USER] : $data['roles'];
            $user->assignRole($userRoles);

            AccountSettings::createWithDefaults($user);

            return $user->load(['userProfile', 'roles']);
        });
    }

    private function getWhitelistedProperties(): array
    {
        $userWhitelist = $this->getUserWhiteListedProperties();
        $userProfileFillable = $this->getUserProfileWhitelistProperties();
        return array_merge(
            $userWhitelist,
            $userProfileFillable,
            ['roles']
        );
    }

    private function getUserWhiteListedProperties(): array
    {
        return (new User())->getFillable();
    }

    private function getUserProfileWhitelistProperties(): array
    {
        return (new UserProfile())->getFillable();
    }
}
