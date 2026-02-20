@props([
    'post',
    'showExcerpt' => true,
    'compact' => false,
])

@php
    $excerptSource = $post->excerpt ?: strip_tags((string) $post->body);
    $excerptWords = $compact ? 15 : 28;
    $cardExcerpt = \Illuminate\Support\Str::words(trim(strip_tags((string) $excerptSource)), $excerptWords);
@endphp

<article {{ $attributes->class([
    'shell-card blog-card h-full overflow-hidden rounded-2xl',
    'blog-card--compact' => $compact,
]) }}>
    @if($post->featuredImageUrl())
        <a href="{{ route('blog.show', $post->slug) }}" class="block">
            <img
                src="{{ $post->featuredImageUrl() }}"
                alt="{{ $post->featured_image_alt ?: $post->title }}"
                loading="lazy"
                class="{{ $compact ? 'h-36' : 'h-52' }} w-full object-cover"
            >
        </a>
    @endif

    <div class="blog-card-body p-5">
        <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.12em] muted-faint">
            @if($post->category)
                <a href="{{ route('blog.category', $post->category->slug) }}" class="nav-link-sm !px-2 !py-1">{{ $post->category->name }}</a>
            @endif
            @if($post->published_at)
                <span>{{ $post->published_at->format('M d, Y') }}</span>
            @endif
            <span>{{ $post->reading_time_minutes ?: 1 }} min read</span>
        </div>

        <h3 class="blog-card-title font-heading {{ $compact ? 'text-lg' : 'text-2xl' }} leading-snug">
            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[color:var(--accent)]">
                {{ $post->title }}
            </a>
        </h3>

        @if($showExcerpt)
            <p class="blog-card-excerpt text-sm leading-7 muted-copy">
                {{ $cardExcerpt }}
            </p>
        @endif

        <div class="blog-card-footer flex flex-wrap items-center gap-3 text-sm">
            <span class="min-w-0 break-words muted-faint">By {{ $post->author?->name ?? 'Arosoft Team' }}</span>
            <a href="{{ route('blog.show', $post->slug) }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Read</a>
        </div>
    </div>
</article>
