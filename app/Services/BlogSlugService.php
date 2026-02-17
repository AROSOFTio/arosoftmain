<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Support\Str;

class BlogSlugService
{
    public function generate(string $title, ?string $preferredSlug = null, ?int $ignorePostId = null): string
    {
        $base = Str::slug($preferredSlug ?: $title);

        if ($base === '') {
            $base = 'post';
        }

        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $ignorePostId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignorePostId = null): bool
    {
        return BlogPost::query()
            ->when($ignorePostId, fn ($query) => $query->where('id', '!=', $ignorePostId))
            ->where('slug', $slug)
            ->exists();
    }
}
