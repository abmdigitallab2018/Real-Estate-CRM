<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_home_screen_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_portfolio_home_displays_active_services_and_portfolios(): void
    {
        Service::create([
            'title' => 'Custom Cloud Architecture',
            'description' => 'Designing resilient cloud systems.',
            'icon' => '☁️',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Portfolio::create([
            'title' => 'Logistics Tracking App',
            'category' => 'Web App',
            'description' => 'Fleet monitoring system.',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_contact_inquiry_can_be_submitted(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'subject' => 'Project Consultation',
            'message' => 'Hello Bhavesh, I would love to discuss an enterprise web project.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'subject' => 'Project Consultation',
            'is_read' => false,
        ]);
    }

    public function test_offline_route_can_be_rendered(): void
    {
        $response = $this->get('/offline');

        $response->assertStatus(200);
    }
}
