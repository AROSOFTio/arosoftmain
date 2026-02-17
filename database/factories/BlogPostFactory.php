<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);
        $body = collect(fake()->paragraphs(6))
            ->map(fn (string $paragraph): string => '<p>'.$paragraph.'</p>')
            ->implode('');

        return [
            'user_id' => User::factory(),
            'category_id' => BlogCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(2),
            'body' => $body,
            'status' => 'published',
            'published_at' => now()->subDays(random_int(1, 30)),
            'reading_time_minutes' => max(1, (int) ceil(str_word_count(strip_tags($body)) / 200)),
            'robots' => 'index,follow',
            'view_count' => random_int(0, 500),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}

