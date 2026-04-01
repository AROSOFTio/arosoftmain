<?php

namespace App\Jobs;

use App\Services\TiffMergeJobStatusService;
use App\Services\TiffMergeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessTiffMergeJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /**
     * @param array<int, string> $inputPaths
     */
    public function __construct(
        public string $jobToken,
        public array $inputPaths,
        public string $outputPath,
        public string $downloadName,
    ) {
    }

    public function handle(TiffMergeService $tiffMergeService, TiffMergeJobStatusService $jobStatusService): void
    {
        $disk = Storage::disk('local');
        $jobStatusService->markProcessing($this->jobToken);

        try {
            $inputAbsolutePaths = array_map(
                fn (string $path): string => $disk->path($path),
                $this->inputPaths
            );

            $mergeResult = $tiffMergeService->merge($inputAbsolutePaths, $disk->path($this->outputPath));
            $disk->delete($this->inputPaths);

            if (!$mergeResult['ok']) {
                $disk->delete($this->outputPath);
                $jobStatusService->markFailed($this->jobToken, $mergeResult['message']);

                return;
            }

            $jobStatusService->markCompleted($this->jobToken, [
                'message' => $mergeResult['message'],
                'download_relative_path' => $this->outputPath,
                'download_name' => $this->downloadName,
                'download_content_type' => 'image/tiff',
            ]);
        } catch (Throwable $error) {
            report($error);
            $disk->delete(array_merge($this->inputPaths, [$this->outputPath]));
            $jobStatusService->markFailed($this->jobToken, 'TIFF/TIF merge failed on the server. Please retry with a smaller batch if the problem continues.');
        }
    }
}
