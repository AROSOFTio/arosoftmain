<?php

namespace Tests\Feature;

use App\Jobs\ProcessTiffMergeJob;
use App\Services\TiffMergeJobStatusService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function test_merge_tiff_tool_dispatches_a_background_merge_job(): void
    {
        Storage::fake('local');
        Bus::fake();

        $response = $this->post(route('tools.process', ['slug' => 'merge-tiff-tif-files']), [
            'upload_file' => [
                UploadedFile::fake()->create('scan-1.tif', 10, 'image/tiff'),
                UploadedFile::fake()->create('scan-2.tiff', 10, 'image/tiff'),
            ],
        ]);

        $redirectLocation = (string) $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertStringContainsString(route('tools.show', ['slug' => 'merge-tiff-tif-files'], false), $redirectLocation);
        $this->assertStringContainsString('job=', $redirectLocation);

        parse_str((string) parse_url($redirectLocation, PHP_URL_QUERY), $query);
        $jobToken = (string) ($query['job'] ?? '');

        $this->assertNotSame('', $jobToken);
        $response->assertSessionHas('tool_status', 'TIFF/TIF merge started. Keep this page open while the merged file is prepared.');

        Bus::assertDispatched(ProcessTiffMergeJob::class, function (ProcessTiffMergeJob $job) use ($jobToken): bool {
            return $job->jobToken === $jobToken
                && count($job->inputPaths) === 2
                && Str::endsWith($job->outputPath, '.tif')
                && $job->downloadName === 'scan-1-merged.tif';
        });
    }

    public function test_merge_tiff_tool_accepts_large_batches(): void
    {
        Storage::fake('local');
        Bus::fake();

        $response = $this->post(route('tools.process', ['slug' => 'merge-tiff-tif-files']), [
            'upload_file' => $this->makeFakeTiffUploads(60),
        ]);

        $response->assertRedirect();

        Bus::assertDispatched(ProcessTiffMergeJob::class, function (ProcessTiffMergeJob $job): bool {
            return count($job->inputPaths) === 60
                && $job->downloadName === 'scan-001-merged.tif';
        });
    }

    public function test_merge_tiff_job_status_endpoint_returns_download_details_for_completed_jobs(): void
    {
        Storage::fake('local');

        $jobToken = (string) Str::uuid();
        app(TiffMergeJobStatusService::class)->create($jobToken);
        app(TiffMergeJobStatusService::class)->markCompleted($jobToken, [
            'message' => 'TIFF/TIF files merged successfully.',
            'download_relative_path' => 'tool-results/merge-tiff-tif-files/example-output.tif',
            'download_name' => 'scan-1-merged.tif',
            'download_content_type' => 'image/tiff',
        ]);

        $response = $this->get(route('tools.job-status', [
            'slug' => 'merge-tiff-tif-files',
            'jobToken' => $jobToken,
        ]));

        $response->assertOk();
        $response->assertJsonPath('status', 'completed');
        $response->assertJsonPath('download_name', 'scan-1-merged.tif');
        $response->assertJsonPath('download_label', 'Download merged file');
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
