@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<rss version="2.0">
    <channel>
        <title>Arosoft Blog</title>
        <link>{{ route('blog') }}</link>
        <description>Latest articles from Arosoft Innovations Ltd.</description>
        <language>en-us</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>

        @foreach($posts as $post)
            <item>
                <title><![CDATA[{{ $post->title }}]]></title>
                <link>{{ route('blog.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
                <description><![CDATA[{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 220) }}]]></description>
                <pubDate>{{ optional($post->published_at)->toRssString() }}</pubDate>
                <author>{{ $post->author?->name ?? 'Arosoft Team' }}</author>
            </item>
        @endforeach
    </channel>
</rss>
