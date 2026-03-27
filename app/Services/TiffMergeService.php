<?php

namespace App\Services;

use Illuminate\Support\Str;
use Throwable;

class TiffMergeService
{
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

        try {
            if (class_exists(\Imagick::class)) {
                $this->mergeViaImagick($inputAbsolutePaths, $outputAbsolutePath);
            } else {
                $mergeResult = $this->mergeViaCli($inputAbsolutePaths, $outputAbsolutePath);
                if (!$mergeResult['ok']) {
                    return $mergeResult;
                }
            }
        } catch (Throwable $error) {
            $this->removeFile($outputAbsolutePath);

            return [
                'ok' => false,
                'message' => $this->formatMergeError($error->getMessage()),
            ];
        }

        if (!is_file($outputAbsolutePath) || filesize($outputAbsolutePath) === 0) {
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
     * @param array<int, string> $inputAbsolutePaths
     */
    private function mergeViaImagick(array $inputAbsolutePaths, string $outputAbsolutePath): void
    {
        $document = new \Imagick();

        try {
            foreach ($inputAbsolutePaths as $inputAbsolutePath) {
                $source = new \Imagick();
                $source->readImage($inputAbsolutePath);

                foreach ($source as $frame) {
                    $preparedFrame = $this->prepareFrameForCcittGroup4($frame);
                    $document->addImage(clone $preparedFrame);
                    $preparedFrame->clear();
                    $preparedFrame->destroy();
                }

                $source->clear();
                $source->destroy();
            }

            if ($document->getNumberImages() < 1) {
                throw new \RuntimeException('No TIFF pages were loaded for merging.');
            }

            $document->resetIterator();
            $document->writeImages($outputAbsolutePath, true);
        } finally {
            $document->clear();
            $document->destroy();
        }
    }

    /**
     * @param array<int, string> $inputAbsolutePaths
     * @return array{ok: bool, message: string}
     */
    private function mergeViaCli(array $inputAbsolutePaths, string $outputAbsolutePath): array
    {
        if (!$this->canRunShellCommands()) {
            return [
                'ok' => false,
                'message' => 'TIFF/TIF merge requires PHP Imagick or ImageMagick CLI with command execution enabled on this server.',
            ];
        }

        $binary = $this->detectImageMagickBinary();
        if ($binary === null) {
            return [
                'ok' => false,
                'message' => 'ImageMagick CLI binary was not found. Install ImageMagick or enable PHP Imagick extension.',
            ];
        }

        $inputArguments = implode(' ', array_map(
            fn (string $path): string => escapeshellarg($path),
            $inputAbsolutePaths
        ));

        $command = escapeshellarg($binary)
            . ' '
            . $inputArguments
            . ' -background white -alpha remove -alpha off -colorspace Gray -threshold 50% -type bilevel'
            . ' -define tiff:compression=group4 -compress Group4 '
            . escapeshellarg($outputAbsolutePath)
            . ' 2>&1';

        $commandRun = $this->runShellCommand($command);
        $output = trim(implode("\n", $commandRun['output_lines']));

        if ($commandRun['exit_code'] !== 0 || !is_file($outputAbsolutePath) || filesize($outputAbsolutePath) === 0) {
            $this->removeFile($outputAbsolutePath);

            return [
                'ok' => false,
                'message' => $this->formatMergeError($output),
            ];
        }

        return [
            'ok' => true,
            'message' => 'TIFF/TIF files merged successfully.',
        ];
    }

    private function prepareFrameForCcittGroup4(\Imagick $frame): \Imagick
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

    private function applyGroup4Compression(\Imagick $image): void
    {
        if (defined('\Imagick::COMPRESSION_GROUP4')) {
            $image->setImageCompression(\Imagick::COMPRESSION_GROUP4);
        } elseif (defined('\Imagick::COMPRESSION_CCITTFAX4')) {
            $image->setImageCompression(\Imagick::COMPRESSION_CCITTFAX4);
        }

        $image->setOption('tiff:compression', 'group4');
    }

    private function resolveThresholdValue(\Imagick $image): float
    {
        $quantumRange = $image->getQuantumRange();
        $maxQuantum = $quantumRange['quantumRangeLong'] ?? $quantumRange['quantumRangeString'] ?? 65535;

        return ((float) $maxQuantum) / 2;
    }

    private function canRunShellCommands(): bool
    {
        return function_exists('exec')
            || function_exists('shell_exec')
            || function_exists('proc_open');
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
            'magick',
            'convert',
        ];

        foreach ($candidates as $candidate) {
            if (Str::contains($candidate, DIRECTORY_SEPARATOR)) {
                if (is_file($candidate) && is_executable($candidate)) {
                    return $candidate;
                }

                continue;
            }

            return $candidate;
        }

        return null;
    }

    /**
     * @return array{exit_code: int, output_lines: array<int, string>}
     */
    private function runShellCommand(string $command): array
    {
        $commandWithStderr = Str::contains($command, '2>&1')
            ? $command
            : $command . ' 2>&1';

        if (function_exists('exec')) {
            $outputLines = [];
            $exitCode = 1;
            exec($commandWithStderr, $outputLines, $exitCode);

            return [
                'exit_code' => $exitCode,
                'output_lines' => $outputLines,
            ];
        }

        if (function_exists('shell_exec')) {
            $marker = '__AROSOFT_EXIT__' . str_replace('-', '', (string) Str::uuid());
            $rawOutput = shell_exec($commandWithStderr . '; printf "\\n' . $marker . ':%s" $?');
            $rawOutput = is_string($rawOutput) ? $rawOutput : '';

            $exitCode = 1;
            $trimmedOutput = trim($rawOutput);
            $markerPosition = strrpos($trimmedOutput, $marker . ':');
            if ($markerPosition !== false) {
                $exitSegment = substr($trimmedOutput, $markerPosition + strlen($marker) + 1);
                if (is_numeric($exitSegment)) {
                    $exitCode = (int) $exitSegment;
                }

                $trimmedOutput = trim(substr($trimmedOutput, 0, $markerPosition));
            }

            $outputLines = $trimmedOutput === ''
                ? []
                : (preg_split('/\r\n|\r|\n/', $trimmedOutput) ?: []);

            return [
                'exit_code' => $exitCode,
                'output_lines' => $outputLines,
            ];
        }

        if (function_exists('proc_open')) {
            $descriptorSpec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $pipes = [];
            $process = @proc_open($commandWithStderr, $descriptorSpec, $pipes);
            if (!is_resource($process)) {
                return [
                    'exit_code' => 1,
                    'output_lines' => ['Unable to start shell process.'],
                ];
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
            $combined = trim((string) $stdout . "\n" . (string) $stderr);
            $outputLines = $combined === ''
                ? []
                : (preg_split('/\r\n|\r|\n/', $combined) ?: []);

            return [
                'exit_code' => (int) $exitCode,
                'output_lines' => $outputLines,
            ];
        }

        return [
            'exit_code' => 1,
            'output_lines' => ['No shell execution function is available.'],
        ];
    }

    private function formatMergeError(string $rawOutput): string
    {
        $normalized = Str::lower($rawOutput);

        if (Str::contains($normalized, 'not authorized')) {
            return 'TIFF/TIF merge is blocked by ImageMagick policy. Enable TIFF read/write permissions and retry.';
        }

        if (Str::contains($normalized, 'no decode delegate')) {
            return 'Server ImageMagick cannot read one of the TIFF/TIF files.';
        }

        if (Str::contains($normalized, 'group4') || Str::contains($normalized, 'ccitt')) {
            return 'Server ImageMagick cannot write CCITT4 Group 4 TIFF output.';
        }

        return 'Failed to merge TIFF/TIF files into one CCITT4 Group 4 TIFF.';
    }

    private function removeFile(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        @unlink($path);
    }
}
