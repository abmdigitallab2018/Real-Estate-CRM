<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $adminA;
    protected User $adminB;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = SubscriptionPlan::create([
            'name' => 'Pro Agency',
            'slug' => 'pro',
            'price' => 99.00,
            'billing_interval' => 'monthly',
            'max_agents' => 10,
            'max_properties' => 100,
            'max_storage_mb' => 10240,
            'features' => ['all'],
            'is_active' => true,
        ]);

        $this->tenantA = Tenant::create([
            'name' => 'Agency Alpha',
            'slug' => 'alpha',
            'email' => 'info@alpha.com',
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Agency Beta',
            'slug' => 'beta',
            'email' => 'info@beta.com',
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->adminA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Admin Alpha',
            'email' => 'admin@alpha.com',
            'role' => 'agency_admin',
            'password' => bcrypt('password'),
        ]);

        $this->adminB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Admin Beta',
            'email' => 'admin@beta.com',
            'role' => 'agency_admin',
            'password' => bcrypt('password'),
        ]);

        $this->superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@recrm.com',
            'role' => 'super_admin',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_tenant_a_cannot_see_tenant_b_properties(): void
    {
        $propertyB = Property::create([
            'tenant_id' => $this->tenantB->id,
            'title' => 'Beta Luxury Villa',
            'property_code' => 'PROP-B01',
            'property_type' => 'villa',
            'listing_purpose' => 'sale',
            'price' => 1250000.00,
            'city' => 'Austin',
            'locality' => 'Downtown',
            'address' => '456 Beta St',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.properties.index'));
        $response->assertStatus(200);

        // When queried in the context of Tenant A, Tenant B's property is not in results
        $this->actingAs($this->adminA);
        $visibleProperties = Property::all();
        $this->assertFalse($visibleProperties->contains('id', $propertyB->id));
    }

    public function test_tenant_a_cannot_access_tenant_b_lead(): void
    {
        $leadB = Lead::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Secret Buyer',
            'email' => 'buyer@beta.com',
            'phone' => '555-9999',
            'lead_type' => 'buyer',
            'status' => 'new',
            'source' => 'website',
            'budget_min' => 500000,
            'budget_max' => 1000000,
        ]);

        $this->actingAs($this->adminA);
        $this->assertNull(Lead::find($leadB->id));
    }

    public function test_superadmin_can_access_all_tenant_data(): void
    {
        Property::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Alpha Penthouse',
            'property_code' => 'PROP-A01',
            'property_type' => 'apartment',
            'listing_purpose' => 'sale',
            'price' => 850000.00,
            'city' => 'Miami',
            'locality' => 'South Beach',
            'address' => '123 Ocean Dr',
            'status' => 'available',
        ]);

        Property::create([
            'tenant_id' => $this->tenantB->id,
            'title' => 'Beta Loft',
            'property_code' => 'PROP-B02',
            'property_type' => 'apartment',
            'listing_purpose' => 'rent',
            'rent_price' => 4500.00,
            'city' => 'Dallas',
            'locality' => 'Uptown',
            'address' => '789 Elm St',
            'status' => 'available',
        ]);

        $this->actingAs($this->superAdmin);
        $totalProperties = Property::withoutGlobalScope('tenant')->count();
        $this->assertEquals(2, $totalProperties);
    }
}
