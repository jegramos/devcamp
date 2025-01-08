<?php

namespace App\Actions;

use App\Models\Resume;
use App\Models\User;
use App\Services\CloudStorageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

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
    private CloudStorageManager $cloudStorageManager;

    public function __construct(CloudStorageManager $cloudStorageManager)
    {
        $this->cloudStorageManager = $cloudStorageManager;
    }

    /**
     * @throws InvalidArgumentException|UploadException
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

        $data = $this->uploadProjectCoverPhotos($user, $data);
        $data = $this->uploadWorkTimelineDownloadable($user, $data);

        $user->resume()->updateOrCreate(['user_id' => $user->id], $data);
        $user->refresh();

        return $user->resume;
    }

    private function getWhitelistedProperties(): array
    {
        return (new Resume())->getFillable();
    }

    /**
     * Upload the project cover photos
     */
    private function uploadProjectCoverPhotos(User $user, array $data): array
    {
        if (!isset($data['projects'])) {
            return $data;
        }

        foreach ($data['projects'] as $index => $project) {
            $file = $project['cover'];

            if (!($file instanceof UploadedFile)) {
                continue;
            }

            $path = "images/$user->id/projects/cover/";
            $fullPath = $this->cloudStorageManager->upload($path, $file);
            $data['projects'][$index]['cover'] = $fullPath;
        }

        return $data;
    }

    private function uploadWorkTimelineDownloadable(User $user, array $data): array
    {
        if (!isset($data['work_timeline']['downloadable'])) {
            return $data;
        }

        $path = "file/$user->id/work-timeline/downloadable/";
        $file = $data['work_timeline']['downloadable'];

        if (!($file instanceof UploadedFile)) {
            return $data;
        }

        $fullPath = $this->cloudStorageManager->upload($path, $file);
        $data['work_timeline']['downloadable'] = $fullPath;

        return $data;
    }
}
