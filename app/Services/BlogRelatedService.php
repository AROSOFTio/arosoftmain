<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Collection;

class BlogRelatedService
{
    /**
     * @return Collection<int, BlogPost>
     */
    public function relatedForPost(BlogPost $post, int $limit = 4): Collection
    {
        $post->loadMissing('tags');

        /** @var Collection<int, BlogPost> $related */
        $related = new Collection();

        if ($post->category_id) {
            $related = BlogPost::query()
                ->publiclyVisible()
                ->with(['category', 'author'])
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();
        }

        if ($related->count() < $limit && $post->tags->isNotEmpty()) {
            $needed = $limit - $related->count();
            $existingIds = $related->pluck('id')->push($post->id)->all();

            $tagBased = BlogPost::query()
                ->publiclyVisible()
                ->with(['category', 'author'])
                ->whereHas('tags', fn ($query) => $query->whereIn('blog_tags.id', $post->tags->pluck('id')))
                ->whereNotIn('id', $existingIds)
                ->orderByDesc('published_at')
                ->limit($needed)
                ->get();

            $related = $related->concat($tagBased);
        }

        if ($related->count() < $limit) {
            $needed = $limit - $related->count();
            $existingIds = $related->pluck('id')->push($post->id)->all();

            $fallback = BlogPost::query()
                ->publiclyVisible()
                ->with(['category', 'author'])
                ->whereNotIn('id', $existingIds)
                ->orderByDesc('published_at')
                ->limit($needed)
                ->get();

            $related = $related->concat($fallback);
        }

        return new Collection($related->take($limit)->all());
    }

    /**
     * @param Collection<int, BlogPost> $inlineRelated
     */
    public function injectInlineBlocks(string $html, Collection $inlineRelated): string
    {
        if ($html === '' || $inlineRelated->isEmpty()) {
            return $html;
        }

        $firstPost = $inlineRelated->first();
        if ($firstPost instanceof BlogPost) {
            $html = $this->insertAfterParagraph(
                $html,
                view('components.blog.related-inline', ['post' => $firstPost])->render(),
                3
            );
        }

        $secondPost = $inlineRelated->skip(1)->first();
        if (!$secondPost instanceof BlogPost) {
            return $html;
        }

        $paragraphCount = $this->paragraphCount($html);
        $insertAfter = max(4, $paragraphCount - 2);

        return $this->insertAfterParagraph(
            $html,
            view('components.blog.related-inline', ['post' => $secondPost])->render(),
            $insertAfter
        );
    }

    private function paragraphCount(string $html): int
    {
        if (!preg_match_all('/<\/p>/i', $html, $matches)) {
            return 0;
        }

        return count($matches[0]);
    }

    private function insertAfterParagraph(string $html, string $snippet, int $afterParagraph): string
    {
        if (!preg_match_all('/<\/p>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            return $html.$snippet;
        }

        $paragraphs = $matches[0];
        $index = min(max(1, $afterParagraph), count($paragraphs)) - 1;
        $match = $paragraphs[$index];
        $offset = $match[1] + strlen($match[0]);

        return substr($html, 0, $offset).$snippet.substr($html, $offset);
    }
}
