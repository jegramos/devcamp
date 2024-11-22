<?php

namespace App\Actions\User;

use App\Enums\ExternalLoginProvider;
use App\Exceptions\DuplicateEmailException;
use App\Models\ExternalAccount;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;
use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * This action class either creates an internal user if no external account is bound yet
 * or updates the internal user details if there is already an external account bound.
 *
 * Example:
 * <code>
 * $config = config('services.google.oauth');
 * $provider = Socialite::buildProvider(GoogleProvider::class, $config);
 *
 * $user = syncExternalAccountAction->execute(
 *      ExternalLoginProvider::GOOGLE,
 *      $provider->user(),
 *      $createUserAction,
 *      $updateUserAction,
 *      [Role::USER]
 * );
 * </code>
 */
readonly class SyncExternalAccountAction
{
    /**
     * @throws Throwable
     */
    public function execute(
        ExternalLoginProvider $provider,
        ProviderUser          $providerAccount,
        CreateUserAction      $createUserAction,
        UpdateUserAction      $updateUserAction,
        array                 $roles = [],
        bool                  $forceUpdate = false
    ): User {
        return DB::transaction(function () use ($provider, $providerAccount, $createUserAction, $updateUserAction, $roles, $forceUpdate) {
            // Check if there is an existing record for the `provider` & `provider_id`
            $providerUserId = $providerAccount->getId();
            $providerUserEmail = $providerAccount->getEmail();
            $externalAccount = ExternalAccount::query()
                ->where('provider', $provider)
                ->where('provider_id', $providerUserId)
                ->first();

            if (!$externalAccount) {
                /**
                 * If there is not existing record, we create a new user with an external account,
                 * but we check first if the email is not already taken
                 */
                $existingUserFound = User::query()->where('email', $providerUserEmail)->exists();
                if ($existingUserFound) {
                    throw new DuplicateEmailException(
                        "Aborted user creation. The email '$providerUserEmail' already exists."
                    );
                }

                $userInfo = [
                    'email' => $providerUserEmail,
                    'username' => $provider->value . '-user-' . Str::uuid()->toString(),
                    'password' => null,
                    'given_name' => $this->parseGivenName($provider, $providerAccount),
                    'family_name' => $this->parseFamilyName($provider, $providerAccount),
                    'email_verified_at' => now(),
                    'profile_picture_path' => $providerAccount->getAvatar(),
                ];

                $user = $createUserAction->execute($userInfo);
                $user->externalAccount()->create([
                    'provider' => $provider,
                    'provider_id' => $providerUserId,
                    'access_token' => $providerAccount->token,
                    'refresh_token' => $providerAccount->refreshToken,
                ]);

                if (!empty($roles)) {
                    $user->syncRoles($roles);
                }

                return $user->fresh(['userProfile', 'roles', 'externalAccount']);
            }

            // If there is an existing record, we update the tokens and update the user profile
            $externalAccount->access_token = $providerAccount->token;

            // Some providers (Google) only sends back the refresh token when
            // the user sees the Google UI prompt. Retain the current one, if none is provided.
            if (!is_null($providerAccount->refreshToken)) {
                $externalAccount->refresh_token = $providerAccount->refreshToken;
            }
            $externalAccount->save();

            $updateUserInfo = [
                'email' => $providerUserEmail,
                'given_name' => $this->parseGivenName($provider, $providerAccount),
                'family_name' => $this->parseFamilyName($provider, $providerAccount),
                'profile_picture_path' => $providerAccount->getAvatar(),
            ];

            // Always update the fields from the external provider
            // when this action is called. For example, when the flag is `false`,
            // only the email will be updated when the user logs in.
            if (!$forceUpdate) {
                $updateUserInfo = Arr::only($updateUserInfo, ['email']);
            }

            $updateUserAction->execute($externalAccount->user, $updateUserInfo);

            return $externalAccount->user->load(['userProfile', 'roles', 'externalAccount']);
        });
    }

    private function parseGivenName(ExternalLoginProvider $provider, ProviderUser $providerAccount): string
    {
        if ($provider === ExternalLoginProvider::GOOGLE) {
            return $providerAccount->user['given_name'];
        }

        // Github only has a 'name' field for the entire name
        if ($provider === ExternalLoginProvider::GITHUB) {
            $name = explode(' ', $providerAccount->user['name']);
            if (count($name) === 2) {
                return $name[0];
            }

            if (count($name) > 2) {
                // Get all name parts except the last
                $firstNameParts = array_slice($name, 0, -1);
                return implode(' ', $firstNameParts);
            }

            // Return the entire name if it cannot be parsed properly
            return $providerAccount->user['name'];
        }

        throw new InvalidArgumentException('Invalid provider account.');
    }

    private function parseFamilyName(ExternalLoginProvider $provider, ProviderUser $providerAccount): string
    {
        if ($provider === ExternalLoginProvider::GOOGLE) {
            return $providerAccount->user['family_name'];
        }

        // Github only has a 'name' field for the entire name
        if ($provider === ExternalLoginProvider::GITHUB) {
            $name = explode(' ', $providerAccount->user['name']);
            if (count($name) === 2) {
                return $name[1];
            }

            if (count($name) > 2) {
                return $name[count($name) - 1]; // just get the last part
            }

            // Return the entire name if it cannot be parsed properly
            return $providerAccount->user['name'];
        }

        throw new InvalidArgumentException('Invalid provider account.');
    }
}
