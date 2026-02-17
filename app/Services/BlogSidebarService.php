<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Support\AdminSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BlogSidebarService
{
    public function data(): array
    {
        return [
            'categories' => $this->categories(),
            'tags' => $this->tags(),
            'popularPosts' => $this->popularPosts(),
            'latestPosts' => $this->latestPosts(),
        ];
    }

    public function flush(): void
    {
        $current = (int) Cache::get('blog:sidebar:version', 1);
        Cache::forever('blog:sidebar:version', $current + 1);
    }

    /**
     * @return Collection<int, BlogCategory>
     */
    private function categories(): Collection
    {
        return Cache::remember(
            $this->key('categories'),
            now()->addMinutes((int) AdminSettings::get('sidebar_cache_ttl_minutes', config('blog.sidebar_cache_ttl_minutes', 20))),
            fn (): Collection => BlogCategory::query()
                ->whereHas('posts', fn ($query) => $query->publiclyVisible())
                ->withCount(['posts as published_posts_count' => fn ($query) => $query->publiclyVisible()])
                ->orderByDesc('published_posts_count')
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * @return Collection<int, BlogTag>
     */
    private function tags(): Collection
    {
        return Cache::remember(
            $this->key('tags'),
            now()->addMinutes((int) AdminSettings::get('sidebar_cache_ttl_minutes', config('blog.sidebar_cache_ttl_minutes', 20))),
            fn (): Collection => BlogTag::query()
                ->whereHas('posts', fn ($query) => $query->publiclyVisible())
                ->withCount(['posts as published_posts_count' => fn ($query) => $query->publiclyVisible()])
                ->orderByDesc('published_posts_count')
                ->orderBy('name')
                ->limit(25)
                ->get()
        );
    }

    /**
     * @return Collection<int, BlogPost>
     */
    private function popularPosts(): Collection
    {
        return Cache::remember(
            $this->key('popular'),
            now()->addMinutes((int) AdminSettings::get('sidebar_cache_ttl_minutes', config('blog.sidebar_cache_ttl_minutes', 20))),
            fn (): Collection => BlogPost::query()
                ->publiclyVisible()
                ->with(['category'])
                ->orderByDesc('view_count')
                ->orderByDesc('published_at')
                ->limit(6)
                ->get()
        );
    }

    /**
     * @return Collection<int, BlogPost>
     */
    private function latestPosts(): Collection
    {
        return Cache::remember(
            $this->key('latest'),
            now()->addMinutes((int) AdminSettings::get('sidebar_cache_ttl_minutes', config('blog.sidebar_cache_ttl_minutes', 20))),
            fn (): Collection => BlogPost::query()
                ->publiclyVisible()
                ->with(['category'])
                ->orderByDesc('published_at')
                ->limit(6)
                ->get()
        );
    }

    private function key(string $suffix): string
    {
        $version = (int) Cache::get('blog:sidebar:version', 1);

        return 'blog:sidebar:'.$version.':'.$suffix;
    }
}
