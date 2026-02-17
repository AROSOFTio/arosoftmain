<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_published_blog_post(): void
    {
        $author = User::factory()->create();
        $category = BlogCategory::factory()->create(['name' => 'Engineering', 'slug' => 'engineering']);
        $tag = BlogTag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

        $post = BlogPost::factory()->create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Production hardening checklist',
            'slug' => 'production-hardening-checklist',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $post->tags()->attach($tag->id);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk()
            ->assertSeeText('Production hardening checklist')
            ->assertSeeText('Engineering')
            ->assertSee('application/ld+json', false);
    }
}

