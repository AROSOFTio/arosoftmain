<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalYearProjectHostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_year_project_hosting_page_loads(): void
    {
        $response = $this->get(route('final-year-project-hosting'));

        $response->assertOk();
        $response->assertSee('Final Year Project Hosting');
        $response->assertSee('UGX 50,000');
        $response->assertSee('UGX 86,000');
    }

    public function test_order_is_saved_in_database_when_checkout_is_not_configured(): void
    {
        $response = $this->post(route('final-year-project-hosting.order.store'), [
            'full_name' => 'Student One',
            'email' => 'student1@example.com',
            'phone' => '+256700000001',
            'institution' => 'KIU',
            'project_title' => 'Clinic Records System',
            'package' => 'hosting_only',
            'notes' => 'Need deployment support.',
        ]);

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location', '');
        $this->assertStringContainsString('/final-year-project-hosting?order=FYP-', $location);

        $this->assertDatabaseHas('final_year_project_orders', [
            'customer_email' => 'student1@example.com',
            'package_key' => 'hosting_only',
            'payment_status' => 'NOT_STARTED',
        ]);
    }
}
