<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Property;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAndPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Property $property;
    protected User $customerUser;

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
            'name' => 'Coastal Realty',
            'slug' => 'coastal',
            'email' => 'info@coastal.com',
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->property = Property::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Malibu Beachfront Villa',
            'slug' => 'malibu-beachfront-villa',
            'property_code' => 'PROP-MBV',
            'property_type' => 'villa',
            'listing_purpose' => 'sale',
            'price' => 3800000.00,
            'city' => 'Malibu',
            'locality' => 'Pacific Coast',
            'address' => '21000 Pacific Coast Hwy',
            'status' => 'available',
            'is_published' => true,
        ]);

        $this->customerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tony Stark',
            'email' => 'tony@stark.com',
            'role' => 'customer',
            'password' => bcrypt('password'),
        ]);

        Customer::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customerUser->id,
            'name' => 'Tony Stark',
            'email' => 'tony@stark.com',
            'phone' => '123-456-7890',
            'customer_type' => 'buyer',
            'status' => 'active',
        ]);
    }

    public function test_can_view_public_property_index(): void
    {
        $response = $this->get(route('properties.index'));
        $response->assertStatus(200);
    }

    public function test_can_view_public_property_detail(): void
    {
        $response = $this->get(route('properties.show', $this->property->slug));
        $response->assertStatus(200);
    }

    public function test_can_view_public_pricing_page(): void
    {
        $response = $this->get(route('pricing'));
        $response->assertStatus(200);
    }

    public function test_can_submit_public_inquiry(): void
    {
        $payload = [
            'property_id' => $this->property->id,
            'name' => 'Pepper Potts',
            'email' => 'pepper@stark.com',
            'phone' => '800-555-0199',
            'message' => 'I would like to know if this property has private beach access.',
        ];

        $response = $this->post(route('properties.inquiry'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'email' => 'pepper@stark.com',
            'name' => 'Pepper Potts',
        ]);
    }

    public function test_customer_can_access_portal_dashboard(): void
    {
        $response = $this->actingAs($this->customerUser)->get(route('portal.dashboard'));
        $response->assertStatus(200);
    }
}
