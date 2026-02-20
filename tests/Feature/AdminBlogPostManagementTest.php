<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBlogPostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_post_and_attached_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $post = BlogPost::factory()->create([
            'user_id' => $admin->id,
            'featured_image_path' => 'blog/featured/sample-featured.jpg',
            'og_image_path' => 'blog/sample-og.jpg',
        ]);
        $tag = BlogTag::factory()->create();
        $post->tags()->attach($tag->id);

        Storage::disk('public')->put($post->featured_image_path, 'featured-image');
        Storage::disk('public')->put($post->og_image_path, 'og-image');

        $response = $this->actingAs($admin)->delete(route('admin.blog.posts.destroy', $post));

        $response->assertRedirect(route('admin.blog.posts.index'));

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('blog_post_tag', [
            'blog_post_id' => $post->id,
            'blog_tag_id' => $tag->id,
        ]);

        Storage::disk('public')->assertMissing($post->featured_image_path);
        Storage::disk('public')->assertMissing($post->og_image_path);
    }

    public function test_featured_image_is_cropped_to_1200x630_when_post_is_saved(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $image = UploadedFile::fake()->image('cover.jpg', 2000, 1400);

        $response = $this->actingAs($admin)->post(route('admin.blog.posts.store'), [
            'user_id' => $admin->id,
            'title' => 'Image dimensions test post',
            'body' => '<p>Sample body content for image processing.</p>',
            'status' => 'published',
            'featured_image' => $image,
        ]);

        $response->assertRedirect();

        $post = BlogPost::query()->where('title', 'Image dimensions test post')->firstOrFail();

        $this->assertNotNull($post->featured_image_path);
        $this->assertStringEndsWith('.jpg', (string) $post->featured_image_path);
        Storage::disk('public')->assertExists((string) $post->featured_image_path);

        $dimensions = getimagesizefromstring(
            (string) Storage::disk('public')->get((string) $post->featured_image_path)
        );

        $this->assertIsArray($dimensions);
        $this->assertSame(1200, $dimensions[0]);
        $this->assertSame(630, $dimensions[1]);
    }
}
