<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        return [
            'ok' => false,
            'message' => 'This tool does not support instant text processing yet.',
            'label' => 'Unsupported Processor',
            'output' => null,
        ];
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
        if (!class_exists(\Imagick::class)) {
            return [
                'ok' => false,
                'message' => 'TIFF to PDF conversion requires the PHP Imagick extension on the server.',
            ];
        }

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
            $imagick = new \Imagick();
            $imagick->readImage($disk->path($inputPath));
            $imagick->setImageFormat('pdf');
            $imagick->writeImages($disk->path($outputPath), true);
            $imagick->clear();
            $imagick->destroy();

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
}
