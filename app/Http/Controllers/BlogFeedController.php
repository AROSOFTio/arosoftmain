<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class BlogFeedController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember(
            'blog:sitemap:xml',
            now()->addMinutes(config('blog.feed_cache_ttl_minutes', 30)),
            function (): string {
                $posts = BlogPost::query()
                    ->publiclyVisible()
                    ->orderByDesc('published_at')
                    ->get(['slug', 'updated_at', 'published_at']);

                $categories = BlogCategory::query()
                    ->whereHas('posts', fn ($query) => $query->publiclyVisible())
                    ->orderBy('name')
                    ->get(['slug', 'updated_at']);

                $tags = BlogTag::query()
                    ->whereHas('posts', fn ($query) => $query->publiclyVisible())
                    ->orderBy('name')
                    ->get(['slug', 'updated_at']);

                return view('blog.feeds.sitemap', [
                    'posts' => $posts,
                    'categories' => $categories,
                    'tags' => $tags,
                ])->render();
            }
        );

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function rss(): Response
    {
        $xml = Cache::remember(
            'blog:rss:xml',
            now()->addMinutes(config('blog.feed_cache_ttl_minutes', 30)),
            function (): string {
                $posts = BlogPost::query()
                    ->publiclyVisible()
                    ->with('author:id,name')
                    ->orderByDesc('published_at')
                    ->limit(30)
                    ->get();

                return view('blog.feeds.rss', [
                    'posts' => $posts,
                ])->render();
            }
        );

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}

