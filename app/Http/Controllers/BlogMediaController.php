<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogMediaController extends Controller
{
    public function show(string $path): Response
    {
        $normalizedPath = ltrim($path, '/');
        if (!Str::startsWith($normalizedPath, 'blog/')) {
            $normalizedPath = 'blog/'.$normalizedPath;
        }

        if (
            $normalizedPath === ''
            || Str::contains($normalizedPath, ['..', '\\'])
            || !Str::startsWith($normalizedPath, 'blog/')
        ) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($normalizedPath)) {
            abort(404);
        }

        $content = $disk->get($normalizedPath);

        return response($content, 200, [
            'Content-Type' => $this->mimeTypeFromPath($normalizedPath),
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function mimeTypeFromPath(string $path): string
    {
        $extension = Str::lower((string) pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }
}
