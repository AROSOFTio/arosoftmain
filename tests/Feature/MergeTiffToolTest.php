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
        $response->assertSee('multiple', false);
        $response->assertSee('CCITT4 Group 4', false);
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

    public function test_merge_tiff_tool_returns_a_tiff_download(): void
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

        $response->assertOk();
        $response->assertDownload('scan-1-merged.tif');
        $response->assertHeader('content-type', 'image/tiff');
    }
}
