<?php

namespace Tests\Feature;

use App\Services\TiffMergeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class MergeTiffToolTest extends TestCase
{
    public function test_merge_tiff_tool_page_allows_multiple_uploads(): void
    {
        $response = $this->get(route('tools.show', ['slug' => 'merge-tiff-tif-files']));

        $response->assertOk();
        $response->assertSee('name="upload_file[]"', false);
        $response->assertSee('TIFF/TIF file 1', false);
        $response->assertSee('Add TIFF/TIF file', false);
        $response->assertSee('Processing progress', false);
        $response->assertSee('CCITT4 Group 4', false);
        $response->assertSee('Up to 60 files per batch, 20 MB each.', false);
    }

    public function test_tools_index_can_open_merge_tiff_tool_via_query_parameter(): void
    {
        $response = $this->get(route('tools', ['tool' => 'merge-tiff-tif-files']));

        $response->assertOk();
        $response->assertSee('Mergers', false);
        $response->assertSee('Merge TIFF/TIF Files', false);
        $response->assertSee('name="upload_file[]"', false);
    }

    public function test_tools_index_links_to_merge_tiff_dedicated_url(): void
    {
        $response = $this->get(route('tools'));

        $response->assertOk();
        $response->assertSee(route('tools.show', ['slug' => 'merge-tiff-tif-files']), false);
    }

    public function test_merge_tiff_tool_requires_multiple_files(): void
    {
        Storage::fake('local');

        $response = $this->from(route('tools.show', ['slug' => 'merge-tiff-tif-files']))
            ->post(route('tools.process', ['slug' => 'merge-tiff-tif-files']), [
                'upload_file' => [
                    UploadedFile::fake()->create('scan-1.tif', 10, 'image/tiff'),
                ],
            ]);

        $response->assertRedirect(route('tools.show', ['slug' => 'merge-tiff-tif-files']));
        $response->assertSessionHasErrors('upload_file');
    }

    public function test_merge_tiff_tool_redirects_with_a_signed_download_button(): void
    {
        Storage::fake('local');

        $this->mock(TiffMergeService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('merge')
                ->once()
                ->withArgs(function (array $inputAbsolutePaths, string $outputAbsolutePath): bool {
                    $this->assertCount(2, $inputAbsolutePaths);
                    $this->assertStringEndsWith('.tif', $outputAbsolutePath);

                    file_put_contents($outputAbsolutePath, 'merged-tiff-binary');

                    return true;
                })
                ->andReturn([
                    'ok' => true,
                    'message' => 'TIFF/TIF files merged successfully.',
                ]);
        });

        $response = $this->post(route('tools.process', ['slug' => 'merge-tiff-tif-files']), [
            'upload_file' => [
                UploadedFile::fake()->create('scan-1.tif', 10, 'image/tiff'),
                UploadedFile::fake()->create('scan-2.tiff', 10, 'image/tiff'),
            ],
        ]);

        $response->assertRedirect(route('tools.show', ['slug' => 'merge-tiff-tif-files']));
        $response->assertSessionHas('tool_download_url');
        $response->assertSessionHas('tool_download_label', 'Download merged file');

        $downloadResponse = $this->get((string) session('tool_download_url'));

        $downloadResponse->assertOk();
        $downloadResponse->assertDownload('scan-1-merged.tif');
        $downloadResponse->assertHeader('content-type', 'image/tiff');
    }

    public function test_merge_tiff_tool_accepts_large_batches(): void
    {
        Storage::fake('local');

        $uploads = $this->makeFakeTiffUploads(60);

        $this->mock(TiffMergeService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('merge')
                ->once()
                ->withArgs(function (array $inputAbsolutePaths, string $outputAbsolutePath): bool {
                    $this->assertCount(60, $inputAbsolutePaths);
                    $this->assertStringEndsWith('.tif', $outputAbsolutePath);

                    file_put_contents($outputAbsolutePath, 'merged-large-batch-binary');

                    return true;
                })
                ->andReturn([
                    'ok' => true,
                    'message' => 'TIFF/TIF files merged successfully.',
                ]);
        });

        $response = $this->post(route('tools.process', ['slug' => 'merge-tiff-tif-files']), [
            'upload_file' => $uploads,
        ]);

        $response->assertRedirect(route('tools.show', ['slug' => 'merge-tiff-tif-files']));
        $response->assertSessionHas('tool_download_url');

        $downloadResponse = $this->get((string) session('tool_download_url'));

        $downloadResponse->assertOk();
        $downloadResponse->assertDownload('scan-001-merged.tif');
        $downloadResponse->assertHeader('content-type', 'image/tiff');
    }

    public function test_merge_tiff_tool_rejects_batches_above_the_limit(): void
    {
        Storage::fake('local');

        $response = $this->from(route('tools.show', ['slug' => 'merge-tiff-tif-files']))
            ->post(route('tools.process', ['slug' => 'merge-tiff-tif-files']), [
                'upload_file' => $this->makeFakeTiffUploads(61),
            ]);

        $response->assertRedirect(route('tools.show', ['slug' => 'merge-tiff-tif-files']));
        $response->assertSessionHasErrors('upload_file');
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function makeFakeTiffUploads(int $count): array
    {
        $uploads = [];

        for ($index = 1; $index <= $count; $index++) {
            $uploads[] = UploadedFile::fake()->create(sprintf('scan-%03d.tif', $index), 10, 'image/tiff');
        }

        return $uploads;
    }
}
