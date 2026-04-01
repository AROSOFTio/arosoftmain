<?php

namespace App\Services;

use Illuminate\Support\Str;
use Throwable;

class TiffMergeService
{
    private const CLI_CHUNK_SIZE = 20;
    private const IMAGICK_CHUNK_SIZE = 12;
    protected ?string $activeTemporaryPath = null;

    /**
     * @param array<int, string> $inputAbsolutePaths
     * @return array{ok: bool, message: string}
     */
    public function merge(array $inputAbsolutePaths, string $outputAbsolutePath): array
    {
        if ($inputAbsolutePaths === []) {
            return [
                'ok' => false,
                'message' => 'Select TIFF/TIF files to merge.',
            ];
        }

        $backend = null;

        try {
            $this->prepareExecutionEnvironment();
            $this->assertInputFilesAreReadable($inputAbsolutePaths);
            $this->ensureOutputDirectoryExists($outputAbsolutePath);

            $backend = $this->resolveBackend();
            if ($backend === null) {
                return [
                    'ok' => false,
                    'message' => 'TIFF/TIF merge requires ImageMagick on this server. Enable PHP Imagick for the site PHP version or install ImageMagick CLI and configure TOOLS_IMAGEMAGICK_BINARY.',
                ];
            }

            $temporaryDirectory = $this->createTemporaryWorkspace($this->resolveWorkspaceBaseDirectory($outputAbsolutePath));
            $this->activateTemporaryPath($temporaryDirectory);

            try {
                $normalizedPaths = $this->normalizeInputs($inputAbsolutePaths, $temporaryDirectory, $backend);
                $temporaryMergedOutput = $this->composeMergedOutput($normalizedPaths, $temporaryDirectory, $backend);
                $this->publishOutput($temporaryMergedOutput, $outputAbsolutePath);
            } finally {
                $this->deactivateTemporaryPath();
                $this->removeDirectory($temporaryDirectory);
            }
        } catch (Throwable $error) {
            report($error);
            logger()->error('TIFF merge failed.', [
                'backend' => is_array($backend) ? ($backend['type'] ?? 'unknown') : 'unresolved',
                'input_count' => count($inputAbsolutePaths),
                'output_path' => $outputAbsolutePath,
                'temporary_path' => $this->activeTemporaryPath,
                'message' => $error->getMessage(),
            ]);
            $this->removeFile($outputAbsolutePath);

            return [
                'ok' => false,
                'message' => $this->formatMergeError($error->getMessage()),
            ];
        }

        if (!$this->isUsableFile($outputAbsolutePath)) {
            $this->removeFile($outputAbsolutePath);

            return [
                'ok' => false,
                'message' => 'Merged TIFF/TIF output was not created successfully.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'TIFF/TIF files merged successfully.',
        ];
    }

    /**
     * @return array{type: 'cli', binary: string}|array{type: 'imagick'}|null
     */
    protected function resolveBackend(): ?array
    {
        if ($this->canRunShellCommands()) {
            $binary = $this->detectImageMagickBinary();
            if ($binary !== null) {
                return [
                    'type' => 'cli',
                    'binary' => $binary,
                ];
            }
        }

        if (class_exists(\Imagick::class)) {
            return [
                'type' => 'imagick',
            ];
        }

        return null;
    }

    /**
     * @param array<int, string> $inputAbsolutePaths
     * @param array{type: 'cli', binary: string}|array{type: 'imagick'} $backend
     * @return array<int, string>
     */
    protected function normalizeInputs(array $inputAbsolutePaths, string $temporaryDirectory, array $backend): array
    {
        $normalizedPaths = [];

        foreach ($inputAbsolutePaths as $index => $inputAbsolutePath) {
            $normalizedPath = $temporaryDirectory . DIRECTORY_SEPARATOR . sprintf('normalized-%04d.tif', $index + 1);
            $this->normalizeInput($inputAbsolutePath, $normalizedPath, $backend);
            $normalizedPaths[] = $normalizedPath;
        }

        return $normalizedPaths;
    }

    /**
     * @param array{type: 'cli', binary: string}|array{type: 'imagick'} $backend
     */
    protected function normalizeInput(string $inputAbsolutePath, string $outputAbsolutePath, array $backend): void
    {
        if ($backend['type'] === 'cli') {
            $this->normalizeInputViaCli($inputAbsolutePath, $outputAbsolutePath, $backend['binary']);

            return;
        }

        $this->normalizeInputViaImagick($inputAbsolutePath, $outputAbsolutePath);
    }

    /**
     * @param array<int, string> $normalizedPaths
     * @param array{type: 'cli', binary: string}|array{type: 'imagick'} $backend
     */
    protected function composeMergedOutput(array $normalizedPaths, string $temporaryDirectory, array $backend): string
    {
        if ($normalizedPaths === []) {
            throw new \RuntimeException('No TIFF pages were loaded for merging.');
        }

        $currentRoundPaths = $normalizedPaths;
        $round = 0;

        while (count($currentRoundPaths) > 1) {
            $round++;
            $nextRoundPaths = [];

            foreach (array_chunk($currentRoundPaths, $this->mergeChunkSize($backend)) as $chunkIndex => $chunkPaths) {
                if (count($chunkPaths) === 1) {
                    $nextRoundPaths[] = $chunkPaths[0];

                    continue;
                }

                $mergedChunkPath = $temporaryDirectory . DIRECTORY_SEPARATOR . sprintf(
                    'merged-round-%02d-%03d.tif',
                    $round,
                    $chunkIndex + 1
                );

                $this->mergeNormalizedChunk($chunkPaths, $mergedChunkPath, $backend);
                $nextRoundPaths[] = $mergedChunkPath;

                foreach ($chunkPaths as $chunkPath) {
                    $this->removeFile($chunkPath);
                }
            }

            $currentRoundPaths = $nextRoundPaths;
        }

        return $currentRoundPaths[0];
    }

    /**
     * @param array<int, string> $chunkPaths
     * @param array{type: 'cli', binary: string}|array{type: 'imagick'} $backend
     */
    protected function mergeNormalizedChunk(array $chunkPaths, string $outputAbsolutePath, array $backend): void
    {
        if ($backend['type'] === 'cli') {
            $this->mergeNormalizedChunkViaCli($chunkPaths, $outputAbsolutePath, $backend['binary']);

            return;
        }

        $this->mergeNormalizedChunkViaImagick($chunkPaths, $outputAbsolutePath);
    }

    /**
     * @param array<int, string> $chunkPaths
     */
    protected function mergeNormalizedChunkViaCli(array $chunkPaths, string $outputAbsolutePath, string $binary): void
    {
        $commandRun = $this->runCommand([
            $binary,
            ...$this->imageMagickRuntimeArguments(),
            ...$chunkPaths,
            '-adjoin',
            '-define',
            'tiff:compression=group4',
            '-compress',
            'Group4',
            $outputAbsolutePath,
        ]);

        if ($commandRun['exit_code'] !== 0 || !$this->isUsableFile($outputAbsolutePath)) {
            throw new \RuntimeException($this->formatCommandOutput($commandRun['output_lines']));
        }
    }

    protected function normalizeInputViaCli(string $inputAbsolutePath, string $outputAbsolutePath, string $binary): void
    {
        $commandRun = $this->runCommand([
            $binary,
            ...$this->imageMagickRuntimeArguments(),
            $inputAbsolutePath,
            '-background',
            'white',
            '-alpha',
            'remove',
            '-alpha',
            'off',
            '-colorspace',
            'Gray',
            '-threshold',
            '50%',
            '-type',
            'bilevel',
            '-adjoin',
            '-define',
            'tiff:compression=group4',
            '-compress',
            'Group4',
            $outputAbsolutePath,
        ]);

        if ($commandRun['exit_code'] !== 0 || !$this->isUsableFile($outputAbsolutePath)) {
            throw new \RuntimeException($this->formatCommandOutput($commandRun['output_lines']));
        }
    }

    protected function normalizeInputViaImagick(string $inputAbsolutePath, string $outputAbsolutePath): void
    {
        $source = new \Imagick();
        $document = new \Imagick();

        try {
            $source->readImage($inputAbsolutePath);

            foreach ($source as $frame) {
                $preparedFrame = $this->prepareFrameForCcittGroup4($frame);
                $document->addImage(clone $preparedFrame);
                $preparedFrame->clear();
                $preparedFrame->destroy();
            }

            if ($document->getNumberImages() < 1) {
                throw new \RuntimeException('No TIFF pages were loaded for merging.');
            }

            $this->finalizeDocumentForCcittGroup4($document);
            $document->resetIterator();
            $document->writeImages($outputAbsolutePath, true);
        } finally {
            $source->clear();
            $source->destroy();
            $document->clear();
            $document->destroy();
        }

        if (!$this->isUsableFile($outputAbsolutePath)) {
            throw new \RuntimeException('Normalized TIFF/TIF output was not created successfully.');
        }
    }

    /**
     * @param array<int, string> $chunkPaths
     */
    protected function mergeNormalizedChunkViaImagick(array $chunkPaths, string $outputAbsolutePath): void
    {
        $document = new \Imagick();

        try {
            foreach ($chunkPaths as $chunkPath) {
                $source = new \Imagick();

                try {
                    $source->readImage($chunkPath);

                    foreach ($source as $frame) {
                        $preparedFrame = $this->prepareNormalizedFrameForOutput($frame);
                        $document->addImage(clone $preparedFrame);
                        $preparedFrame->clear();
                        $preparedFrame->destroy();
                    }
                } finally {
                    $source->clear();
                    $source->destroy();
                }
            }

            if ($document->getNumberImages() < 1) {
                throw new \RuntimeException('No TIFF pages were loaded for merging.');
            }

            $this->finalizeDocumentForCcittGroup4($document);
            $document->resetIterator();
            $document->writeImages($outputAbsolutePath, true);
        } finally {
            $document->clear();
            $document->destroy();
        }

        if (!$this->isUsableFile($outputAbsolutePath)) {
            throw new \RuntimeException('Merged TIFF/TIF output was not created successfully.');
        }
    }

    protected function prepareFrameForCcittGroup4(\Imagick $frame): \Imagick
    {
        $prepared = clone $frame;
        $prepared->setImageBackgroundColor(new \ImagickPixel('white'));

        if (method_exists($prepared, 'mergeImageLayers')) {
            $flattened = $prepared->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            if ($flattened instanceof \Imagick) {
                $prepared->clear();
                $prepared->destroy();
                $prepared = $flattened;
            }
        }

        if (method_exists($prepared, 'stripImage')) {
            $prepared->stripImage();
        }

        $prepared->setImagePage(0, 0, 0, 0);
        $prepared->setImageColorspace(\Imagick::COLORSPACE_GRAY);
        $prepared->thresholdImage($this->resolveThresholdValue($prepared));

        if (defined('\Imagick::IMGTYPE_BILEVEL')) {
            $prepared->setImageType(\Imagick::IMGTYPE_BILEVEL);
        }

        $prepared->setImageDepth(1);
        $prepared->setImageFormat('tiff');
        $this->applyGroup4Compression($prepared);

        return $prepared;
    }

    protected function prepareNormalizedFrameForOutput(\Imagick $frame): \Imagick
    {
        $prepared = clone $frame;
        $prepared->setImagePage(0, 0, 0, 0);
        $prepared->setImageFormat('tiff');
        $prepared->setImageDepth(1);

        if (defined('\Imagick::IMGTYPE_BILEVEL')) {
            $prepared->setImageType(\Imagick::IMGTYPE_BILEVEL);
        }

        $this->applyGroup4Compression($prepared);

        return $prepared;
    }

    protected function finalizeDocumentForCcittGroup4(\Imagick $document): void
    {
        foreach ($document as $frame) {
            $frame->setImagePage(0, 0, 0, 0);
            $frame->setImageFormat('tiff');
            $frame->setImageDepth(1);

            if (defined('\Imagick::IMGTYPE_BILEVEL')) {
                $frame->setImageType(\Imagick::IMGTYPE_BILEVEL);
            }

            $this->applyGroup4Compression($frame);
        }
    }

    protected function applyGroup4Compression(\Imagick $image): void
    {
        if (defined('\Imagick::COMPRESSION_GROUP4')) {
            $image->setImageCompression(\Imagick::COMPRESSION_GROUP4);
        } elseif (defined('\Imagick::COMPRESSION_CCITTFAX4')) {
            $image->setImageCompression(\Imagick::COMPRESSION_CCITTFAX4);
        }

        $image->setOption('tiff:compression', 'group4');
    }

    protected function resolveThresholdValue(\Imagick $image): float
    {
        $quantumRange = $image->getQuantumRange();
        $maxQuantum = $quantumRange['quantumRangeLong'] ?? $quantumRange['quantumRangeString'] ?? 65535;

        return ((float) $maxQuantum) / 2;
    }

    protected function mergeChunkSize(array $backend): int
    {
        $configuredSize = $backend['type'] === 'cli'
            ? (int) config('services.imagemagick.cli_chunk_size', self::CLI_CHUNK_SIZE)
            : (int) config('services.imagemagick.imagick_chunk_size', self::IMAGICK_CHUNK_SIZE);

        return max(2, $configuredSize);
    }

    protected function prepareExecutionEnvironment(): void
    {
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '-1');
    }

    protected function activateTemporaryPath(string $temporaryDirectory): void
    {
        $configuredTemporaryPath = trim((string) config('services.imagemagick.temporary_path', ''));
        $resolvedTemporaryPath = $configuredTemporaryPath !== '' ? $configuredTemporaryPath : $temporaryDirectory;

        if (!is_dir($resolvedTemporaryPath)) {
            @mkdir($resolvedTemporaryPath, 0775, true);
        }

        if (!is_dir($resolvedTemporaryPath) || !is_writable($resolvedTemporaryPath)) {
            $resolvedTemporaryPath = $temporaryDirectory;
        }

        $this->activeTemporaryPath = $resolvedTemporaryPath;

        if (function_exists('putenv')) {
            foreach (['MAGICK_TEMPORARY_PATH', 'MAGICK_TMPDIR', 'TMPDIR', 'TMP', 'TEMP'] as $environmentVariable) {
                @putenv($environmentVariable . '=' . $resolvedTemporaryPath);
            }
        }

        $this->configureImagickRuntime($resolvedTemporaryPath);
    }

    protected function deactivateTemporaryPath(): void
    {
        $this->activeTemporaryPath = null;
    }

    protected function configureImagickRuntime(string $temporaryPath): void
    {
        if (!class_exists(\Imagick::class)) {
            return;
        }

        if (method_exists(\Imagick::class, 'setRegistry')) {
            try {
                \Imagick::setRegistry('temporary-path', $temporaryPath);
            } catch (Throwable) {
                // Ignore runtime registry errors and continue with default behavior.
            }
        }

        $threadLimit = max(1, (int) config('services.imagemagick.thread_limit', 1));
        $memoryLimitMb = max(0, (int) config('services.imagemagick.memory_limit_mb', 256));
        $mapLimitMb = max(0, (int) config('services.imagemagick.map_limit_mb', 512));
        $diskLimitMb = max(0, (int) config('services.imagemagick.disk_limit_mb', 4096));

        $this->applyImagickResourceLimit('RESOURCETYPE_THREAD', $threadLimit);
        $this->applyImagickResourceLimit('RESOURCETYPE_MEMORY', $this->megabytesToBytes($memoryLimitMb));
        $this->applyImagickResourceLimit('RESOURCETYPE_MAP', $this->megabytesToBytes($mapLimitMb));
        $this->applyImagickResourceLimit('RESOURCETYPE_DISK', $this->megabytesToBytes($diskLimitMb));
    }

    protected function applyImagickResourceLimit(string $constantName, int $limit): void
    {
        $constantReference = \Imagick::class . '::' . $constantName;

        if ($limit < 1 || !defined($constantReference)) {
            return;
        }

        try {
            \Imagick::setResourceLimit(constant($constantReference), $limit);
        } catch (Throwable) {
            // Ignore unsupported limit calls and continue with available defaults.
        }
    }

    protected function megabytesToBytes(int $megabytes): int
    {
        return max(0, $megabytes) * 1024 * 1024;
    }

    /**
     * @return array<int, string>
     */
    protected function imageMagickRuntimeArguments(): array
    {
        $arguments = [];
        $threadLimit = max(1, (int) config('services.imagemagick.thread_limit', 1));
        $memoryLimitMb = max(0, (int) config('services.imagemagick.memory_limit_mb', 256));
        $mapLimitMb = max(0, (int) config('services.imagemagick.map_limit_mb', 512));
        $diskLimitMb = max(0, (int) config('services.imagemagick.disk_limit_mb', 4096));

        $arguments = array_merge($arguments, ['-limit', 'thread', (string) $threadLimit]);

        if ($memoryLimitMb > 0) {
            $arguments = array_merge($arguments, ['-limit', 'memory', $memoryLimitMb . 'MiB']);
        }

        if ($mapLimitMb > 0) {
            $arguments = array_merge($arguments, ['-limit', 'map', $mapLimitMb . 'MiB']);
        }

        if ($diskLimitMb > 0) {
            $arguments = array_merge($arguments, ['-limit', 'disk', $diskLimitMb . 'MiB']);
        }

        if ($this->activeTemporaryPath !== null) {
            $arguments = array_merge($arguments, ['-define', 'registry:temporary-path=' . $this->activeTemporaryPath]);
        }

        return $arguments;
    }

    protected function canRunShellCommands(): bool
    {
        return function_exists('proc_open')
            || function_exists('exec')
            || function_exists('shell_exec');
    }

    protected function detectImageMagickBinary(): ?string
    {
        $candidates = array_values(array_unique(array_filter(array_merge(
            $this->configuredImageMagickCandidates(),
            $this->defaultImageMagickCandidates()
        ))));

        foreach ($candidates as $candidate) {
            if ($this->looksLikeFilesystemPath($candidate) && !is_file($candidate)) {
                continue;
            }

            $commandRun = $this->runCommand([$candidate, '-version']);
            $output = Str::lower(trim(implode("\n", $commandRun['output_lines'])));

            if ($commandRun['exit_code'] === 0 && Str::contains($output, 'imagemagick')) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function configuredImageMagickCandidates(): array
    {
        $binary = (string) config('services.imagemagick.binary', '');

        return $binary !== '' ? [$binary] : [];
    }

    /**
     * @return array<int, string>
     */
    protected function defaultImageMagickCandidates(): array
    {
        if ($this->isWindows()) {
            return array_merge(
                [
                    'magick.exe',
                    'magick',
                ],
                glob('C:\\Program Files\\ImageMagick*\\magick.exe') ?: [],
                glob('C:\\Program Files (x86)\\ImageMagick*\\magick.exe') ?: [],
                glob('C:\\ImageMagick*\\magick.exe') ?: []
            );
        }

        return [
            '/usr/bin/magick',
            '/usr/local/bin/magick',
            '/opt/homebrew/bin/magick',
            '/bin/magick',
            '/usr/bin/convert',
            '/usr/local/bin/convert',
            '/bin/convert',
            '/usr/bin/convert-im6.q16',
            'magick',
            'convert',
            'convert-im6.q16',
        ];
    }

    protected function looksLikeFilesystemPath(string $candidate): bool
    {
        return preg_match('/[\/\\\\]/', $candidate) === 1;
    }

    /**
     * @param array<int, string> $commandParts
     * @return array{exit_code: int, output_lines: array<int, string>}
     */
    protected function runCommand(array $commandParts): array
    {
        if (function_exists('proc_open')) {
            $procOpenResult = $this->runCommandViaProcOpen($commandParts);
            if ($procOpenResult !== null) {
                return $procOpenResult;
            }
        }

        $command = $this->buildCommandString($commandParts);

        if (function_exists('exec')) {
            return $this->runCommandViaExec($command);
        }

        if (function_exists('shell_exec')) {
            return $this->runCommandViaShellExec($command);
        }

        return [
            'exit_code' => 1,
            'output_lines' => ['No shell execution function is available.'],
        ];
    }

    /**
     * @param array<int, string> $commandParts
     * @return array{exit_code: int, output_lines: array<int, string>}|null
     */
    protected function runCommandViaProcOpen(array $commandParts): ?array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $pipes = [];

        try {
            $process = @proc_open($commandParts, $descriptorSpec, $pipes, null, null, ['bypass_shell' => true]);
        } catch (Throwable) {
            return null;
        }

        if (!is_resource($process)) {
            return null;
        }

        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        $stdout = isset($pipes[1]) && is_resource($pipes[1]) ? stream_get_contents($pipes[1]) : '';
        $stderr = isset($pipes[2]) && is_resource($pipes[2]) ? stream_get_contents($pipes[2]) : '';

        if (isset($pipes[1]) && is_resource($pipes[1])) {
            fclose($pipes[1]);
        }

        if (isset($pipes[2]) && is_resource($pipes[2])) {
            fclose($pipes[2]);
        }

        $exitCode = proc_close($process);

        return [
            'exit_code' => (int) $exitCode,
            'output_lines' => $this->splitOutputLines((string) $stdout . "\n" . (string) $stderr),
        ];
    }

    /**
     * @param array<int, string> $commandParts
     */
    protected function buildCommandString(array $commandParts): string
    {
        return implode(' ', array_map(
            static fn (string $part): string => escapeshellarg($part),
            $commandParts
        ));
    }

    /**
     * @return array{exit_code: int, output_lines: array<int, string>}
     */
    protected function runCommandViaExec(string $command): array
    {
        $outputLines = [];
        $exitCode = 1;
        exec($command . ' 2>&1', $outputLines, $exitCode);

        return [
            'exit_code' => $exitCode,
            'output_lines' => $outputLines,
        ];
    }

    /**
     * @return array{exit_code: int, output_lines: array<int, string>}
     */
    protected function runCommandViaShellExec(string $command): array
    {
        $marker = '__AROSOFT_EXIT__' . str_replace('-', '', (string) Str::uuid());
        $rawOutput = $this->isWindows()
            ? shell_exec($command . ' 2>&1 & echo ' . $marker . ':%ERRORLEVEL%')
            : shell_exec($command . ' 2>&1; printf "\\n' . $marker . ':%s" $?');

        $trimmedOutput = trim(is_string($rawOutput) ? $rawOutput : '');
        $exitCode = 1;
        $markerPosition = strrpos($trimmedOutput, $marker . ':');

        if ($markerPosition !== false) {
            $exitSegment = substr($trimmedOutput, $markerPosition + strlen($marker) + 1);
            if (is_numeric($exitSegment)) {
                $exitCode = (int) $exitSegment;
            }

            $trimmedOutput = trim(substr($trimmedOutput, 0, $markerPosition));
        }

        return [
            'exit_code' => $exitCode,
            'output_lines' => $this->splitOutputLines($trimmedOutput),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function splitOutputLines(string $rawOutput): array
    {
        $trimmedOutput = trim($rawOutput);

        return $trimmedOutput === ''
            ? []
            : (preg_split('/\r\n|\r|\n/', $trimmedOutput) ?: []);
    }

    protected function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    protected function isUsableFile(string $path): bool
    {
        return is_file($path) && filesize($path) > 0;
    }

    /**
     * @param array<int, string> $inputAbsolutePaths
     */
    protected function assertInputFilesAreReadable(array $inputAbsolutePaths): void
    {
        foreach ($inputAbsolutePaths as $inputAbsolutePath) {
            if (!is_file($inputAbsolutePath) || !is_readable($inputAbsolutePath)) {
                throw new \RuntimeException('One or more TIFF/TIF input files could not be read.');
            }
        }
    }

    protected function ensureOutputDirectoryExists(string $outputAbsolutePath): void
    {
        $directory = dirname($outputAbsolutePath);

        if ($directory === '' || $directory === '.') {
            throw new \RuntimeException('TIFF/TIF output directory is invalid.');
        }

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('TIFF/TIF output directory could not be created.');
        }

        if (!is_writable($directory)) {
            throw new \RuntimeException('TIFF/TIF output directory is not writable.');
        }
    }

    protected function createTemporaryWorkspace(string $baseDirectory): string
    {
        $temporaryDirectory = $baseDirectory . DIRECTORY_SEPARATOR . 'tiff-merge-' . Str::uuid();

        if (!@mkdir($temporaryDirectory, 0775, true) && !is_dir($temporaryDirectory)) {
            throw new \RuntimeException('Temporary TIFF/TIF merge workspace could not be created.');
        }

        return $temporaryDirectory;
    }

    protected function resolveWorkspaceBaseDirectory(string $outputAbsolutePath): string
    {
        $configuredTemporaryPath = trim((string) config('services.imagemagick.temporary_path', ''));

        if ($configuredTemporaryPath !== '') {
            if (!is_dir($configuredTemporaryPath)) {
                @mkdir($configuredTemporaryPath, 0775, true);
            }

            if (is_dir($configuredTemporaryPath) && is_writable($configuredTemporaryPath)) {
                return $configuredTemporaryPath;
            }
        }

        return dirname($outputAbsolutePath);
    }

    protected function publishOutput(string $temporaryOutputPath, string $outputAbsolutePath): void
    {
        $this->removeFile($outputAbsolutePath);

        if (@rename($temporaryOutputPath, $outputAbsolutePath)) {
            return;
        }

        if (!@copy($temporaryOutputPath, $outputAbsolutePath)) {
            throw new \RuntimeException('Merged TIFF/TIF output could not be written to the destination path.');
        }

        $this->removeFile($temporaryOutputPath);
    }

    protected function formatCommandOutput(array $outputLines): string
    {
        $output = trim(implode("\n", $outputLines));

        return $output !== ''
            ? $output
            : 'ImageMagick command failed without returning output.';
    }

    protected function formatMergeError(string $rawOutput): string
    {
        $normalized = Str::lower($rawOutput);

        if (Str::contains($normalized, 'not authorized')) {
            return 'TIFF/TIF merge is blocked by ImageMagick policy. Enable TIFF read/write permissions and retry.';
        }

        if (Str::contains($normalized, ['no decode delegate', 'unable to open image', 'could not be read'])) {
            return 'Server ImageMagick cannot read one of the TIFF/TIF files.';
        }

        if (Str::contains($normalized, ['group4', 'ccitt'])) {
            return 'Server ImageMagick cannot write CCITT4 Group 4 TIFF output.';
        }

        if (Str::contains($normalized, [
            'bits/sample',
            'bilevel',
            'fax3setupstate',
            'compression scheme does not support',
            'photometric',
            'predictor',
        ])) {
            return 'Server ImageMagick could not encode one of the TIFF/TIF pages as CCITT4 Group 4 output.';
        }

        if (Str::contains($normalized, ['cache resources exhausted', 'memory allocation failed', 'not enough memory'])) {
            return 'Server ImageMagick ran out of memory or disk cache while merging this batch. Increase ImageMagick limits or split the upload.';
        }

        if (Str::contains($normalized, ['no space left on device', 'disk full', 'unable to extend cache', 'unable to create temporary file'])) {
            return 'Server storage ran out of space for TIFF/TIF merging. Increase temporary disk space and retry.';
        }

        if (Str::contains($normalized, ['output directory', 'destination path', 'workspace'])) {
            return 'Server storage is not ready for TIFF/TIF merging. Verify writable temporary and result directories.';
        }

        return 'Failed to merge TIFF/TIF files into one CCITT4 Group 4 TIFF.';
    }

    protected function removeFile(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        @unlink($path);
    }

    protected function removeDirectory(string $directory): void
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

            $this->removeFile($path);
        }

        @rmdir($directory);
    }
}
