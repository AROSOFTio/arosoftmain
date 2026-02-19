<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    private static ?bool $supportsFeaturedFlag = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image_path',
        'featured_image_alt',
        'is_featured',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image_path',
        'reading_time_minutes',
        'view_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'reading_time_minutes' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where(function (Builder $query): void {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            })->orWhere(function (Builder $query): void {
                $query->where('status', 'scheduled')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            });
        });
    }

    public function isPubliclyVisible(): bool
    {
        if (!$this->published_at instanceof Carbon) {
            return false;
        }

        return in_array($this->status, ['published', 'scheduled'], true)
            && $this->published_at->isPast();
    }

    public function featuredImageUrl(): ?string
    {
        if (!$this->featured_image_path) {
            return null;
        }

        $path = ltrim((string) $this->featured_image_path, '/');
        $routePath = Str::startsWith($path, 'blog/') ? (string) Str::after($path, 'blog/') : $path;

        return route('blog.media', [
            'path' => $routePath,
        ]);
    }

    public function ogImageUrl(): ?string
    {
        if ($this->og_image_path) {
            $path = ltrim((string) $this->og_image_path, '/');
            $routePath = Str::startsWith($path, 'blog/') ? (string) Str::after($path, 'blog/') : $path;

            return route('blog.media', [
                'path' => $routePath,
            ]);
        }

        return $this->featuredImageUrl();
    }

    public static function supportsFeaturedFlag(): bool
    {
        return self::$supportsFeaturedFlag ??= Schema::hasColumn((new self())->getTable(), 'is_featured');
    }
}
