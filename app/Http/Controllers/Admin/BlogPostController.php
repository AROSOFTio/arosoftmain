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

        $posts = BlogPost::query()
            ->with(['author:id,name', 'category:id,name'])
            ->when(
                $filters['status'] !== '',
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                $filters['category'] !== '',
                fn ($query) => $query->where('category_id', (int) $filters['category'])
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
            'categories' => BlogCategory::query()->orderBy('name')->get(['id', 'name']),
            'authors' => User::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BlogPost::class);

        return view('admin.blog.posts.create', $this->formData(new BlogPost([
            'status' => 'draft',
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
            'categories' => BlogCategory::query()->orderBy('name')->get(['id', 'name']),
            'tags' => BlogTag::query()->orderBy('name')->get(['id', 'name']),
            'authors' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function persist(UpsertBlogPostRequest $request, BlogPost $post): void
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $post): void {
            $post->fill([
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
            ]);

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
            $name = trim((string) $validated['new_category']);
            $slug = Str::slug($name);

            if ($slug !== '') {
                $category = BlogCategory::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::title($name)]
                );

                return (int) $category->id;
            }
        }

        return !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveStatus(array $validated): string
    {
        $status = (string) $validated['status'];
        $publishedAt = !empty($validated['published_at'])
            ? Carbon::parse((string) $validated['published_at'])
            : null;

        if ($status === 'published' && $publishedAt?->isFuture()) {
            return 'scheduled';
        }

        return $status;
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

        if ($status === 'scheduled') {
            return now()->addHour();
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

