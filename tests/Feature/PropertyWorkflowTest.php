<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
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
            'name' => 'Prestige Realty',
            'slug' => 'prestige',
            'email' => 'contact@prestige.com',
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->agent = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Sarah Connor',
            'email' => 'sarah@prestige.com',
            'role' => 'agency_admin',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_can_create_property_listing(): void
    {
        $payload = [
            'title' => 'Oceanview Grand Penthouse',
            'property_type' => 'apartment',
            'listing_purpose' => 'sale',
            'price' => 1750000.00,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'carpet_area' => 3200,
            'address' => '100 Ocean Blvd',
            'city' => 'Santa Monica',
            'locality' => 'Beachfront',
            'status' => 'available',
        ];

        $response = $this->actingAs($this->agent)->post(route('admin.properties.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('properties', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Oceanview Grand Penthouse',
            'city' => 'Santa Monica',
            'status' => 'available',
        ]);
    }

    public function test_can_update_property_status(): void
    {
        $property = Property::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Downtown Modern Studio',
            'property_type' => 'apartment',
            'listing_purpose' => 'rent',
            'rent_amount' => 2500.00,
            'city' => 'Los Angeles',
            'locality' => 'Downtown',
            'address' => '555 Spring St',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->agent)->post(route('admin.properties.status', $property->id), [
            'status' => 'reserved',
        ]);

        $response->assertRedirect();
        $this->assertEquals('reserved', $property->fresh()->status);
    }
}
