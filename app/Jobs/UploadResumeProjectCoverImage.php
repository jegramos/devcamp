<?php

namespace App\Jobs;

use App\Actions\CreateOrUpdateResumeAction;
use App\Services\CloudStorageManager;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadResumeProjectCoverImage implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public int $timeout = 60 * 3;

    private int $userId;
    private string|UploadedFile $image;
    private CloudStorageManager $cloudStorageManager;
    private CreateOrUpdateResumeAction $createOrUpdateResumeAction;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string|UploadedFile $image)
    {
        $this->userId = $userId;
        $this->image = $image;
        $this->cloudStorageManager = resolve(CloudStorageManager::class);
        $this->createOrUpdateResumeAction = resolve(CreateOrUpdateResumeAction::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            Log::info('Batch Job cancelled', [
                'batchId' => $this->batch()->id,
                'class' => __CLASS__,
            ]);

            return;
        }

        $path = "resumes/$this->userId/project-cover-images";

        try {
            $fullPath = $this->cloudStorageManager->upload($path, $this->image);
        } catch (UploadException $e) {
            Log::error('Error uploading resume project cover image: ' . $e->getMessage(), [
                'userId' => $this->userId,
                'class' => __CLASS__,
                'stackTrace' => $e->getTraceAsString(),
            ]);
        }
    }
}
