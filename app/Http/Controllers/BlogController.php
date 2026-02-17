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

        return $this->renderListing(
            title: 'Arosoft Blog',
            heading: 'Insights, tutorials, and product updates',
            queryBuilder: BlogPost::query()->publiclyVisible(),
            queryText: $queryText
        );
    }

    public function category(string $slug): View
    {
        $category = BlogCategory::query()->where('slug', $slug)->firstOrFail();

        return $this->renderListing(
            title: 'Category: '.$category->name,
            heading: $category->name,
            queryBuilder: BlogPost::query()
                ->publiclyVisible()
                ->where('category_id', $category->id),
            category: $category
        );
    }

    public function tag(string $slug): View
    {
        $tag = BlogTag::query()->where('slug', $slug)->firstOrFail();

        return $this->renderListing(
            title: 'Tag: '.$tag->name,
            heading: '#'.$tag->name,
            queryBuilder: BlogPost::query()
                ->publiclyVisible()
                ->whereHas('tags', fn (Builder $query) => $query->where('blog_tags.id', $tag->id)),
            tag: $tag
        );
    }

    public function search(Request $request): View|RedirectResponse
    {
        $queryText = trim((string) $request->query('q', ''));

        if ($queryText === '') {
            return redirect()->route('blog');
        }

        return $this->renderListing(
            title: 'Search results',
            heading: 'Search results for "'.$queryText.'"',
            queryBuilder: BlogPost::query()->publiclyVisible(),
            queryText: $queryText
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
        ?BlogTag $tag = null
    ): View {
        $queryBuilder
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->when($queryText !== '', function (Builder $query) use ($queryText): void {
                $term = '%'.str_replace(' ', '%', $queryText).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('excerpt', 'like', $term)
                        ->orWhere('body', 'like', $term);
                });
            });

        $featuredPost = (clone $queryBuilder)
            ->orderByDesc('published_at')
            ->first();

        $posts = (clone $queryBuilder)
            ->when($featuredPost, fn ($query) => $query->where('id', '!=', $featuredPost->id))
            ->orderByDesc('published_at')
            ->paginate(9)
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
        ]);
    }
}
