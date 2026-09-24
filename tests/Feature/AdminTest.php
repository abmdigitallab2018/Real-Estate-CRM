<?php

namespace Tests\Feature;

use App\Models\AboutSetting;
use App\Models\ContactInquiry;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_and_update_service(): void
    {
        $user = User::factory()->create();

        $createResponse = $this->actingAs($user)->post('/admin/services', [
            'title' => 'API Optimization',
            'description' => 'Fast and secure REST APIs.',
            'icon' => '⚡',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $createResponse->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', [
            'title' => 'API Optimization',
            'sort_order' => 2,
        ]);

        $service = Service::first();

        $updateResponse = $this->actingAs($user)->put("/admin/services/{$service->id}", [
            'title' => 'High-Speed API Optimization',
            'description' => 'Upgraded description.',
            'icon' => '🚀',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $updateResponse->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', [
            'title' => 'High-Speed API Optimization',
            'icon' => '🚀',
        ]);
    }

    public function test_admin_can_create_and_delete_portfolio_project(): void
    {
        $user = User::factory()->create();

        $createResponse = $this->actingAs($user)->post('/admin/portfolios', [
            'title' => 'SaaS Dashboard',
            'category' => 'Web App',
            'project_url' => 'https://example.com/demo',
            'description' => 'A complex dashboard application.',
            'is_active' => true,
        ]);

        $createResponse->assertRedirect(route('admin.portfolios.index'));
        $this->assertDatabaseHas('portfolios', [
            'title' => 'SaaS Dashboard',
        ]);

        $project = Portfolio::first();

        $deleteResponse = $this->actingAs($user)->delete("/admin/portfolios/{$project->id}");
        $deleteResponse->assertRedirect(route('admin.portfolios.index'));
        $this->assertDatabaseMissing('portfolios', [
            'id' => $project->id,
        ]);
    }

    public function test_admin_can_update_about_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/admin/about', [
            'hero_tagline' => 'Principal Software Architect & Consultant',
            'bio' => 'Engineering scalable systems for over a decade.',
            'years_experience' => 8,
            'completed_projects' => 60,
        ]);

        $response->assertRedirect(route('admin.about.edit'));
        $this->assertDatabaseHas('about_settings', [
            'hero_tagline' => 'Principal Software Architect & Consultant',
            'years_experience' => 8,
            'completed_projects' => 60,
        ]);
    }

    public function test_admin_can_view_and_delete_contact_inquiry(): void
    {
        $user = User::factory()->create();

        $inquiry = ContactInquiry::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'subject' => 'Hiring Inquiry',
            'message' => 'Are you open for contract positions?',
            'is_read' => false,
        ]);

        // Viewing inquiry marks it as read
        $showResponse = $this->actingAs($user)->get("/admin/contacts/{$inquiry->id}");
        $showResponse->assertStatus(200);
        $this->assertTrue($inquiry->fresh()->is_read);

        // Delete inquiry
        $deleteResponse = $this->actingAs($user)->delete("/admin/contacts/{$inquiry->id}");
        $deleteResponse->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseMissing('contact_inquiries', [
            'id' => $inquiry->id,
        ]);
    }
}
