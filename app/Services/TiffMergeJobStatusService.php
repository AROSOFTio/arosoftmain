<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TiffMergeJobStatusService
{
    private const CACHE_PREFIX = 'tools:merge-tiff-job:';
    private const TTL_MINUTES = 1440;

    public function create(string $jobToken, array $attributes = []): array
    {
        $state = array_merge([
            'slug' => 'merge-tiff-tif-files',
            'status' => 'queued',
            'progress' => 12,
            'message' => 'Upload received. Preparing TIFF/TIF merge in the background...',
            'download_relative_path' => null,
            'download_name' => null,
            'download_content_type' => null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], $attributes);

        $this->store($jobToken, $state);

        return $state;
    }

    public function find(string $jobToken): ?array
    {
        $state = Cache::get($this->cacheKey($jobToken));

        return is_array($state) ? $state : null;
    }

    public function markProcessing(string $jobToken, string $message = 'Merging TIFF/TIF pages in the background...', int $progress = 48): ?array
    {
        return $this->update($jobToken, [
            'status' => 'processing',
            'progress' => max(20, min(92, $progress)),
            'message' => $message,
        ]);
    }

    public function markCompleted(string $jobToken, array $attributes): ?array
    {
        return $this->update($jobToken, array_merge($attributes, [
            'status' => 'completed',
            'progress' => 100,
        ]));
    }

    public function markFailed(string $jobToken, string $message): ?array
    {
        return $this->update($jobToken, [
            'status' => 'failed',
            'progress' => 100,
            'message' => $message,
        ]);
    }

    private function update(string $jobToken, array $attributes): ?array
    {
        $currentState = $this->find($jobToken);
        if ($currentState === null) {
            return null;
        }

        $nextState = array_merge($currentState, $attributes, [
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->store($jobToken, $nextState);

        return $nextState;
    }

    private function store(string $jobToken, array $state): void
    {
        Cache::put($this->cacheKey($jobToken), $state, now()->addMinutes(self::TTL_MINUTES));
    }

    private function cacheKey(string $jobToken): string
    {
        return self::CACHE_PREFIX . $jobToken;
    }
}
