@props(['post'])

<aside class="my-8 rounded-2xl border border-[color:rgba(0,157,49,0.28)] bg-[color:rgba(0,157,49,0.08)] p-5">
    <p class="page-kicker">Related read</p>
    <h4 class="mt-2 font-heading text-xl leading-snug">
        <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[color:var(--accent)]">
            {{ $post->title }}
        </a>
    </h4>
    <p class="mt-2 text-sm leading-7 muted-copy">
        {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150) }}
    </p>
    <a href="{{ route('blog.show', $post->slug) }}" class="btn-outline mt-4 !w-auto !px-4 !py-2 !text-[0.68rem]">Open article</a>
</aside>

