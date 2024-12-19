<?php

namespace App\Actions;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * This action class handles the creation or update of a user's resume.
 *
 * Example:
 * <code>
 * $resumeData = [
 *     'name' => 'Mark William Calaway',
 *     'titles' => ['Software Engineer', 'Software Architect'],
 *     'summary' => ['+5 years of back-end development', '...'],
 *     'socials' => [['name': 'Facebook', 'url': 'https://facebook.com/some-profile']]
 * ];
 *
 * $updatedResume = $createOrUpdateResumeAction->execute($user, $resumeData);
 * </code>
 */
readonly class CreateOrUpdateResumeAction
{
    /**
     * @throws InvalidArgumentException
     */
    public function execute(User $user, array $data): Resume
    {
        $whitelistedProperties = $this->getWhitelistedProperties();
        $nonWhitelistedKeys = Arr::except($data, $whitelistedProperties);
        if (!empty($nonWhitelistedKeys)) {
            $invalidKeys = implode(', ', array_keys($nonWhitelistedKeys));
            $validKeys = implode(', ', $whitelistedProperties);
            throw new InvalidArgumentException("The keys `$invalidKeys` are not allowed. The whitelisted properties are `$validKeys`.");
        }

        $user->resume()->updateOrCreate(['user_id' => $user->id], $data);
        $user->refresh();
        return $user->resume;
    }

    private function getWhitelistedProperties(): array
    {
        return (new Resume())->getFillable();
    }
}
