<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBlogPostCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_blog_post(): void
    {
        $admin = User::factory()->admin()->create();
        $category = BlogCategory::factory()->create();
        $tag = BlogTag::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.blog.posts.store'), [
            'user_id' => $admin->id,
            'title' => 'SEO health baseline for launch',
            'slug' => '',
            'excerpt' => 'A concise baseline for launch-day SEO checks.',
            'body' => '<p>Initial content paragraph for testing.</p><p>https://youtu.be/dQw4w9WgXcQ</p><script>alert(1)</script>',
            'status' => 'published',
            'published_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'category_id' => $category->id,
            'tags' => [$tag->id],
            'meta_title' => 'SEO health baseline for launch',
            'meta_description' => 'Launch-day SEO checklist and controls.',
            'meta_keywords' => 'seo, launch, checklist',
            'robots' => 'index,follow',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'SEO health baseline for launch',
            'status' => 'published',
            'category_id' => $category->id,
        ]);

        $post = BlogPost::query()->where('title', 'SEO health baseline for launch')->firstOrFail();

        $this->assertStringNotContainsString('<script>', $post->body);
        $this->assertGreaterThanOrEqual(1, (int) $post->reading_time_minutes);
        $this->assertDatabaseHas('blog_post_tag', [
            'blog_post_id' => $post->id,
            'blog_tag_id' => $tag->id,
        ]);
    }
}
