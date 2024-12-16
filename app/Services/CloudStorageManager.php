<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface CloudStorageManager
{
    /**
     * Upload a file
     */
    public function upload(string $path, UploadedFile|string $file, ?string $fileName = null): string;

    /**
     * Delete a file
     */
    public function delete(string $path): bool;

    /**
     * Generate a URL available by X seconds
     */
    public function generateTmpUrl(string $path, int $timeLimitInSeconds): string;
}
