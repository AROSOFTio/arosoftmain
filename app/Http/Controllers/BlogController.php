<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\BlogRelatedService;
use App\Services\BlogSidebarService;
use App\Services\BlogViewTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogSidebarService $sidebarService,
        private readonly BlogRelatedService $relatedService,
        private readonly BlogViewTracker $viewTracker,
    ) {
    }

    public function index(Request $request): View
    {
        $queryText = trim((string) $request->query('q', ''));
        $sort = $this->resolveSort((string) $request->query('sort', 'latest'));
        $categorySlug = trim((string) $request->query('category', ''));
        $activeCategory = null;
        $queryBuilder = BlogPost::query()->publiclyVisible();

        if ($categorySlug !== '') {
            $activeCategory = BlogCategory::query()->where('slug', $categorySlug)->first();

            if ($activeCategory instanceof BlogCategory) {
                $queryBuilder->whereIn('category_id', $activeCategory->descendantAndSelfIds());
            } else {
                $queryBuilder->whereRaw('1 = 0');
            }
        }

        return $this->renderListing(
            title: 'Arosoft Blog',
            heading: 'Insights, tutorials, and product updates',
            queryBuilder: $queryBuilder,
            queryText: $queryText,
            category: $activeCategory,
            sort: $sort,
        );
    }

    public function category(string $slug): View
    {
        $category = BlogCategory::query()->where('slug', $slug)->firstOrFail();
        $sort = $this->resolveSort((string) request()->query('sort', 'latest'));

        return $this->renderListing(
            title: 'Category: '.$category->name,
            heading: $category->name,
            queryBuilder: BlogPost::query()
                ->publiclyVisible()
                ->whereIn('category_id', $category->descendantAndSelfIds()),
            category: $category,
            sort: $sort,
        );
    }

    public function tag(string $slug): View
    {
        $tag = BlogTag::query()->where('slug', $slug)->firstOrFail();
        $sort = $this->resolveSort((string) request()->query('sort', 'latest'));

        return $this->renderListing(
            title: 'Tag: '.$tag->name,
            heading: '#'.$tag->name,
            queryBuilder: BlogPost::query()
                ->publiclyVisible()
                ->whereHas('tags', fn (Builder $query) => $query->where('blog_tags.id', $tag->id)),
            tag: $tag,
            sort: $sort,
        );
    }

    public function search(Request $request): View|RedirectResponse
    {
        $queryText = trim((string) $request->query('q', ''));
        $sort = $this->resolveSort((string) $request->query('sort', 'latest'));

        if ($queryText === '') {
            return redirect()->route('blog');
        }

        return $this->renderListing(
            title: 'Search results',
            heading: 'Search results for "'.$queryText.'"',
            queryBuilder: BlogPost::query()->publiclyVisible(),
            queryText: $queryText,
            sort: $sort,
        );
    }

    public function show(Request $request, string $slug): View
    {
        $post = BlogPost::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->viewTracker->track($post, $request);

        $relatedPosts = $this->relatedService->relatedForPost($post, 6);
        $bodyWithInline = $this->relatedService->injectInlineBlocks(
            $post->body,
            $relatedPosts->take(2)
        );

        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Blog', 'url' => route('blog')],
            ['name' => $post->title, 'url' => route('blog.show', $post->slug)],
        ];

        return view('blog.show', [
            'post' => $post,
            'bodyWithInline' => $bodyWithInline,
            'relatedPosts' => $relatedPosts->take(4),
            'breadcrumbs' => $breadcrumbs,
            'sidebar' => $this->sidebarService->data(),
        ]);
    }

    private function renderListing(
        string $title,
        string $heading,
        Builder $queryBuilder,
        string $queryText = '',
        ?BlogCategory $category = null,
        ?BlogTag $tag = null,
        string $sort = 'latest',
    ): View {
        $baseQuery = $queryBuilder
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->when($queryText !== '', function (Builder $query) use ($queryText): void {
                $term = '%'.str_replace(' ', '%', $queryText).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('excerpt', 'like', $term)
                        ->orWhere('body', 'like', $term);
                });
            });

        $featuredPost = null;

        if (BlogPost::supportsFeaturedFlag()) {
            $featuredPost = (clone $baseQuery)
                ->where('is_featured', true)
                ->orderByDesc('published_at')
                ->first();
        }

        $postsQuery = (clone $baseQuery)
            ->when($featuredPost, fn ($query) => $query->where('id', '!=', $featuredPost->id));

        $this->applySort($postsQuery, $sort);

        $posts = $postsQuery
            ->paginate(12)
            ->withQueryString();

        return view('blog.index', [
            'title' => $title,
            'heading' => $heading,
            'featuredPost' => $featuredPost,
            'posts' => $posts,
            'sidebar' => $this->sidebarService->data(),
            'queryText' => $queryText,
            'activeCategory' => $category,
            'activeTag' => $tag,
            'sort' => $sort,
        ]);
    }

    private function applySort(Builder $query, string $sort): void
    {
        if ($sort === 'popular') {
            $query->orderByDesc('view_count')->orderByDesc('published_at');
            return;
        }

        if ($sort === 'oldest') {
            $query->orderBy('published_at');
            return;
        }

        $query->orderByDesc('published_at');
    }

    private function resolveSort(string $sort): string
    {
        return in_array($sort, ['latest', 'popular', 'oldest'], true) ? $sort : 'latest';
    }
}
