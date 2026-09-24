<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = SubscriptionPlan::create([
            'name' => 'Agency Starter',
            'slug' => 'starter',
            'price' => 49.00,
            'billing_interval' => 'monthly',
            'max_agents' => 5,
            'max_properties' => 50,
            'max_storage_mb' => 2048,
            'features' => ['all'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Skyline Realty',
            'slug' => 'skyline',
            'email' => 'contact@skyline.com',
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Alex',
            'email' => 'alex@skyline.com',
            'role' => 'agency_admin',
            'password' => bcrypt('password'),
        ]);

        $this->agent = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Agent Brian',
            'email' => 'brian@skyline.com',
            'role' => 'agent',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_can_create_and_assign_lead(): void
    {
        $payload = [
            'name' => 'Jessica Alba',
            'email' => 'jessica@example.com',
            'phone' => '123-456-7890',
            'lead_type' => 'buyer',
            'source' => 'website',
            'status' => 'new',
            'priority' => 'high',
            'assigned_agent_id' => $this->agent->id,
            'budget_min' => 700000,
            'budget_max' => 1200000,
            'preferred_location' => 'Beverly Hills',
            'property_type' => 'villa',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.leads.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Jessica Alba',
            'assigned_agent_id' => $this->agent->id,
            'status' => 'new',
        ]);
    }

    public function test_can_convert_lead_to_customer_and_deal(): void
    {
        $this->withoutExceptionHandling();
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Robert Downey',
            'email' => 'robert@marvel.com',
            'phone' => '987-654-3210',
            'lead_type' => 'buyer',
            'source' => 'referrals',
            'status' => 'qualified',
            'priority' => 'high',
            'assigned_agent_id' => $this->agent->id,
            'budget_min' => 2000000,
            'budget_max' => 5000000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.leads.convert', $lead->id), [
            'create_deal' => true,
            'deal_title' => 'Malibu Cliffside Purchase Deal',
            'expected_value' => 3500000,
            'expected_close_date' => now()->addDays(30)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertEquals('converted', $lead->fresh()->status);
        $this->assertDatabaseHas('customers', [
            'tenant_id' => $this->tenant->id,
            'email' => 'robert@marvel.com',
        ]);
        $this->assertDatabaseHas('deals', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Malibu Cliffside Purchase Deal',
        ]);
    }
}
