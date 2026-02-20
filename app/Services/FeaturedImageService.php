<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FeaturedImageService
{
    private const TARGET_WIDTH = 1200;
    private const TARGET_HEIGHT = 630;
    private const QUALITY = 86;

    public function store(UploadedFile $file): string
    {
        $binary = class_exists(\Imagick::class)
            ? $this->processWithImagick($file)
            : $this->processWithGd($file);

        $path = sprintf(
            'blog/featured/%s/%s.jpg',
            now()->format('Y/m'),
            Str::uuid()->toString()
        );

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function processWithImagick(UploadedFile $file): string
    {
        $imagick = new \Imagick();
        $imagick->readImage($file->getRealPath());
        $imagick->setIteratorIndex(0);

        if (method_exists($imagick, 'autoOrient')) {
            $imagick->autoOrient();
        } elseif (method_exists($imagick, 'autoOrientImage')) {
            $imagick->autoOrientImage();
        }

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        if ($width < 1 || $height < 1) {
            $imagick->clear();
            $imagick->destroy();
            throw new RuntimeException('Invalid image dimensions.');
        }

        [$cropWidth, $cropHeight, $cropX, $cropY] = $this->centerCropBox($width, $height);

        $imagick->cropImage($cropWidth, $cropHeight, $cropX, $cropY);
        $imagick->setImagePage(0, 0, 0, 0);
        $imagick->resizeImage(
            self::TARGET_WIDTH,
            self::TARGET_HEIGHT,
            \Imagick::FILTER_LANCZOS,
            1,
            true
        );
        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(self::QUALITY);

        $binary = (string) $imagick->getImageBlob();

        $imagick->clear();
        $imagick->destroy();

        if ($binary === '') {
            throw new RuntimeException('Unable to encode featured image.');
        }

        return $binary;
    }

    private function processWithGd(UploadedFile $file): string
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            throw new RuntimeException('GD extension is required to process featured images.');
        }

        $contents = file_get_contents($file->getRealPath());
        if ($contents === false) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        $source = imagecreatefromstring($contents);
        if ($source === false) {
            throw new RuntimeException('Unsupported image format.');
        }

        $source = $this->autoOrientGdImage($source, $file);

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth < 1 || $sourceHeight < 1) {
            imagedestroy($source);
            throw new RuntimeException('Invalid image dimensions.');
        }

        [$cropWidth, $cropHeight, $cropX, $cropY] = $this->centerCropBox($sourceWidth, $sourceHeight);

        $target = imagecreatetruecolor(self::TARGET_WIDTH, self::TARGET_HEIGHT);
        if ($target === false) {
            imagedestroy($source);
            throw new RuntimeException('Unable to initialize image canvas.');
        }

        $background = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $background);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $cropX,
            $cropY,
            self::TARGET_WIDTH,
            self::TARGET_HEIGHT,
            $cropWidth,
            $cropHeight
        );

        ob_start();
        imagejpeg($target, null, self::QUALITY);
        $binary = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($target);

        if ($binary === '') {
            throw new RuntimeException('Unable to encode featured image.');
        }

        return $binary;
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function centerCropBox(int $sourceWidth, int $sourceHeight): array
    {
        $targetRatio = self::TARGET_WIDTH / self::TARGET_HEIGHT;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($cropHeight * $targetRatio);
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($cropWidth / $targetRatio);
        }

        $cropX = max(0, (int) floor(($sourceWidth - $cropWidth) / 2));
        $cropY = max(0, (int) floor(($sourceHeight - $cropHeight) / 2));

        return [$cropWidth, $cropHeight, $cropX, $cropY];
    }

    private function autoOrientGdImage(mixed $image, UploadedFile $file): mixed
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $extension = Str::lower($file->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotateDegrees = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($rotateDegrees === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $rotateDegrees, 0);
        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
