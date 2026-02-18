<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BlogCategory extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    /**
     * @return list<int>
     */
    public function descendantAndSelfIds(): array
    {
        return self::descendantAndSelfIdsFor((int) $this->id);
    }

    /**
     * @return list<int>
     */
    public static function descendantAndSelfIdsFor(int $rootId): array
    {
        $categories = self::query()->get(['id', 'parent_id']);
        $childrenByParent = $categories->groupBy(fn (BlogCategory $category) => (int) ($category->parent_id ?? 0));

        $ids = [];
        $stack = [$rootId];

        while ($stack !== []) {
            $currentId = (int) array_pop($stack);

            if (in_array($currentId, $ids, true)) {
                continue;
            }

            $ids[] = $currentId;

            /** @var Collection<int, BlogCategory> $children */
            $children = $childrenByParent->get($currentId, collect());

            foreach ($children as $child) {
                $stack[] = (int) $child->id;
            }
        }

        return $ids;
    }
}
