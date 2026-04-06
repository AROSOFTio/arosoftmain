<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_case_insensitive_email(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this
            ->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'ADMIN@EXAMPLE.COM',
                'password' => 'password',
            ]);

        $response->assertRedirect(route('admin.blog.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_user_is_denied_admin_access_even_with_valid_password(): void
    {
        User::factory()->create([
            'email' => 'staff@example.com',
        ]);

        $response = $this
            ->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'staff@example.com',
                'password' => 'password',
            ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}

