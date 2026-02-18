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
            function (): Collection {
                $categories = BlogCategory::query()
                    ->orderBy('name')
                    ->get(['id', 'parent_id', 'name', 'slug', 'description']);

                $directCounts = BlogPost::query()
                    ->publiclyVisible()
                    ->whereNotNull('category_id')
                    ->selectRaw('category_id, COUNT(*) as aggregate')
                    ->groupBy('category_id')
                    ->pluck('aggregate', 'category_id');

                $childrenByParent = $categories->groupBy(fn (BlogCategory $category) => (int) ($category->parent_id ?? 0));
                $aggregateCounts = [];

                $countWithDescendants = function (int $categoryId) use (&$countWithDescendants, $childrenByParent, $directCounts, &$aggregateCounts): int {
                    $count = (int) ($directCounts[$categoryId] ?? 0);

                    foreach ($childrenByParent->get($categoryId, collect()) as $child) {
                        $count += $countWithDescendants((int) $child->id);
                    }

                    $aggregateCounts[$categoryId] = $count;

                    return $count;
                };

                foreach ($childrenByParent->get(0, collect()) as $rootCategory) {
                    $countWithDescendants((int) $rootCategory->id);
                }

                return $this->flattenCategoryTree($childrenByParent, $aggregateCounts);
            }
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

    /**
     * @param Collection<int, Collection<int, BlogCategory>> $childrenByParent
     * @param array<int, int> $aggregateCounts
     * @return Collection<int, BlogCategory>
     */
    private function flattenCategoryTree(Collection $childrenByParent, array $aggregateCounts): Collection
    {
        $flattened = collect();

        $walk = function (int $parentId, int $depth) use (&$walk, $childrenByParent, $aggregateCounts, $flattened): void {
            $siblings = $childrenByParent
                ->get($parentId, collect())
                ->sortBy(fn (BlogCategory $category): string => strtolower($category->name));

            foreach ($siblings as $category) {
                $count = (int) ($aggregateCounts[(int) $category->id] ?? 0);

                if ($count < 1) {
                    continue;
                }

                $category->setAttribute('published_posts_count', $count);
                $category->setAttribute('depth', $depth);
                $flattened->push($category);

                $walk((int) $category->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $flattened;
    }
}
