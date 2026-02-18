<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Support\AdminSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BlogFeedController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember(
            'blog:sitemap:xml',
            now()->addMinutes((int) AdminSettings::get('feed_cache_ttl_minutes', config('blog.feed_cache_ttl_minutes', 30))),
            function (): string {
                $posts = BlogPost::query()
                    ->publiclyVisible()
                    ->orderByDesc('published_at')
                    ->get(['slug', 'updated_at', 'published_at']);

                $directCategoryIds = BlogPost::query()
                    ->publiclyVisible()
                    ->whereNotNull('category_id')
                    ->distinct()
                    ->pluck('category_id')
                    ->map(fn ($id): int => (int) $id);

                $allCategories = BlogCategory::query()
                    ->orderBy('name')
                    ->get(['id', 'parent_id', 'slug', 'updated_at']);

                $categoriesById = $allCategories->keyBy('id');
                $categoryIdsForSitemap = [];

                foreach ($directCategoryIds as $directCategoryId) {
                    $currentId = $directCategoryId;

                    while ($currentId > 0 && !in_array($currentId, $categoryIdsForSitemap, true)) {
                        $categoryIdsForSitemap[] = $currentId;
                        $currentId = (int) optional($categoriesById->get($currentId))->parent_id;
                    }
                }

                $categories = $allCategories
                    ->whereIn('id', $categoryIdsForSitemap)
                    ->values();

                $tags = BlogTag::query()
                    ->whereHas('posts', fn ($query) => $query->publiclyVisible())
                    ->orderBy('name')
                    ->get(['slug', 'updated_at']);

                return $this->buildSitemapXml($posts, $categories, $tags);
            }
        );

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function rss(): Response
    {
        $xml = Cache::remember(
            'blog:rss:xml',
            now()->addMinutes((int) AdminSettings::get('feed_cache_ttl_minutes', config('blog.feed_cache_ttl_minutes', 30))),
            function (): string {
                $posts = BlogPost::query()
                    ->publiclyVisible()
                    ->with('author:id,name')
                    ->orderByDesc('published_at')
                    ->limit(30)
                    ->get();

                return $this->buildRssXml($posts);
            }
        );

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    /**
     * @param Collection<int, BlogPost> $posts
     */
    private function buildRssXml(Collection $posts): string
    {
        $items = $posts->map(function (BlogPost $post): string {
            $title = $this->cdata((string) $post->title);
            $link = $this->xml((string) route('blog.show', $post->slug));
            $description = $this->cdata((string) ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 220)));
            $pubDate = optional($post->published_at)->toRssString();
            $author = $this->xml((string) ($post->author?->name ?? 'Arosoft Team'));

            return implode("\n", [
                '        <item>',
                "            <title>{$title}</title>",
                "            <link>{$link}</link>",
                "            <guid isPermaLink=\"true\">{$link}</guid>",
                "            <description>{$description}</description>",
                '            <pubDate>'.$this->xml((string) $pubDate).'</pubDate>',
                "            <author>{$author}</author>",
                '        </item>',
            ]);
        })->implode("\n");

        return implode("\n", [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0">',
            '    <channel>',
            '        <title>'.$this->xml('Arosoft Blog').'</title>',
            '        <link>'.$this->xml((string) route('blog')).'</link>',
            '        <description>'.$this->xml('Latest articles from Arosoft Innovations Ltd.').'</description>',
            '        <language>en-us</language>',
            '        <lastBuildDate>'.$this->xml((string) now()->toRssString()).'</lastBuildDate>',
            $items,
            '    </channel>',
            '</rss>',
        ]);
    }

    /**
     * @param Collection<int, BlogPost> $posts
     * @param Collection<int, BlogCategory> $categories
     * @param Collection<int, BlogTag> $tags
     */
    private function buildSitemapXml(Collection $posts, Collection $categories, Collection $tags): string
    {
        $urls = collect([
            [
                'loc' => route('home'),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('blog'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ]);

        foreach ($posts as $post) {
            $urls->push([
                'loc' => route('blog.show', $post->slug),
                'lastmod' => optional($post->updated_at)->toAtomString() ?: optional($post->published_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);
        }

        foreach ($categories as $category) {
            $urls->push([
                'loc' => route('blog.category', $category->slug),
                'lastmod' => optional($category->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ]);
        }

        foreach ($tags as $tag) {
            $urls->push([
                'loc' => route('blog.tag', $tag->slug),
                'lastmod' => optional($tag->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ]);
        }

        $urlNodes = $urls->map(function (array $url): string {
            $lines = [
                '    <url>',
                '        <loc>'.$this->xml((string) $url['loc']).'</loc>',
            ];

            if (!empty($url['lastmod'])) {
                $lines[] = '        <lastmod>'.$this->xml((string) $url['lastmod']).'</lastmod>';
            }

            $lines[] = '        <changefreq>'.$this->xml((string) $url['changefreq']).'</changefreq>';
            $lines[] = '        <priority>'.$this->xml((string) $url['priority']).'</priority>';
            $lines[] = '    </url>';

            return implode("\n", $lines);
        })->implode("\n");

        return implode("\n", [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            $urlNodes,
            '</urlset>',
        ]);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function cdata(string $value): string
    {
        return '<![CDATA['.str_replace(']]>', ']]]]><![CDATA[>', $value).']]>';
    }
}
