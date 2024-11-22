<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * This action class handles the update of an existing user, including authentication data (`users` table),
 * user profile fields (`user_profiles` table), and role assignments. It performs all operations within a
 * database transaction to ensure data integrity.
 *
 * Example:
 * <code>
 * $user = User::find(123);
 * $updateData = [
 *     'given_name' => 'Ramon',
 *     'family_name' => 'Magsaysay',
 *     'email' => 'example@email.com',
 *     'roles' => [Role::ADMIN, Role::USER]
 * ];
 *
 * $updatedUser = $updateUserAction->execute($user, $updateData);
 * </code>
 */
readonly class UpdateUserAction
{
    /**
     * @throws Throwable
     */
    public function execute(User $user, array $data): User
    {
        $whitelistedProperties = $this->getWhitelistedProperties();
        $nonWhitelistedKeys = Arr::except($data, $whitelistedProperties);
        if (!empty($nonWhitelistedKeys)) {
            $invalidKeys = implode(', ', array_keys($nonWhitelistedKeys));
            $validKeys = implode(', ', $whitelistedProperties);
            throw new InvalidArgumentException("The keys `$invalidKeys` are not allowed. The whitelisted properties are `$validKeys`.");
        }

        return DB::transaction(function () use ($user, $data, $whitelistedProperties) {
            $filteredData = Arr::only($data, $whitelistedProperties);
            $user->update(Arr::only($filteredData, $this->getUserWhiteListedProperties()));
            $user->userProfile()->update(Arr::only($filteredData, $this->getUserProfileWhitelistProperties()));

            if (isset($filteredData['roles'])) {
                $user->syncRoles($filteredData['roles']);
            }

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
        $userFillable = (new User())->getFillable();

        // Don't include the password when updating profile information.
        return array_values(array_filter($userFillable, fn (string $f) => $f !== 'password'));
    }

    private function getUserProfileWhitelistProperties(): array
    {
        return (new UserProfile())->getFillable();
    }
}
