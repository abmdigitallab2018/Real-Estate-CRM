<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Property;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAndReservationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $agent;
    protected Customer $customer;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = SubscriptionPlan::create([
            'name' => 'Agency Pro',
            'slug' => 'pro',
            'price' => 99.00,
            'billing_interval' => 'monthly',
            'max_agents' => 10,
            'max_properties' => 100,
            'max_storage_mb' => 10240,
            'features' => ['all'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Bel Air Properties',
            'slug' => 'belair',
            'email' => 'sales@belair.com',
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->agent = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Agent Clark',
            'email' => 'clark@belair.com',
            'role' => 'agency_admin',
            'password' => bcrypt('password'),
        ]);

        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bruce Wayne',
            'email' => 'bruce@wayne.com',
            'phone' => '111-222-3333',
            'customer_type' => 'buyer',
            'status' => 'active',
        ]);

        $this->property = Property::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Wayne Manor Annex',
            'property_code' => 'PROP-WM01',
            'property_type' => 'bungalow',
            'listing_purpose' => 'sale',
            'price' => 5000000.00,
            'city' => 'Gotham',
            'locality' => 'Crest Hill',
            'address' => '1007 Mountain Drive',
            'status' => 'available',
        ]);
    }

    public function test_can_create_property_reservation(): void
    {
        $payload = [
            'property_id' => $this->property->id,
            'customer_id' => $this->customer->id,
            'agent_id' => $this->agent->id,
            'deal_type' => 'sale',
            'total_amount' => 5000000.00,
            'booking_amount' => 250000.00,
            'status' => 'confirmed',
            'booking_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->agent)->post(route('admin.bookings.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'customer_id' => $this->customer->id,
            'status' => 'confirmed',
        ]);

        // Property status should be updated to reserved
        $this->assertEquals('reserved', $this->property->fresh()->status);
    }

    public function test_prevents_duplicate_active_reservation_on_same_property(): void
    {
        // First booking confirmed
        Booking::create([
            'tenant_id' => $this->tenant->id,
            'booking_code' => 'BKG-TEST-01',
            'property_id' => $this->property->id,
            'customer_id' => $this->customer->id,
            'agent_id' => $this->agent->id,
            'deal_type' => 'sale',
            'total_amount' => 5000000.00,
            'booking_amount' => 250000.00,
            'status' => 'confirmed',
            'booking_date' => now()->toDateString(),
        ]);

        $customer2 = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Diana Prince',
            'email' => 'diana@amazon.com',
            'phone' => '444-555-6666',
            'customer_type' => 'buyer',
            'status' => 'active',
        ]);

        // Attempt second booking for same property
        $payload = [
            'property_id' => $this->property->id,
            'customer_id' => $customer2->id,
            'agent_id' => $this->agent->id,
            'deal_type' => 'sale',
            'total_amount' => 5000000.00,
            'booking_amount' => 250000.00,
            'status' => 'confirmed',
            'booking_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->agent)->post(route('admin.bookings.store'), $payload);
        $response->assertSessionHas('error');
    }
}
