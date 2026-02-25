<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ToolsController extends Controller
{
    public function index(): View
    {
        return $this->renderCatalog();
    }

    public function show(string $slug): View
    {
        return $this->renderCatalog($slug);
    }

    public function process(Request $request, string $slug): RedirectResponse|BinaryFileResponse
    {
        $catalog = $this->catalog();
        abort_unless(isset($catalog['tools'][$slug]), 404);

        $tool = $catalog['tools'][$slug];

        if ($tool['input_type'] === 'text') {
            $processor = $tool['processor'] ?? '';
            $textPayloadRule = $processor === 'uuid_v4'
                ? ['nullable', 'string', 'max:200000']
                : ['required', 'string', 'max:200000'];

            $validated = $request->validate([
                'text_payload' => $textPayloadRule,
            ]);

            $result = $this->runTextProcessor($tool, (string) ($validated['text_payload'] ?? ''));

            if ($result['ok']) {
                return redirect()
                    ->route('tools.show', ['slug' => $slug])
                    ->with('tool_status', $result['message'])
                    ->with('tool_result', $result['output'])
                    ->with('tool_result_label', $result['label']);
            }

            return redirect()
                ->route('tools.show', ['slug' => $slug])
                ->withInput()
                ->with('tool_error', $result['message']);
        }

        $rules = array_merge(['required'], $tool['validation_rules']);

        $validated = $request->validate([
            'upload_file' => $rules,
            'processing_note' => ['nullable', 'string', 'max:500'],
        ]);

        $uploadedFile = $validated['upload_file'];
        $result = $this->runFileProcessor($tool, $uploadedFile);

        if (!$result['ok']) {
            return redirect()
                ->route('tools.show', ['slug' => $slug])
                ->withInput()
                ->with('tool_error', $result['message']);
        }

        if (isset($result['download_relative_path'])) {
            $downloadPath = Storage::disk('local')->path($result['download_relative_path']);
            $downloadName = $result['download_name'] ?? 'converted-file.pdf';

            return response()->download($downloadPath, $downloadName, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        }

        return redirect()
            ->route('tools.show', ['slug' => $slug])
            ->with('tool_status', $result['message']);
    }

    public function formats(Request $request, string $slug): JsonResponse
    {
        $catalog = $this->catalog();
        abort_unless(isset($catalog['tools'][$slug]), 404);

        $tool = $catalog['tools'][$slug];
        if (($tool['processor'] ?? '') !== 'youtube_downloader') {
            return response()->json([
                'ok' => false,
                'message' => 'Formats endpoint is only available for downloader tools.',
            ], 422);
        }

        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $parsed = $this->parseYoutubeVideoUrl((string) $validated['url']);
        if ($parsed === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid YouTube URL. Paste a valid watch, short, embed, or youtu.be link.',
            ], 422);
        }

        $inspection = $this->inspectYoutubeMedia($parsed['canonical_url']);
        if (!$inspection['ok']) {
            return response()->json([
                'ok' => false,
                'message' => $inspection['message'],
            ], 422);
        }

        $videoOptions = array_map(function (int $height) use ($slug, $parsed): array {
            return [
                'label' => $height . 'p',
                'download_url' => URL::temporarySignedRoute('tools.download', now()->addMinutes(15), [
                    'slug' => $slug,
                    'url' => $parsed['canonical_url'],
                    'type' => 'video',
                    'quality' => $height,
                ]),
            ];
        }, $inspection['video_heights']);

        $audioBitrates = [320, 256, 192, 128];
        $audioOptions = array_map(function (int $bitrate) use ($slug, $parsed): array {
            return [
                'label' => 'MP3 ' . $bitrate . 'kbps',
                'download_url' => URL::temporarySignedRoute('tools.download', now()->addMinutes(15), [
                    'slug' => $slug,
                    'url' => $parsed['canonical_url'],
                    'type' => 'audio',
                    'quality' => $bitrate,
                ]),
            ];
        }, $audioBitrates);

        return response()->json([
            'ok' => true,
            'title' => $inspection['title'],
            'duration' => $inspection['duration'],
            'thumbnail' => $inspection['thumbnail'],
            'normalized_url' => $parsed['canonical_url'],
            'video_options' => $videoOptions,
            'audio_options' => $audioOptions,
        ]);
    }

    public function download(Request $request, string $slug): RedirectResponse|BinaryFileResponse
    {
        $catalog = $this->catalog();
        abort_unless(isset($catalog['tools'][$slug]), 404);

        $tool = $catalog['tools'][$slug];
        if (($tool['processor'] ?? '') !== 'youtube_downloader') {
            abort(404);
        }

        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
            'type' => ['required', 'in:video,audio'],
            'quality' => ['required', 'integer'],
        ]);

        $parsed = $this->parseYoutubeVideoUrl((string) $validated['url']);
        if ($parsed === null) {
            return redirect()
                ->route('tools.show', ['slug' => $slug])
                ->with('tool_error', 'Invalid YouTube URL for download.');
        }

        if (!function_exists('exec')) {
            return redirect()
                ->route('tools.show', ['slug' => $slug])
                ->with('tool_error', 'Server download execution is disabled (exec not available).');
        }

        $ytDlp = $this->detectYtDlpBinary();
        if ($ytDlp === null) {
            return redirect()
                ->route('tools.show', ['slug' => $slug])
                ->with('tool_error', 'yt-dlp is not installed on the server. Install yt-dlp to enable video downloads.');
        }

        $resultsDirectory = Storage::disk('local')->path('tool-results/youtube-video-downloader');
        if (!is_dir($resultsDirectory)) {
            @mkdir($resultsDirectory, 0775, true);
        }

        $jobToken = (string) Str::uuid();
        $outputTemplate = $resultsDirectory . DIRECTORY_SEPARATOR . $jobToken . '.%(ext)s';
        $urlArgument = escapeshellarg($parsed['canonical_url']);

        $type = (string) $validated['type'];
        $quality = (int) $validated['quality'];
        $command = '';
        $label = '';

        if ($type === 'video') {
            $allowedHeights = [4320, 2160, 1440, 1080, 720, 480, 360, 240, 144];
            if (!in_array($quality, $allowedHeights, true)) {
                return redirect()
                    ->route('tools.show', ['slug' => $slug])
                    ->with('tool_error', 'Unsupported video quality requested.');
            }

            $formatSelector = "bestvideo[height<={$quality}]+bestaudio/best[height<={$quality}]";
            $label = $quality . 'p';

            $command = escapeshellarg($ytDlp)
                . ' --no-playlist --no-warnings --restrict-filenames --force-overwrites --merge-output-format mp4'
                . ' -f ' . escapeshellarg($formatSelector)
                . ' -o ' . escapeshellarg($outputTemplate)
                . ' ' . $urlArgument
                . ' 2>&1';
        } else {
            $allowedBitrates = [320, 256, 192, 128];
            if (!in_array($quality, $allowedBitrates, true)) {
                return redirect()
                    ->route('tools.show', ['slug' => $slug])
                    ->with('tool_error', 'Unsupported MP3 quality requested.');
            }

            if ($this->detectFfmpegBinary() === null) {
                return redirect()
                    ->route('tools.show', ['slug' => $slug])
                    ->with('tool_error', 'ffmpeg is required for MP3 conversion. Install ffmpeg on the server and retry.');
            }

            $label = 'mp3-' . $quality . 'kbps';
            $postprocessorArgs = 'ffmpeg:-b:a ' . $quality . 'k';

            $command = escapeshellarg($ytDlp)
                . ' --no-playlist --no-warnings --restrict-filenames --force-overwrites'
                . ' -x --audio-format mp3 --audio-quality 0'
                . ' --postprocessor-args ' . escapeshellarg($postprocessorArgs)
                . ' -o ' . escapeshellarg($outputTemplate)
                . ' ' . $urlArgument
                . ' 2>&1';
        }

        $outputLines = [];
        $exitCode = 1;
        exec($command, $outputLines, $exitCode);

        $downloadPath = $this->resolveYoutubeDownloadPath($resultsDirectory, $jobToken);

        if ($exitCode !== 0 || $downloadPath === null || !is_file($downloadPath)) {
            return redirect()
                ->route('tools.show', ['slug' => $slug])
                ->with('tool_error', $this->formatYoutubeDownloadError(trim(implode("\n", $outputLines))));
        }

        $extension = pathinfo($downloadPath, PATHINFO_EXTENSION);
        $downloadName = 'youtube-' . $parsed['video_id'] . '-' . $label . '.' . $extension;

        return response()->download($downloadPath, $downloadName)->deleteFileAfterSend(true);
    }

    private function renderCatalog(?string $slug = null): View
    {
        $catalog = $this->catalog();
        $activeTool = $this->resolveActiveTool($catalog, $slug);
        $isToolDetailPage = $slug !== null;

        $metaTitle = $isToolDetailPage
            ? sprintf('%s | IT Tools | Arosoft Innovations Ltd', $activeTool['name'])
            : 'IT Tools | Arosoft Innovations Ltd';

        $metaDescription = $isToolDetailPage
            ? $activeTool['description']
            : 'Explore categorized Arosoft IT tools including password removers, converters, and generators with dedicated SEO-friendly URLs.';

        $canonicalUrl = $isToolDetailPage
            ? route('tools.show', ['slug' => $activeTool['slug']])
            : route('tools');

        $toolSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $activeTool['name'],
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'description' => $activeTool['description'],
            'url' => route('tools.show', ['slug' => $activeTool['slug']]),
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Arosoft Innovations Ltd',
                'url' => 'https://arosoft.io',
            ],
        ];

        $catalogSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Arosoft IT Tools Directory',
            'itemListElement' => array_values(array_map(
                fn (array $tool, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $tool['name'],
                    'url' => route('tools.show', ['slug' => $tool['slug']]),
                ],
                $catalog['ordered_tools'],
                array_keys($catalog['ordered_tools'])
            )),
        ];

        return view('pages.tools', [
            'categories' => $catalog['categories'],
            'activeTool' => $activeTool,
            'toolCount' => count($catalog['ordered_tools']),
            'isToolDetailPage' => $isToolDetailPage,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'toolSchema' => $toolSchema,
            'catalogSchema' => $catalogSchema,
        ]);
    }

    private function resolveActiveTool(array $catalog, ?string $slug): array
    {
        if ($slug !== null) {
            abort_unless(isset($catalog['tools'][$slug]), 404);
            return $catalog['tools'][$slug];
        }

        $defaultTool = $catalog['ordered_tools'][0] ?? null;
        abort_if($defaultTool === null, 404, 'No tools configured.');

        return $defaultTool;
    }

    private function catalog(): array
    {
        $configuredCategories = config('it_tools.categories', []);

        $categories = [];
        $toolsBySlug = [];
        $orderedTools = [];

        foreach ($configuredCategories as $categoryIndex => $category) {
            $categoryKey = (string) ($category['key'] ?? "category-{$categoryIndex}");
            $categoryName = (string) ($category['name'] ?? 'General Tools');
            $categorySummary = (string) ($category['summary'] ?? '');
            $mappedTools = [];

            foreach (($category['tools'] ?? []) as $toolIndex => $tool) {
                $slug = trim((string) ($tool['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }

                $mappedTool = [
                    'slug' => $slug,
                    'name' => (string) ($tool['name'] ?? Str::headline(str_replace('-', ' ', $slug))),
                    'status' => (string) ($tool['status'] ?? 'Live'),
                    'tagline' => (string) ($tool['tagline'] ?? ''),
                    'description' => (string) ($tool['description'] ?? ''),
                    'input_type' => (string) ($tool['input_type'] ?? 'file'),
                    'accept' => (string) ($tool['accept'] ?? ''),
                    'validation_rules' => (array) ($tool['validation_rules'] ?? ['file', 'max:51200']),
                    'button_label' => (string) ($tool['button_label'] ?? 'Process Tool'),
                    'processing_mode' => (string) ($tool['processing_mode'] ?? 'assisted'),
                    'processor' => (string) ($tool['processor'] ?? ''),
                    'use_cases' => (array) ($tool['use_cases'] ?? []),
                    'category_key' => $categoryKey,
                    'category_name' => $categoryName,
                    'position' => $toolIndex + 1,
                ];

                $mappedTools[] = $mappedTool;
                $toolsBySlug[$slug] = $mappedTool;
                $orderedTools[] = $mappedTool;
            }

            $categories[] = [
                'key' => $categoryKey,
                'name' => $categoryName,
                'summary' => $categorySummary,
                'tools' => $mappedTools,
            ];
        }

        return [
            'categories' => $categories,
            'tools' => $toolsBySlug,
            'ordered_tools' => $orderedTools,
        ];
    }

    private function runTextProcessor(array $tool, string $payload): array
    {
        $processor = $tool['processor'] ?? '';
        $trimmedPayload = trim($payload);

        if ($processor === 'json_formatter') {
            $decoded = json_decode($trimmedPayload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'ok' => false,
                    'message' => 'Invalid JSON input. Fix the JSON and try again.',
                    'label' => 'JSON Validation Error',
                    'output' => null,
                ];
            }

            return [
                'ok' => true,
                'message' => 'JSON formatted successfully.',
                'label' => 'Formatted JSON',
                'output' => json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        if ($processor === 'base64_encode') {
            return [
                'ok' => true,
                'message' => 'Text encoded to Base64 successfully.',
                'label' => 'Base64 Output',
                'output' => base64_encode($payload),
            ];
        }

        if ($processor === 'base64_decode') {
            $decoded = base64_decode($trimmedPayload, true);

            if ($decoded === false) {
                return [
                    'ok' => false,
                    'message' => 'Invalid Base64 input. Provide a valid Base64 string.',
                    'label' => 'Decode Error',
                    'output' => null,
                ];
            }

            return [
                'ok' => true,
                'message' => 'Base64 decoded successfully.',
                'label' => 'Decoded Output',
                'output' => $decoded,
            ];
        }

        if ($processor === 'hash_sha256') {
            return [
                'ok' => true,
                'message' => 'SHA-256 hash generated successfully.',
                'label' => 'SHA-256 Hash',
                'output' => hash('sha256', $payload),
            ];
        }

        if ($processor === 'uuid_v4') {
            return [
                'ok' => true,
                'message' => 'UUID generated successfully.',
                'label' => 'UUID v4',
                'output' => (string) Str::uuid(),
            ];
        }

        if ($processor === 'qr_link') {
            if ($trimmedPayload === '') {
                return [
                    'ok' => false,
                    'message' => 'Provide text or URL to generate a QR link.',
                    'label' => 'QR Error',
                    'output' => null,
                ];
            }

            $qrUrl = 'https://quickchart.io/qr?text=' . rawurlencode($trimmedPayload);

            return [
                'ok' => true,
                'message' => 'QR link generated successfully.',
                'label' => 'QR Code URL',
                'output' => $qrUrl,
            ];
        }

        if ($processor === 'youtube_downloader') {
            if ($trimmedPayload === '') {
                return [
                    'ok' => false,
                    'message' => 'Provide a YouTube video URL to continue.',
                    'label' => 'YouTube URL Error',
                    'output' => null,
                ];
            }

            $parsed = $this->parseYoutubeVideoUrl($trimmedPayload);
            if ($parsed === null) {
                return [
                    'ok' => false,
                    'message' => 'Invalid YouTube URL. Paste a valid watch, short, embed, or youtu.be link.',
                    'label' => 'YouTube URL Error',
                    'output' => null,
                ];
            }

            $normalizedUrl = 'https://www.youtube.com/watch?v=' . $parsed['video_id'];

            $output = implode("\n", [
                'Video ID: ' . $parsed['video_id'],
                'Normalized URL: ' . $normalizedUrl,
                'Request Status: Ready for download processing',
                'Pipeline: Auto format generation is ready (video qualities + MP3 options)',
            ]);

            return [
                'ok' => true,
                'message' => 'YouTube video URL accepted and prepared successfully.',
                'label' => 'YouTube Download Request',
                'output' => $output,
            ];
        }

        return [
            'ok' => false,
            'message' => 'This tool does not support instant text processing yet.',
            'label' => 'Unsupported Processor',
            'output' => null,
        ];
    }

    private function parseYoutubeVideoUrl(string $input): ?array
    {
        $sanitized = trim($input);
        if ($sanitized === '') {
            return null;
        }

        if (!str_starts_with(Str::lower($sanitized), 'http://') && !str_starts_with(Str::lower($sanitized), 'https://')) {
            $sanitized = 'https://' . $sanitized;
        }

        $parts = parse_url($sanitized);
        if ($parts === false) {
            return null;
        }

        $host = Str::lower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $query = (string) ($parts['query'] ?? '');

        $isYoutubeHost = Str::contains($host, 'youtube.com')
            || Str::contains($host, 'youtu.be')
            || Str::contains($host, 'youtube-nocookie.com');

        if (!$isYoutubeHost) {
            return null;
        }

        $videoId = null;

        if (Str::contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif ($path === 'watch') {
            parse_str($query, $queryParams);
            $videoId = $queryParams['v'] ?? null;
        } elseif (Str::startsWith($path, 'shorts/')) {
            $videoId = explode('/', $path)[1] ?? null;
        } elseif (Str::startsWith($path, 'embed/')) {
            $videoId = explode('/', $path)[1] ?? null;
        } elseif (Str::startsWith($path, 'live/')) {
            $videoId = explode('/', $path)[1] ?? null;
        }

        if (!is_string($videoId) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return null;
        }

        return [
            'video_id' => $videoId,
            'url' => $sanitized,
            'canonical_url' => 'https://www.youtube.com/watch?v=' . $videoId,
        ];
    }

    private function inspectYoutubeMedia(string $url): array
    {
        if (!function_exists('exec')) {
            return [
                'ok' => false,
                'message' => 'Server execution is disabled. Enable exec to inspect YouTube formats.',
            ];
        }

        $ytDlp = $this->detectYtDlpBinary();
        if ($ytDlp === null) {
            return [
                'ok' => false,
                'message' => 'yt-dlp is not installed on the server. Install yt-dlp to load quality options.',
            ];
        }

        $command = escapeshellarg($ytDlp)
            . ' --dump-single-json --no-playlist --no-warnings '
            . escapeshellarg($url)
            . ' 2>&1';

        $outputLines = [];
        $exitCode = 1;
        exec($command, $outputLines, $exitCode);

        $rawOutput = trim(implode("\n", $outputLines));
        $jsonPayload = $this->extractJsonFromOutput($rawOutput);

        if ($exitCode !== 0 || $jsonPayload === null) {
            return [
                'ok' => false,
                'message' => $this->formatYoutubeDownloadError($rawOutput),
            ];
        }

        $decoded = json_decode($jsonPayload, true);
        if (!is_array($decoded)) {
            return [
                'ok' => false,
                'message' => 'Unable to parse YouTube format data from server response.',
            ];
        }

        $heights = [];
        foreach (($decoded['formats'] ?? []) as $format) {
            if (!is_array($format)) {
                continue;
            }

            $vcodec = (string) ($format['vcodec'] ?? 'none');
            $height = (int) ($format['height'] ?? 0);

            if ($vcodec !== 'none' && $height > 0) {
                $heights[$height] = true;
            }
        }

        $videoHeights = array_map('intval', array_keys($heights));
        rsort($videoHeights);

        if (count($videoHeights) === 0) {
            $videoHeights = [1080, 720, 480, 360];
        }

        return [
            'ok' => true,
            'title' => (string) ($decoded['title'] ?? 'YouTube Video'),
            'duration' => (int) ($decoded['duration'] ?? 0),
            'thumbnail' => (string) ($decoded['thumbnail'] ?? ''),
            'video_heights' => $videoHeights,
        ];
    }

    private function detectYtDlpBinary(): ?string
    {
        $candidates = [
            '/usr/local/bin/yt-dlp',
            '/usr/bin/yt-dlp',
            '/bin/yt-dlp',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        $commandOutput = [];
        $exitCode = 1;
        exec('command -v yt-dlp 2>/dev/null', $commandOutput, $exitCode);
        if ($exitCode === 0) {
            $detected = trim($commandOutput[0] ?? '');
            if ($detected !== '' && is_file($detected) && is_executable($detected)) {
                return $detected;
            }
        }

        return null;
    }

    private function detectFfmpegBinary(): ?string
    {
        $candidates = [
            '/usr/local/bin/ffmpeg',
            '/usr/bin/ffmpeg',
            '/bin/ffmpeg',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        $commandOutput = [];
        $exitCode = 1;
        exec('command -v ffmpeg 2>/dev/null', $commandOutput, $exitCode);
        if ($exitCode === 0) {
            $detected = trim($commandOutput[0] ?? '');
            if ($detected !== '' && is_file($detected) && is_executable($detected)) {
                return $detected;
            }
        }

        return null;
    }

    private function resolveYoutubeDownloadPath(string $resultsDirectory, string $jobToken): ?string
    {
        $matches = glob($resultsDirectory . DIRECTORY_SEPARATOR . $jobToken . '.*') ?: [];
        if (count($matches) === 0) {
            return null;
        }

        $validMatches = array_values(array_filter($matches, static function (string $path): bool {
            $basename = basename($path);

            return !Str::endsWith($basename, ['.part', '.ytdl', '.temp'])
                && is_file($path)
                && filesize($path) > 0;
        }));

        if (count($validMatches) === 0) {
            return null;
        }

        usort($validMatches, static function (string $left, string $right): int {
            return filemtime($right) <=> filemtime($left);
        });

        return $validMatches[0];
    }

    private function extractJsonFromOutput(string $rawOutput): ?string
    {
        $start = strpos($rawOutput, '{');
        $end = strrpos($rawOutput, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return substr($rawOutput, $start, $end - $start + 1);
    }

    private function formatYoutubeDownloadError(string $rawOutput): string
    {
        $normalized = Str::lower($rawOutput);

        if (Str::contains($normalized, 'ffmpeg')) {
            return 'ffmpeg is required for merge/audio conversion. Install ffmpeg on the server and retry.';
        }

        if (Str::contains($normalized, 'private video') || Str::contains($normalized, 'sign in to confirm')) {
            return 'This YouTube video cannot be downloaded publicly (private or restricted).';
        }

        if (Str::contains($normalized, 'copyright') || Str::contains($normalized, 'unavailable')) {
            return 'This media is unavailable for download. Use content you own or have permission to process.';
        }

        return 'Failed to process YouTube download. Verify server tools (yt-dlp/ffmpeg) and try again.';
    }

    private function runFileProcessor(array $tool, UploadedFile $uploadedFile): array
    {
        $processor = $tool['processor'] ?? '';

        if ($processor === 'tiff_to_pdf') {
            return $this->convertTiffToPdf($uploadedFile);
        }

        $storedPath = $uploadedFile->store("tool-uploads/{$tool['slug']}", 'local');
        if ($storedPath === false) {
            return [
                'ok' => false,
                'message' => 'Upload failed. Please try again.',
            ];
        }

        $modeLabel = $tool['processing_mode'] === 'instant'
            ? 'instant processing'
            : 'assisted processing';

        return [
            'ok' => true,
            'message' => sprintf(
                'Received "%s" for %s (%s). Reference: %s',
                $uploadedFile->getClientOriginalName(),
                $tool['name'],
                $modeLabel,
                $storedPath
            ),
        ];
    }

    private function convertTiffToPdf(UploadedFile $uploadedFile): array
    {
        $disk = Storage::disk('local');
        $inputPath = $uploadedFile->store('tool-uploads/tiff-to-pdf-converter', 'local');

        if ($inputPath === false) {
            return [
                'ok' => false,
                'message' => 'Unable to store uploaded TIFF file.',
            ];
        }

        $resultDirectory = 'tool-results/tiff-to-pdf-converter';
        $disk->makeDirectory($resultDirectory);

        $outputPath = $resultDirectory . '/' . Str::uuid() . '.pdf';
        $downloadName = Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.pdf';
        $downloadName = $downloadName !== '.pdf' ? $downloadName : 'converted-file.pdf';

        try {
            $inputAbsolutePath = $disk->path($inputPath);
            $outputAbsolutePath = $disk->path($outputPath);

            if (class_exists(\Imagick::class)) {
                $imagick = new \Imagick();
                $imagick->readImage($inputAbsolutePath);
                $imagick->setImageFormat('pdf');
                $imagick->writeImages($outputAbsolutePath, true);
                $imagick->clear();
                $imagick->destroy();
            } else {
                $cliConversion = $this->convertTiffToPdfViaCli($inputAbsolutePath, $outputAbsolutePath);
                if (!$cliConversion['ok']) {
                    $disk->delete([$inputPath, $outputPath]);

                    return [
                        'ok' => false,
                        'message' => $cliConversion['message'],
                    ];
                }
            }

            $disk->delete($inputPath);

            return [
                'ok' => true,
                'message' => 'TIFF converted to PDF successfully.',
                'download_relative_path' => $outputPath,
                'download_name' => $downloadName,
            ];
        } catch (Throwable $error) {
            $disk->delete([$inputPath, $outputPath]);

            return [
                'ok' => false,
                'message' => $this->formatTiffConversionError($error),
            ];
        }
    }

    private function formatTiffConversionError(Throwable $error): string
    {
        $normalized = Str::lower($error->getMessage());

        if (Str::contains($normalized, 'not authorized')) {
            return 'TIFF to PDF conversion is blocked by ImageMagick policy. Enable PDF write in policy.xml, then retry.';
        }

        return 'TIFF to PDF conversion failed. Please try another TIFF file.';
    }

    private function convertTiffToPdfViaCli(string $inputAbsolutePath, string $outputAbsolutePath): array
    {
        if (!function_exists('exec')) {
            return [
                'ok' => false,
                'message' => 'TIFF to PDF requires PHP Imagick or ImageMagick CLI with exec enabled on this server.',
            ];
        }

        $binary = $this->detectImageMagickBinary();
        if ($binary === null) {
            return [
                'ok' => false,
                'message' => 'ImageMagick CLI binary was not found. Install ImageMagick or enable PHP Imagick extension.',
            ];
        }

        $command = escapeshellarg($binary)
            . ' '
            . escapeshellarg($inputAbsolutePath)
            . ' -compress Zip '
            . escapeshellarg($outputAbsolutePath)
            . ' 2>&1';

        $outputLines = [];
        $exitCode = 1;
        exec($command, $outputLines, $exitCode);

        $output = trim(implode("\n", $outputLines));

        if ($exitCode !== 0 || !is_file($outputAbsolutePath) || filesize($outputAbsolutePath) === 0) {
            return [
                'ok' => false,
                'message' => $this->formatTiffCliError($output),
            ];
        }

        return [
            'ok' => true,
            'message' => 'TIFF converted to PDF successfully via ImageMagick CLI.',
        ];
    }

    private function detectImageMagickBinary(): ?string
    {
        $candidates = [
            '/usr/bin/magick',
            '/usr/local/bin/magick',
            '/bin/magick',
            '/usr/bin/convert',
            '/usr/local/bin/convert',
            '/bin/convert',
            '/usr/bin/convert-im6.q16',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function formatTiffCliError(string $rawOutput): string
    {
        $normalized = Str::lower($rawOutput);

        if (Str::contains($normalized, 'not authorized')) {
            return 'TIFF to PDF conversion is blocked by ImageMagick policy. Enable PDF write in policy.xml, then retry.';
        }

        if (Str::contains($normalized, 'no decode delegate')) {
            return 'Server ImageMagick cannot read this TIFF format. Try a different TIFF file or enable the required delegate.';
        }

        return 'TIFF to PDF conversion failed on this server. Enable PHP Imagick or verify ImageMagick CLI permissions.';
    }
}
