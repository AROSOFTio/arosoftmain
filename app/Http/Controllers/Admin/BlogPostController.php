<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use App\Services\BlogContentSanitizer;
use App\Services\BlogReadingTimeService;
use App\Services\BlogRelatedService;
use App\Services\BlogSidebarService;
use App\Services\BlogSlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function __construct(
        private readonly BlogSlugService $slugService,
        private readonly BlogContentSanitizer $sanitizer,
        private readonly BlogReadingTimeService $readingTimeService,
        private readonly BlogSidebarService $sidebarService,
        private readonly BlogRelatedService $relatedService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BlogPost::class);

        $filters = [
            'status' => (string) $request->query('status', ''),
            'category' => (string) $request->query('category', ''),
            'author' => (string) $request->query('author', ''),
            'search' => trim((string) $request->query('search', '')),
        ];

        if (!in_array($filters['status'], ['', 'draft', 'published'], true)) {
            $filters['status'] = '';
        }

        $posts = BlogPost::query()
            ->with(['author:id,name', 'category:id,name'])
            ->when(
                $filters['status'] !== '',
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                $filters['category'] !== '',
                fn ($query) => $query->whereIn(
                    'category_id',
                    BlogCategory::descendantAndSelfIdsFor((int) $filters['category'])
                )
            )
            ->when(
                $filters['author'] !== '',
                fn ($query) => $query->where('user_id', (int) $filters['author'])
            )
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $term = '%'.str_replace(' ', '%', $filters['search']).'%';

                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                        ->orWhere('excerpt', 'like', $term);
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog.posts.index', [
            'posts' => $posts,
            'categories' => $this->hierarchicalCategories(),
            'authors' => User::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BlogPost::class);

        return view('admin.blog.posts.create', $this->formData(new BlogPost([
            'status' => 'published',
            'robots' => 'index,follow',
        ])));
    }

    public function store(UpsertBlogPostRequest $request): RedirectResponse
    {
        $this->authorize('create', BlogPost::class);

        $post = new BlogPost();
        $this->persist($request, $post);

        return redirect()
            ->route('admin.blog.posts.edit', $post)
            ->with('status', 'Post created successfully.');
    }

    public function edit(BlogPost $blogPost): View
    {
        $this->authorize('update', $blogPost);

        $blogPost->load('tags');

        return view('admin.blog.posts.edit', $this->formData($blogPost));
    }

    public function update(UpsertBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $this->authorize('update', $blogPost);

        $this->persist($request, $blogPost);

        return redirect()
            ->route('admin.blog.posts.edit', $blogPost)
            ->with('status', 'Post updated successfully.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $this->authorize('delete', $blogPost);

        DB::transaction(function () use ($blogPost): void {
            $this->deleteImageIfPresent($blogPost->featured_image_path);
            $this->deleteImageIfPresent($blogPost->og_image_path);

            $blogPost->tags()->detach();
            $blogPost->delete();
        });

        $this->invalidateBlogCaches();

        return redirect()
            ->route('admin.blog.posts.index')
            ->with('status', 'Post deleted successfully.');
    }

    public function preview(BlogPost $blogPost): View
    {
        $this->authorize('update', $blogPost);

        $blogPost->load(['author', 'category', 'tags']);
        $inlineRelated = $this->relatedService->relatedForPost($blogPost, 2);
        $bodyWithInline = $this->relatedService->injectInlineBlocks($blogPost->body, $inlineRelated);

        return view('admin.blog.posts.preview', [
            'post' => $blogPost,
            'bodyWithInline' => $bodyWithInline,
            'bottomRelated' => $this->relatedService->relatedForPost($blogPost, 4),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(BlogPost $blogPost): array
    {
        return [
            'post' => $blogPost,
            'categories' => $this->hierarchicalCategories(),
            'tags' => BlogTag::query()->orderBy('name')->get(['id', 'name']),
            'authors' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function persist(UpsertBlogPostRequest $request, BlogPost $post): void
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $post): void {
            $attributes = [
                'user_id' => (int) ($validated['user_id'] ?? $request->user()->id),
                'category_id' => $this->resolveCategoryId($validated),
                'title' => $validated['title'],
                'slug' => $this->slugService->generate(
                    $validated['title'],
                    $validated['slug'] ?? null,
                    $post->exists ? (int) $post->id : null
                ),
                'excerpt' => $validated['excerpt'] ?? null,
                'body' => $this->sanitizer->sanitizeForStorage($validated['body']),
                'featured_image_alt' => $validated['featured_image_alt'] ?? null,
                'status' => $this->resolveStatus($validated),
                'published_at' => $this->resolvePublishedAt($validated),
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'meta_keywords' => $validated['meta_keywords'] ?? null,
                'canonical_url' => $validated['canonical_url'] ?? null,
                'robots' => $validated['robots'] ?? 'index,follow',
                'og_title' => $validated['og_title'] ?? null,
                'og_description' => $validated['og_description'] ?? null,
            ];

            if (BlogPost::supportsFeaturedFlag()) {
                $attributes['is_featured'] = $request->boolean('is_featured');
            }

            $post->fill($attributes);

            $post->reading_time_minutes = $this->readingTimeService->calculateMinutes($post->body);

            if ($request->boolean('remove_featured_image')) {
                $this->deleteImageIfPresent($post->featured_image_path);
                $post->featured_image_path = null;
            }

            if ($request->hasFile('featured_image')) {
                $this->deleteImageIfPresent($post->featured_image_path);
                $post->featured_image_path = $request->file('featured_image')?->store('blog', 'public');
            }

            if ($request->boolean('remove_og_image')) {
                $this->deleteImageIfPresent($post->og_image_path);
                $post->og_image_path = null;
            }

            if ($request->hasFile('og_image')) {
                $this->deleteImageIfPresent($post->og_image_path);
                $post->og_image_path = $request->file('og_image')?->store('blog', 'public');
            }

            $post->save();

            if (BlogPost::supportsFeaturedFlag() && $post->is_featured) {
                BlogPost::query()
                    ->whereKeyNot($post->id)
                    ->where('is_featured', true)
                    ->update(['is_featured' => false]);
            }

            $post->tags()->sync($this->resolveTagIds($validated));
        });

        $this->invalidateBlogCaches();
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveCategoryId(array $validated): ?int
    {
        if (!empty($validated['new_category'])) {
            $segments = $this->parseCategoryPath((string) $validated['new_category']);

            if ($segments !== []) {
                $parentId = null;
                $slugSegments = [];

                foreach ($segments as $segment) {
                    $slugPart = Str::slug($segment);

                    if ($slugPart === '') {
                        continue;
                    }

                    $slugSegments[] = $slugPart;
                    $category = BlogCategory::query()->firstOrCreate(
                        ['slug' => implode('-', $slugSegments)],
                        [
                            'name' => Str::title($segment),
                            'parent_id' => $parentId,
                        ]
                    );

                    if ((int) ($category->parent_id ?? 0) !== (int) ($parentId ?? 0)) {
                        $category->parent_id = $parentId;
                        $category->save();
                    }

                    $parentId = (int) $category->id;
                }

                if ($parentId) {
                    return $parentId;
                }
            }
        }

        return !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
    }

    /**
     * @return Collection<int, BlogCategory>
     */
    private function hierarchicalCategories(): Collection
    {
        $categories = BlogCategory::query()
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);

        $childrenByParent = $categories->groupBy(fn (BlogCategory $category) => (int) ($category->parent_id ?? 0));
        $flattened = collect();

        $walk = function (int $parentId, int $depth) use (&$walk, $childrenByParent, $flattened): void {
            $siblings = $childrenByParent
                ->get($parentId, collect())
                ->sortBy(fn (BlogCategory $category): string => strtolower($category->name));

            foreach ($siblings as $category) {
                $category->setAttribute('depth', $depth);
                $flattened->push($category);
                $walk((int) $category->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $flattened;
    }

    /**
     * @return list<string>
     */
    private function parseCategoryPath(string $rawPath): array
    {
        $normalized = trim($rawPath);

        if ($normalized === '') {
            return [];
        }

        $segments = preg_split('/\s*(?:>|\/|\\\\|\|)\s*/', $normalized) ?: [];

        if (
            count($segments) === 1
            && str_contains($normalized, '-')
            && substr_count($normalized, '-') >= 2
            && !str_contains($normalized, ' ')
        ) {
            $segments = explode('-', $normalized);
        }

        return collect($segments)
            ->map(fn (string $segment): string => trim($segment))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveStatus(array $validated): string
    {
        return ((string) $validated['status']) === 'draft' ? 'draft' : 'published';
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolvePublishedAt(array $validated): ?Carbon
    {
        $status = (string) $validated['status'];
        $publishedAt = !empty($validated['published_at'])
            ? Carbon::parse((string) $validated['published_at'])
            : null;

        if ($status === 'draft') {
            return null;
        }

        if ($publishedAt instanceof Carbon) {
            return $publishedAt;
        }

        return now();
    }

    /**
     * @param array<string, mixed> $validated
     * @return list<int>
     */
    private function resolveTagIds(array $validated): array
    {
        $ids = collect($validated['tags'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->values();

        if (!empty($validated['new_tags'])) {
            $newTagNames = collect(preg_split('/[,;\n]+/', (string) $validated['new_tags']) ?: [])
                ->map(fn (string $tag): string => trim($tag))
                ->filter()
                ->unique();

            foreach ($newTagNames as $name) {
                $slug = Str::slug($name);

                if ($slug === '') {
                    continue;
                }

                $tag = BlogTag::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::title($name)]
                );

                $ids->push((int) $tag->id);
            }
        }

        return $ids->unique()->values()->all();
    }

    private function deleteImageIfPresent(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function invalidateBlogCaches(): void
    {
        $this->sidebarService->flush();
        Cache::forget('blog:sitemap:xml');
        Cache::forget('blog:rss:xml');
    }
}
