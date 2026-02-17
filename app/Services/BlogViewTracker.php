<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogViewTracker
{
    public function track(BlogPost $post, Request $request): void
    {
        $fingerprint = hash(
            'sha256',
            implode('|', [
                (string) $post->id,
                (string) $request->session()->getId(),
                (string) $request->ip(),
                substr((string) $request->userAgent(), 0, 120),
            ])
        );

        $cacheKey = 'blog:post_viewed:'.$post->id.':'.$fingerprint;
        $ttlHours = (int) config('blog.view_count_window_hours', 12);

        if (!Cache::add($cacheKey, 1, now()->addHours($ttlHours))) {
            return;
        }

        BlogPost::query()->whereKey($post->id)->increment('view_count');
        $post->view_count++;
    }
}

