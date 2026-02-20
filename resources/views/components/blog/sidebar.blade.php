@props([
    'categories' => collect(),
    'tags' => collect(),
    'popularPosts' => collect(),
    'latestPosts' => collect(),
    'queryText' => '',
])

<div class="space-y-5">
    <section class="info-card">
        <h3 class="font-heading text-lg">Search articles</h3>
        <form action="{{ route('blog.search') }}" method="get" class="mt-3 space-y-3">
            <label for="blog-search" class="sr-only">Search blog</label>
            <input
                id="blog-search"
                type="search"
                name="q"
                value="{{ $queryText }}"
                placeholder="Search topics..."
                class="form-field"
            >
            <button type="submit" class="btn-solid !w-full !text-[0.68rem]">Search</button>
        </form>
    </section>

    <section class="info-card">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-heading text-lg">Categories</h3>
            <a href="{{ route('blog') }}" class="nav-link-sm">All</a>
        </div>
        <ul class="mt-3 space-y-2 text-sm">
            @forelse($categories as $category)
                @php $depth = (int) ($category->depth ?? 0); @endphp
                <li class="flex items-center justify-between gap-3">
                    <a href="{{ route('blog.category', $category->slug) }}" class="min-w-0 flex-1 break-words hover:text-[color:var(--accent)]">
                        {{ str_repeat('-- ', $depth) }}{{ $category->name }}
                    </a>
                    <span class="shrink-0 muted-faint">{{ $category->published_posts_count }}</span>
                </li>
            @empty
                <li class="muted-faint">No categories yet.</li>
            @endforelse
        </ul>
    </section>

    <section class="info-card">
        <h3 class="font-heading text-lg">Popular posts</h3>
        <div class="mt-3 space-y-3">
            @forelse($popularPosts as $post)
                <article>
                    <a href="{{ route('blog.show', $post->slug) }}" class="block break-words text-sm font-semibold leading-6 hover:text-[color:var(--accent)]">
                        {{ $post->title }}
                    </a>
                    <p class="mt-1 text-xs muted-faint">{{ number_format((int) $post->view_count) }} views</p>
                </article>
            @empty
                <p class="text-sm muted-faint">No popular posts yet.</p>
            @endforelse
        </div>
    </section>

    <section class="info-card">
        <h3 class="font-heading text-lg">Latest posts</h3>
        <div class="mt-3 space-y-3">
            @forelse($latestPosts as $post)
                <article>
                    <a href="{{ route('blog.show', $post->slug) }}" class="block break-words text-sm font-semibold leading-6 hover:text-[color:var(--accent)]">
                        {{ $post->title }}
                    </a>
                    <p class="mt-1 text-xs muted-faint">{{ optional($post->published_at)->format('M d, Y') }}</p>
                </article>
            @empty
                <p class="text-sm muted-faint">No recent posts yet.</p>
            @endforelse
        </div>
    </section>

    <section class="info-card">
        <h3 class="font-heading text-lg">Tags</h3>
        <div class="mt-3 flex flex-wrap gap-2">
            @forelse($tags as $tag)
                <a href="{{ route('blog.tag', $tag->slug) }}" class="nav-link-sm break-all !py-1 !px-3">
                    #{{ $tag->name }}
                </a>
            @empty
                <span class="text-sm muted-faint">No tags yet.</span>
            @endforelse
        </div>
    </section>

    <section class="info-card">
        <p class="page-kicker">Newsletter</p>
        <h3 class="mt-2 font-heading text-lg">Stay updated with Arosoft</h3>
        <p class="mt-2 text-sm leading-7 muted-copy">Get notified when we publish practical guides and tech updates.</p>
        <a href="{{ route('contact') }}" class="btn-solid mt-4 !w-full !text-[0.68rem]">Request updates</a>
    </section>
</div>
