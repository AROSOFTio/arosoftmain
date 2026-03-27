<?php

namespace Tests\Unit;

use App\Services\TiffMergeService;
use Illuminate\Support\Str;
use Tests\TestCase;

class TiffMergeServiceTest extends TestCase
{
    public function test_merge_reduces_large_batches_in_chunks(): void
    {
        $workspace = storage_path('framework/testing/tiff-merge-' . Str::uuid());
        $this->makeDirectory($workspace);

        $inputPaths = [];
        for ($index = 1; $index <= 105; $index++) {
            $path = $workspace . DIRECTORY_SEPARATOR . sprintf('input-%03d.tif', $index);
            file_put_contents($path, 'input-' . $index);
            $inputPaths[] = $path;
        }

        $outputPath = $workspace . DIRECTORY_SEPARATOR . 'merged-output.tif';

        $service = new class extends TiffMergeService
        {
            /** @var array<int, array<int, string>> */
            public array $mergedChunks = [];

            protected function resolveBackend(): ?array
            {
                return ['type' => 'imagick'];
            }

            protected function mergeChunkSize(array $backend): int
            {
                return 15;
            }

            protected function normalizeInput(string $inputAbsolutePath, string $outputAbsolutePath, array $backend): void
            {
                file_put_contents($outputAbsolutePath, basename($inputAbsolutePath));
            }

            protected function mergeNormalizedChunk(array $chunkPaths, string $outputAbsolutePath, array $backend): void
            {
                $this->mergedChunks[] = array_map('basename', $chunkPaths);

                $combinedOutput = implode("\n", array_map(
                    static fn (string $path): string => (string) file_get_contents($path),
                    $chunkPaths
                ));

                file_put_contents($outputAbsolutePath, $combinedOutput);
            }
        };

        try {
            $result = $service->merge($inputPaths, $outputPath);

            $this->assertTrue($result['ok']);
            $this->assertFileExists($outputPath);
            $this->assertGreaterThan(1, count($service->mergedChunks));
            $this->assertLessThanOrEqual(15, max(array_map('count', $service->mergedChunks)));
        } finally {
            $this->removeDirectory($workspace);
        }
    }

    public function test_detect_image_magick_binary_rejects_non_imagemagick_commands(): void
    {
        $service = new class extends TiffMergeService
        {
            public function exposedDetectImageMagickBinary(): ?string
            {
                return $this->detectImageMagickBinary();
            }

            protected function configuredImageMagickCandidates(): array
            {
                return ['convert', 'magick'];
            }

            protected function defaultImageMagickCandidates(): array
            {
                return [];
            }

            protected function runCommand(array $commandParts): array
            {
                return $commandParts[0] === 'convert'
                    ? [
                        'exit_code' => 0,
                        'output_lines' => ['Convert FAT volumes to NTFS.'],
                    ]
                    : [
                        'exit_code' => 0,
                        'output_lines' => ['Version: ImageMagick 7.1.1-0'],
                    ];
            }
        };

        $this->assertSame('magick', $service->exposedDetectImageMagickBinary());
    }

    private function makeDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        mkdir($directory, 0775, true);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $entries = scandir($directory);
        if (!is_array($entries)) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $this->removeDirectory($path);

                continue;
            }

            @unlink($path);
        }

        @rmdir($directory);
    }
}
