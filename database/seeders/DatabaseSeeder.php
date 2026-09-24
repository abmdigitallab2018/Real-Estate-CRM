<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\BuyerPreference;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyImage;
use App\Models\PropertyMatch;
use App\Models\SiteVisit;
use App\Models\SubscriptionPlan;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Subscription Plans
        $starterPlan = SubscriptionPlan::create([
            'name' => 'Starter Agency',
            'slug' => 'starter',
            'description' => 'For independent brokers and small teams getting started.',
            'price' => 49.00,
            'billing_interval' => 'monthly',
            'max_agents' => 3,
            'max_properties' => 30,
            'max_storage_mb' => 2048,
            'features' => [
                'Property Listings',
                'Lead Management',
                'Basic Pipeline',
                'Email Notifications',
            ],
            'is_active' => true,
        ]);

        $proPlan = SubscriptionPlan::create([
            'name' => 'Professional Real Estate',
            'slug' => 'pro',
            'description' => 'For growing real estate agencies and boutique brokerages.',
            'price' => 129.00,
            'billing_interval' => 'monthly',
            'max_agents' => 15,
            'max_properties' => 250,
            'max_storage_mb' => 10240,
            'features' => [
                'All Starter Features',
                'Advanced Matching Engine',
                'Kanban Pipeline & Deals',
                'Commission Tracking & Payouts',
                'Site Visit Calendar',
                'Customer Portal Access',
                'Multi-Branch Support',
            ],
            'is_active' => true,
        ]);

        $enterprisePlan = SubscriptionPlan::create([
            'name' => 'Enterprise Builder & Brokerage',
            'slug' => 'enterprise',
            'description' => 'For large developers, multi-city agencies and corporate brokerages.',
            'price' => 299.00,
            'billing_interval' => 'monthly',
            'max_agents' => 100,
            'max_properties' => 2000,
            'max_storage_mb' => 51200,
            'features' => [
                'All Pro Features',
                'Custom Domain & Branding',
                'Dedicated Account Manager',
                'Unlimited Branch Offices',
                'API & Webhook Integrations',
                'Audit Trail & SLA Guarantee',
            ],
            'is_active' => true,
        ]);

        // 2. Tenants (Agencies)
        $tenant1 = Tenant::create([
            'name' => 'Skyline Realty Group',
            'slug' => 'skyline-realty',
            'domain' => 'skyline.recrm.test',
            'email' => 'contact@skylinerealty.com',
            'phone' => '+1 (555) 234-5678',
            'address' => '742 Evergreen Terrace, Suite 400',
            'city' => 'New York',
            'country' => 'United States',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'logo_url' => null,
            'status' => 'active',
            'subscription_plan_id' => $proPlan->id,
            'trial_ends_at' => null,
            'settings' => [
                'primary_color' => '#4F46E5',
                'timezone' => 'America/New_York',
                'site_visit_reminder_hours' => 24,
                'enable_customer_portal' => true,
                'allow_overlapping_visits' => false,
            ],
        ]);

        $tenant2 = Tenant::create([
            'name' => 'Apex Commercial & Luxury Estates',
            'slug' => 'apex-estates',
            'domain' => 'apex.recrm.test',
            'email' => 'info@apexestates.com',
            'phone' => '+1 (555) 876-5432',
            'address' => '100 Financial Center Blvd, 18th Floor',
            'city' => 'Chicago',
            'country' => 'United States',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'logo_url' => null,
            'status' => 'active',
            'subscription_plan_id' => $enterprisePlan->id,
            'trial_ends_at' => null,
            'settings' => [
                'primary_color' => '#0F766E',
                'timezone' => 'America/Chicago',
            ],
        ]);

        // 3. Branches for Tenant 1
        $branchDowntown = Branch::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Skyline Downtown Central HQ',
            'code' => 'SKY-HQ',
            'phone' => '+1 (555) 234-5678',
            'email' => 'downtown@skylinerealty.com',
            'address' => '742 Evergreen Terrace, Suite 400',
            'city' => 'New York',
            'is_main' => true,
            'status' => 'active',
        ]);

        $branchWest = Branch::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Skyline West Suburbs Office',
            'code' => 'SKY-WEST',
            'phone' => '+1 (555) 345-6789',
            'email' => 'west@skylinerealty.com',
            'address' => '120 Ocean Parkway, Suite 12',
            'city' => 'Jersey City',
            'is_main' => false,
            'status' => 'active',
        ]);

        // 4. Users (All Roles)
        // Super Admin
        $superAdmin = User::create([
            'tenant_id' => null,
            'branch_id' => null,
            'name' => 'Global Super Admin',
            'email' => 'superadmin@recrm.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
            'phone' => '+1 (555) 000-0001',
            'email_verified_at' => now(),
        ]);

        // Also create abmlab superadmin for user convenience
        User::create([
            'tenant_id' => null,
            'branch_id' => null,
            'name' => 'ABM Digital Lab Admin',
            'email' => 'abmlab@abmlab.com',
            'password' => Hash::make('abmlab@1997'),
            'role' => 'super_admin',
            'status' => 'active',
            'phone' => '+1 (555) 000-0002',
            'email_verified_at' => now(),
        ]);

        // Agency Admin
        $agencyAdmin = User::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'name' => 'Marcus Vance',
            'email' => 'admin@skylinerealty.com',
            'password' => Hash::make('password'),
            'role' => 'agency_admin',
            'status' => 'active',
            'phone' => '+1 (555) 111-2222',
            'license_number' => 'RE-NY-98214',
            'commission_rate' => 3.00,
            'specialization' => 'Agency Management & Luxury Estates',
            'target_amount' => 15000000.00,
            'email_verified_at' => now(),
        ]);

        // Sales Manager
        $salesManager = User::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'name' => 'Elena Rostova',
            'email' => 'manager@skylinerealty.com',
            'password' => Hash::make('password'),
            'role' => 'sales_manager',
            'status' => 'active',
            'phone' => '+1 (555) 222-3333',
            'license_number' => 'RE-NY-84512',
            'commission_rate' => 2.50,
            'specialization' => 'High-end Condos & Team Leadership',
            'target_amount' => 8000000.00,
            'email_verified_at' => now(),
        ]);

        // Real Estate Agent 1
        $agent1 = User::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'name' => 'David Miller',
            'email' => 'agent@skylinerealty.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'status' => 'active',
            'phone' => '+1 (555) 333-4444',
            'license_number' => 'RE-NY-61209',
            'commission_rate' => 2.00,
            'specialization' => 'Downtown Penthouses & Lofts',
            'target_amount' => 4500000.00,
            'email_verified_at' => now(),
        ]);

        // Real Estate Agent 2
        $agent2 = User::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchWest->id,
            'name' => 'Sarah Jenkins',
            'email' => 'sarah.agent@skylinerealty.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'status' => 'active',
            'phone' => '+1 (555) 444-5555',
            'license_number' => 'RE-NJ-74193',
            'commission_rate' => 2.00,
            'specialization' => 'Suburban Villas & Family Homes',
            'target_amount' => 3500000.00,
            'email_verified_at' => now(),
        ]);

        // Property Manager
        $propertyManager = User::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'name' => 'Oliver Chen',
            'email' => 'propertymanager@skylinerealty.com',
            'password' => Hash::make('password'),
            'role' => 'property_manager',
            'status' => 'active',
            'phone' => '+1 (555) 555-6666',
            'license_number' => 'PM-NY-30192',
            'specialization' => 'Asset Verification & Tenant Management',
            'email_verified_at' => now(),
        ]);

        // Accountant
        $accountant = User::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'name' => 'Rachel Sterling',
            'email' => 'accountant@skylinerealty.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'status' => 'active',
            'phone' => '+1 (555) 666-7777',
            'specialization' => 'Real Estate Brokerage Accounting & Escrow',
            'email_verified_at' => now(),
        ]);

        // Customer User (Buyer Portal)
        $customerUser = User::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => null,
            'name' => 'Jonathan Hayes',
            'email' => 'buyer@client.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
            'phone' => '+1 (555) 777-8888',
            'email_verified_at' => now(),
        ]);

        // Tenant 2 Admin
        User::create([
            'tenant_id' => $tenant2->id,
            'branch_id' => null,
            'name' => 'Arthur Pendelton',
            'email' => 'admin@apexestates.com',
            'password' => Hash::make('password'),
            'role' => 'agency_admin',
            'status' => 'active',
            'phone' => '+1 (555) 999-0000',
            'license_number' => 'RE-IL-54128',
            'commission_rate' => 3.00,
            'email_verified_at' => now(),
        ]);

        // 5. Pipelines & Stages for Tenant 1
        $salesPipeline = Pipeline::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Residential Sales Pipeline',
            'is_default' => true,
        ]);

        $stage1 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'New Inquiry', 'slug' => 'new-inquiry', 'order' => 1, 'probability' => 10, 'color' => '#64748B']);
        $stage2 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'Qualified Buyer', 'slug' => 'qualified', 'order' => 2, 'probability' => 25, 'color' => '#3B82F6']);
        $stage3 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'Property Shortlisted', 'slug' => 'shortlisted', 'order' => 3, 'probability' => 40, 'color' => '#6366F1']);
        $stage4 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'Site Visit Done', 'slug' => 'site-visit', 'order' => 4, 'probability' => 60, 'color' => '#8B5CF6']);
        $stage5 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'Offer & Negotiation', 'slug' => 'negotiation', 'order' => 5, 'probability' => 75, 'color' => '#F59E0B']);
        $stage6 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'Under Booking / Escrow', 'slug' => 'booking', 'order' => 6, 'probability' => 90, 'color' => '#10B981']);
        $stage7 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'Closed Won', 'slug' => 'closed-won', 'order' => 7, 'probability' => 100, 'color' => '#059669', 'is_won' => true]);
        $stage8 = PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $salesPipeline->id, 'name' => 'Closed Lost', 'slug' => 'closed-lost', 'order' => 8, 'probability' => 0, 'color' => '#EF4444', 'is_lost' => true]);

        // Commercial Leasing Pipeline
        $leasingPipeline = Pipeline::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Commercial Leasing Pipeline',
            'is_default' => false,
        ]);
        PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $leasingPipeline->id, 'name' => 'Lead Received', 'slug' => 'lease-inquiry', 'order' => 1, 'probability' => 15, 'color' => '#64748B']);
        PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $leasingPipeline->id, 'name' => 'Space Inspection', 'slug' => 'space-inspection', 'order' => 2, 'probability' => 45, 'color' => '#3B82F6']);
        PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $leasingPipeline->id, 'name' => 'Lease Terms Discussion', 'slug' => 'terms-discussion', 'order' => 3, 'probability' => 70, 'color' => '#F59E0B']);
        PipelineStage::create(['tenant_id' => $tenant1->id, 'pipeline_id' => $leasingPipeline->id, 'name' => 'Agreement Signed', 'slug' => 'lease-signed', 'order' => 4, 'probability' => 100, 'color' => '#059669', 'is_won' => true]);

        // 6. Commission Rules
        CommissionRule::create([
            'tenant_id' => $tenant1->id,
            'title' => 'Standard Residential Sale Commission',
            'property_type' => 'all',
            'deal_type' => 'sale',
            'commission_type' => 'percentage',
            'rate' => 2.00,
            'is_active' => true,
        ]);

        CommissionRule::create([
            'tenant_id' => $tenant1->id,
            'title' => 'Luxury Villa Special Commission',
            'property_type' => 'villa',
            'deal_type' => 'sale',
            'commission_type' => 'percentage',
            'rate' => 2.50,
            'is_active' => true,
        ]);

        CommissionRule::create([
            'tenant_id' => $tenant1->id,
            'title' => 'Commercial Rental Commission',
            'property_type' => 'commercial_office',
            'deal_type' => 'rent',
            'commission_type' => 'percentage',
            'rate' => 8.33, // approx 1 month rent
            'is_active' => true,
        ]);

        // 7. Customers & Owners
        $customer1 = Customer::create([
            'tenant_id' => $tenant1->id,
            'user_id' => $customerUser->id,
            'name' => 'Jonathan Hayes',
            'email' => 'buyer@client.com',
            'phone' => '+1 (555) 777-8888',
            'customer_type' => 'buyer',
            'company_name' => 'Hayes Capital Partners',
            'address' => '350 5th Avenue, Suite 2100',
            'city' => 'New York',
            'assigned_agent_id' => $agent1->id,
            'source' => 'Website Inquiry',
            'notes' => 'High-net-worth tech investor looking for luxury 3-4 BHK in Manhattan with panoramic skyline views.',
            'status' => 'active',
        ]);

        BuyerPreference::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => $customer1->id,
            'purpose' => 'sale',
            'property_types' => ['apartment', 'flat', 'villa'],
            'min_budget' => 1200000.00,
            'max_budget' => 2500000.00,
            'preferred_locations' => ['Downtown Manhattan', 'Tribeca', 'Chelsea', 'SoHo'],
            'min_bedrooms' => 3,
            'min_bathrooms' => 2,
            'min_area' => 1800.00,
            'possession_timeline' => 'immediate',
            'furnishing' => 'semi_furnished',
        ]);

        $owner1 = Customer::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Lord Bradley Sterling',
            'email' => 'bradley.sterling@luxuryowners.com',
            'phone' => '+1 (555) 901-2345',
            'customer_type' => 'seller',
            'company_name' => 'Sterling Estates Trust',
            'address' => '12 Park Avenue',
            'city' => 'New York',
            'assigned_agent_id' => $agent1->id,
            'source' => 'Referral',
            'notes' => 'Owns multiple prime penthouses and luxury townhouses across Manhattan.',
            'status' => 'active',
        ]);

        $customer2 = Customer::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Sophia & Liam Martinez',
            'email' => 'sophia.martinez@techcorp.io',
            'phone' => '+1 (555) 432-8765',
            'customer_type' => 'buyer',
            'city' => 'Jersey City',
            'assigned_agent_id' => $agent2->id,
            'source' => 'Social Media',
            'notes' => 'Family looking for spacious 4BHK Villa with private yard and good school district.',
            'status' => 'active',
        ]);

        BuyerPreference::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => $customer2->id,
            'purpose' => 'sale',
            'property_types' => ['villa', 'bungalow'],
            'min_budget' => 850000.00,
            'max_budget' => 1500000.00,
            'preferred_locations' => ['West Suburbs', 'Alpine', 'Short Hills'],
            'min_bedrooms' => 4,
            'min_bathrooms' => 3,
            'min_area' => 2800.00,
            'possession_timeline' => 'immediate',
            'furnishing' => 'unfurnished',
        ]);

        $customer3 = Customer::create([
            'tenant_id' => $tenant1->id,
            'name' => 'VenturePeak Coworking LLC',
            'email' => 'facilities@venturepeak.com',
            'phone' => '+1 (555) 321-9876',
            'customer_type' => 'tenant',
            'company_name' => 'VenturePeak Hubs',
            'city' => 'New York',
            'assigned_agent_id' => $agent1->id,
            'source' => 'Property Portal',
            'notes' => 'Needs Grade-A commercial office space 5,000 - 10,000 sqft for expansion.',
            'status' => 'active',
        ]);

        // 8. Properties
        $prop1 = Property::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'owner_id' => $owner1->id,
            'listing_agent_id' => $agent1->id,
            'property_code' => 'PROP-1001',
            'title' => 'Skyline Grand Penthouse overlooking Hudson River',
            'slug' => 'skyline-grand-penthouse-hudson-river',
            'description' => 'Breathtaking 3-bedroom, 3.5-bathroom penthouse with floor-to-ceiling glass windows offering panoramic views of the Hudson River and city skyline. Includes private elevator entrance, custom Italian kitchen, spa-like primary bathroom, and wrap-around terrace.',
            'property_type' => 'apartment',
            'listing_purpose' => 'sale',
            'status' => 'available',
            'price' => 1850000.00,
            'maintenance_charges' => 1250.00,
            'is_negotiable' => true,
            'address' => '450 West 42nd Street, Unit PH-B',
            'locality' => 'Midtown West / Hudson Yards',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10036',
            'latitude' => 40.760120,
            'longitude' => -73.996150,
            'bedrooms' => 3,
            'bathrooms' => 4,
            'balconies' => 2,
            'carpet_area' => 2450.00,
            'built_up_area' => 2800.00,
            'area_unit' => 'sqft',
            'furnishing' => 'semi_furnished',
            'floor' => 48,
            'total_floors' => 50,
            'parking_spaces' => 2,
            'construction_status' => 'ready_to_move',
            'year_built' => 2023,
            'available_from' => now()->toDateString(),
            'is_featured' => true,
            'is_published' => true,
            'views_count' => 1420,
            'featured_image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
        ]);

        foreach (['Swimming Pool', 'Gym & Fitness Center', 'Clubhouse', '24/7 Concierge & Security', 'Valet Parking', 'High-Speed Elevators', 'Rooftop Lounge'] as $amenity) {
            PropertyAmenity::create(['tenant_id' => $tenant1->id, 'property_id' => $prop1->id, 'name' => $amenity]);
        }

        PropertyImage::create(['tenant_id' => $tenant1->id, 'property_id' => $prop1->id, 'image_path' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Luxury Living Room', 'is_primary' => true, 'sort_order' => 1]);
        PropertyImage::create(['tenant_id' => $tenant1->id, 'property_id' => $prop1->id, 'image_path' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Hudson River Terrace View', 'is_primary' => false, 'sort_order' => 2]);
        PropertyImage::create(['tenant_id' => $tenant1->id, 'property_id' => $prop1->id, 'image_path' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Master Bedroom Suite', 'is_primary' => false, 'sort_order' => 3]);

        $prop2 = Property::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchWest->id,
            'owner_id' => $owner1->id,
            'listing_agent_id' => $agent2->id,
            'property_code' => 'PROP-1002',
            'title' => 'Serene 4BHK Meadowbrook Villa with Heated Pool',
            'slug' => 'serene-4bhk-meadowbrook-villa-heated-pool',
            'description' => 'Exquisite modern Mediterranean villa featuring 4 lavish bedrooms, 4.5 baths, landscaped gardens, heated swimming pool, 3-car garage, and chef kitchen with Viking appliances. Situated in a prestigious gated community.',
            'property_type' => 'villa',
            'listing_purpose' => 'sale',
            'status' => 'under_negotiation',
            'price' => 1350000.00,
            'maintenance_charges' => 450.00,
            'is_negotiable' => true,
            'address' => '88 Whispering Pines Way',
            'locality' => 'Alpine Estates',
            'city' => 'Jersey City',
            'state' => 'NJ',
            'zip_code' => '07620',
            'latitude' => 40.728157,
            'longitude' => -74.077642,
            'bedrooms' => 4,
            'bathrooms' => 5,
            'balconies' => 3,
            'carpet_area' => 3800.00,
            'built_up_area' => 4500.00,
            'area_unit' => 'sqft',
            'furnishing' => 'fully_furnished',
            'floor' => 1,
            'total_floors' => 2,
            'parking_spaces' => 3,
            'construction_status' => 'ready_to_move',
            'year_built' => 2022,
            'available_from' => now()->toDateString(),
            'is_featured' => true,
            'is_published' => true,
            'views_count' => 980,
            'featured_image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
        ]);

        foreach (['Private Swimming Pool', 'Landscaped Garden', 'Smart Home Automation', 'Solar Power Backup', 'CCTV Security', 'Gated Community'] as $amenity) {
            PropertyAmenity::create(['tenant_id' => $tenant1->id, 'property_id' => $prop2->id, 'name' => $amenity]);
        }
        PropertyImage::create(['tenant_id' => $tenant1->id, 'property_id' => $prop2->id, 'image_path' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Villa Exterior & Pool', 'is_primary' => true, 'sort_order' => 1]);

        $prop3 = Property::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'owner_id' => $owner1->id,
            'listing_agent_id' => $agent1->id,
            'property_code' => 'PROP-1003',
            'title' => 'Grade-A Commercial Office Floor at Nexus Tower',
            'slug' => 'grade-a-commercial-office-nexus-tower',
            'description' => 'Fully furnished commercial corporate floor with 80 workstations, 4 executive director cabins, 3 conference rooms with video conferencing facilities, cafeteria, server room, and LEED Gold certified energy systems.',
            'property_type' => 'commercial_office',
            'listing_purpose' => 'rent',
            'status' => 'available',
            'price' => 18000.00,
            'rent_amount' => 18000.00,
            'security_deposit' => 54000.00,
            'maintenance_charges' => 2200.00,
            'is_negotiable' => false,
            'address' => '120 Broadway, 14th Floor',
            'locality' => 'Financial District',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10271',
            'latitude' => 40.708890,
            'longitude' => -74.010830,
            'bedrooms' => 0,
            'bathrooms' => 4,
            'carpet_area' => 6200.00,
            'built_up_area' => 7000.00,
            'area_unit' => 'sqft',
            'furnishing' => 'fully_furnished',
            'floor' => 14,
            'total_floors' => 35,
            'parking_spaces' => 8,
            'construction_status' => 'ready_to_move',
            'year_built' => 2021,
            'available_from' => now()->toDateString(),
            'is_featured' => true,
            'is_published' => true,
            'views_count' => 650,
            'featured_image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
        ]);
        foreach (['24/7 Access', 'Fiber Optic High-Speed Internet', 'Central Air Conditioning', 'Power Backup Generator', 'Fire Suppression System', 'Cafeteria'] as $amenity) {
            PropertyAmenity::create(['tenant_id' => $tenant1->id, 'property_id' => $prop3->id, 'name' => $amenity]);
        }
        PropertyImage::create(['tenant_id' => $tenant1->id, 'property_id' => $prop3->id, 'image_path' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Open Workstation Floor', 'is_primary' => true, 'sort_order' => 1]);

        $prop4 = Property::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'owner_id' => $owner1->id,
            'listing_agent_id' => $agent1->id,
            'property_code' => 'PROP-1004',
            'title' => 'Chic 2BHK Designer Loft in Tribeca',
            'slug' => 'chic-2bhk-designer-loft-tribeca',
            'description' => 'Cast-iron building loft with 12ft beamed ceilings, exposed brick walls, oversized sash windows, open stainless steel kitchen, and prime Tribeca cobblestone street location.',
            'property_type' => 'flat',
            'listing_purpose' => 'sale',
            'status' => 'reserved', // Reserved test property
            'price' => 1150000.00,
            'maintenance_charges' => 850.00,
            'is_negotiable' => true,
            'address' => '72 Franklin Street, Apt 3A',
            'locality' => 'Tribeca',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10013',
            'latitude' => 40.717520,
            'longitude' => -74.004180,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'balconies' => 1,
            'carpet_area' => 1400.00,
            'built_up_area' => 1650.00,
            'area_unit' => 'sqft',
            'furnishing' => 'semi_furnished',
            'floor' => 3,
            'total_floors' => 6,
            'parking_spaces' => 1,
            'construction_status' => 'ready_to_move',
            'year_built' => 2019,
            'available_from' => now()->toDateString(),
            'is_featured' => false,
            'is_published' => true,
            'views_count' => 1120,
            'featured_image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
        ]);
        foreach (['Exposed Brick', 'Keyed Elevator', 'Storage Room', 'Intercom'] as $amenity) {
            PropertyAmenity::create(['tenant_id' => $tenant1->id, 'property_id' => $prop4->id, 'name' => $amenity]);
        }
        PropertyImage::create(['tenant_id' => $tenant1->id, 'property_id' => $prop4->id, 'image_path' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Tribeca Loft Interior', 'is_primary' => true, 'sort_order' => 1]);

        $prop5 = Property::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchWest->id,
            'owner_id' => $owner1->id,
            'listing_agent_id' => $agent2->id,
            'property_code' => 'PROP-1005',
            'title' => 'Prime Corner Retail Shop in High-Street Galleria',
            'slug' => 'prime-corner-retail-shop-galleria',
            'description' => 'High footfall retail storefront with double-height ceiling, 35ft frontage, excellent street visibility, and dedicated loading dock access.',
            'property_type' => 'shop',
            'listing_purpose' => 'lease',
            'status' => 'available',
            'price' => 7500.00,
            'rent_amount' => 7500.00,
            'security_deposit' => 22500.00,
            'maintenance_charges' => 600.00,
            'is_negotiable' => true,
            'address' => '420 Washington Boulevard',
            'locality' => 'Newport Mall District',
            'city' => 'Jersey City',
            'state' => 'NJ',
            'zip_code' => '07310',
            'bedrooms' => 0,
            'bathrooms' => 1,
            'carpet_area' => 1100.00,
            'built_up_area' => 1300.00,
            'area_unit' => 'sqft',
            'furnishing' => 'unfurnished',
            'floor' => 1,
            'total_floors' => 3,
            'parking_spaces' => 2,
            'construction_status' => 'ready_to_move',
            'available_from' => now()->toDateString(),
            'is_featured' => false,
            'is_published' => true,
            'views_count' => 340,
            'featured_image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
        ]);
        PropertyImage::create(['tenant_id' => $tenant1->id, 'property_id' => $prop5->id, 'image_path' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Shop Frontage', 'is_primary' => true, 'sort_order' => 1]);

        // 9. Leads
        $lead1 = Lead::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => $customer1->id,
            'branch_id' => $branchDowntown->id,
            'assigned_agent_id' => $agent1->id,
            'property_id' => $prop1->id,
            'lead_type' => 'buyer',
            'name' => 'Jonathan Hayes',
            'email' => 'buyer@client.com',
            'phone' => '+1 (555) 777-8888',
            'source' => 'website',
            'status' => 'negotiation',
            'priority' => 'urgent',
            'score' => 92,
            'budget_min' => 1500000.00,
            'budget_max' => 2000000.00,
            'preferred_location' => 'Midtown West / Hudson Yards',
            'property_type' => 'apartment',
            'requirements' => 'Needs high floor penthouse with river view and private parking.',
            'tags' => ['VIP', 'Pre-Approved', 'High Budget'],
        ]);

        $lead2 = Lead::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => $customer2->id,
            'branch_id' => $branchWest->id,
            'assigned_agent_id' => $agent2->id,
            'property_id' => $prop2->id,
            'lead_type' => 'buyer',
            'name' => 'Sophia & Liam Martinez',
            'email' => 'sophia.martinez@techcorp.io',
            'phone' => '+1 (555) 432-8765',
            'source' => 'social_media',
            'status' => 'site_visit_scheduled',
            'priority' => 'high',
            'score' => 78,
            'budget_min' => 1000000.00,
            'budget_max' => 1400000.00,
            'preferred_location' => 'Alpine Estates / West Suburbs',
            'property_type' => 'villa',
            'requirements' => 'Gated community villa with swimming pool and large backyard.',
            'tags' => ['Family', 'Ready to Buy'],
        ]);

        $lead3 = Lead::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => null,
            'branch_id' => $branchDowntown->id,
            'assigned_agent_id' => $agent1->id,
            'property_id' => null,
            'lead_type' => 'buyer',
            'name' => 'Alexandria Wong',
            'email' => 'a.wong@meridian.com',
            'phone' => '+1 (555) 890-1234',
            'source' => 'property_portals',
            'status' => 'qualified',
            'priority' => 'medium',
            'score' => 65,
            'budget_min' => 900000.00,
            'budget_max' => 1250000.00,
            'preferred_location' => 'Tribeca / SoHo',
            'property_type' => 'flat',
            'requirements' => 'Looking for 2BHK loft close to transit.',
            'tags' => ['First Time Buyer'],
        ]);

        $lead4 = Lead::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => null,
            'branch_id' => $branchDowntown->id,
            'assigned_agent_id' => $agent1->id,
            'property_id' => $prop3->id,
            'lead_type' => 'renter',
            'name' => 'Daniel Kim',
            'email' => 'dkim@startuphub.co',
            'phone' => '+1 (555) 678-9012',
            'source' => 'referrals',
            'status' => 'new',
            'priority' => 'medium',
            'score' => 50,
            'budget_min' => 15000.00,
            'budget_max' => 20000.00,
            'preferred_location' => 'Financial District',
            'property_type' => 'commercial_office',
            'requirements' => 'Needs commercial floor for 50-80 employees starting next month.',
            'tags' => ['Corporate Lease'],
        ]);

        // 10. Property Matches
        PropertyMatch::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => $customer1->id,
            'lead_id' => $lead1->id,
            'property_id' => $prop1->id,
            'match_score' => 95,
            'status' => 'shortlisted',
            'shared_at' => now()->subDays(3),
            'customer_feedback' => 'Loved the terrace and views. Ready to submit an offer.',
        ]);

        PropertyMatch::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => $customer2->id,
            'lead_id' => $lead2->id,
            'property_id' => $prop2->id,
            'match_score' => 90,
            'status' => 'suggested',
            'shared_at' => now()->subDays(1),
            'customer_feedback' => 'Scheduled visit for this weekend.',
        ]);

        // 11. Deals
        $deal1 = Deal::create([
            'tenant_id' => $tenant1->id,
            'pipeline_id' => $salesPipeline->id,
            'stage_id' => $stage5->id, // Negotiation
            'customer_id' => $customer1->id,
            'lead_id' => $lead1->id,
            'property_id' => $prop1->id,
            'assigned_agent_id' => $agent1->id,
            'deal_code' => 'DEAL-2026-001',
            'title' => 'Hayes - Hudson Penthouse Purchase',
            'deal_type' => 'sale',
            'expected_value' => 1800000.00,
            'actual_value' => null,
            'probability' => 75,
            'expected_close_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Buyer offered $1,800,000 against listing of $1,850,000. Seller willing to counter at $1,825,000 with furniture package included.',
        ]);

        $deal2 = Deal::create([
            'tenant_id' => $tenant1->id,
            'pipeline_id' => $salesPipeline->id,
            'stage_id' => $stage4->id, // Site Visit Done
            'customer_id' => $customer2->id,
            'lead_id' => $lead2->id,
            'property_id' => $prop2->id,
            'assigned_agent_id' => $agent2->id,
            'deal_code' => 'DEAL-2026-002',
            'title' => 'Martinez - Meadowbrook Villa Sale',
            'deal_type' => 'sale',
            'expected_value' => 1350000.00,
            'probability' => 60,
            'expected_close_date' => now()->addDays(25)->toDateString(),
            'notes' => 'Clients are visiting again with architect before formal offer.',
        ]);

        $deal3 = Deal::create([
            'tenant_id' => $tenant1->id,
            'pipeline_id' => $salesPipeline->id,
            'stage_id' => $stage6->id, // Under Booking
            'customer_id' => $customer1->id,
            'property_id' => $prop4->id,
            'assigned_agent_id' => $agent1->id,
            'deal_code' => 'DEAL-2026-003',
            'title' => 'Franklin St Tribeca Loft Reservation',
            'deal_type' => 'sale',
            'expected_value' => 1150000.00,
            'probability' => 90,
            'expected_close_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Token booking received. Title search in progress.',
        ]);

        // 12. Bookings (Demonstrating Property Reservation)
        $booking1 = Booking::create([
            'tenant_id' => $tenant1->id,
            'property_id' => $prop4->id,
            'customer_id' => $customer1->id,
            'agent_id' => $agent1->id,
            'deal_id' => $deal3->id,
            'booking_number' => 'BKG-2026-0001',
            'booking_date' => now()->subDays(2)->toDateString(),
            'expiry_date' => now()->addDays(12)->toDateString(),
            'total_amount' => 1150000.00,
            'booking_amount' => 50000.00,
            'paid_amount' => 50000.00,
            'balance_amount' => 1100000.00,
            'status' => 'confirmed',
            'payment_status' => 'partially_paid',
            'notes' => 'Booking token of $50,000 deposited in escrow account.',
        ]);

        // 13. Payments
        $payment1 = Payment::create([
            'tenant_id' => $tenant1->id,
            'booking_id' => $booking1->id,
            'deal_id' => $deal3->id,
            'customer_id' => $customer1->id,
            'payment_reference' => 'PAY-2026-0001',
            'amount' => 50000.00,
            'payment_type' => 'booking_token',
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'WIRE-NY-9823412',
            'payment_date' => now()->subDays(2)->toDateString(),
            'receipt_number' => 'RCP-2026-001',
            'status' => 'completed',
            'notes' => 'Escrow deposit via Chase wire transfer.',
        ]);

        // 14. Commissions
        Commission::create([
            'tenant_id' => $tenant1->id,
            'agent_id' => $agent1->id,
            'deal_id' => $deal3->id,
            'booking_id' => $booking1->id,
            'property_id' => $prop4->id,
            'commission_type' => 'percentage',
            'commission_rate' => 2.00,
            'base_amount' => 1150000.00,
            'commission_amount' => 23000.00,
            'status' => 'approved',
            'approved_by' => $agencyAdmin->id,
            'approved_at' => now()->subDay(),
            'payout_notes' => 'Scheduled for release upon deed conveyance.',
        ]);

        // 15. Site Visits
        SiteVisit::create([
            'tenant_id' => $tenant1->id,
            'property_id' => $prop1->id,
            'customer_id' => $customer1->id,
            'assigned_agent_id' => $agent1->id,
            'lead_id' => $lead1->id,
            'deal_id' => $deal1->id,
            'visit_code' => 'VST-2026-001',
            'scheduled_date' => now()->toDateString(), // Today!
            'scheduled_time' => '15:30:00',
            'status' => 'confirmed',
            'interest_level' => 'very_high',
            'customer_feedback' => 'Wants to inspect building mechanicals and parking bay layout.',
            'agent_notes' => 'Bring HOA bylaws document and floor plans to the showing.',
            'next_action' => 'Draft counter-offer after tour',
            'reminder_sent' => true,
        ]);

        SiteVisit::create([
            'tenant_id' => $tenant1->id,
            'property_id' => $prop2->id,
            'customer_id' => $customer2->id,
            'assigned_agent_id' => $agent2->id,
            'lead_id' => $lead2->id,
            'deal_id' => $deal2->id,
            'visit_code' => 'VST-2026-002',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '11:00:00',
            'status' => 'scheduled',
            'interest_level' => 'high',
            'customer_feedback' => null,
            'agent_notes' => 'Second walkthrough with family and children.',
            'next_action' => 'Present community amenities guide',
            'reminder_sent' => false,
        ]);

        SiteVisit::create([
            'tenant_id' => $tenant1->id,
            'property_id' => $prop3->id,
            'customer_id' => $customer3->id,
            'assigned_agent_id' => $agent1->id,
            'lead_id' => $lead4->id,
            'visit_code' => 'VST-2026-003',
            'scheduled_date' => now()->subDays(3)->toDateString(),
            'scheduled_time' => '14:00:00',
            'status' => 'completed',
            'interest_level' => 'medium',
            'customer_feedback' => 'Liked the layout, requested test-fit floor plan for 75 desks.',
            'agent_notes' => 'Follow up with Nexus Tower building manager regarding HVAC modifications.',
            'next_action' => 'Send test fit blueprint',
            'reminder_sent' => true,
        ]);

        // 16. Tasks & Follow-ups
        Task::create([
            'tenant_id' => $tenant1->id,
            'assigned_to' => $agent1->id,
            'created_by' => $salesManager->id,
            'customer_id' => $customer1->id,
            'lead_id' => $lead1->id,
            'property_id' => $prop1->id,
            'deal_id' => $deal1->id,
            'activity_type' => 'call',
            'subject' => 'Call Jonathan Hayes regarding seller counter-offer',
            'description' => 'Discuss $1.825M counter-offer and inclusion of Italian chandelier & dining table set.',
            'due_date' => now()->toDateString(), // Today!
            'due_time' => '17:00:00',
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        Task::create([
            'tenant_id' => $tenant1->id,
            'assigned_to' => $agent2->id,
            'created_by' => $agencyAdmin->id,
            'customer_id' => $customer2->id,
            'lead_id' => $lead2->id,
            'property_id' => $prop2->id,
            'deal_id' => $deal2->id,
            'activity_type' => 'document_prep',
            'subject' => 'Prepare HOA documents and survey map for Martinez showing',
            'description' => 'Assemble recent utility statements and pool inspection report.',
            'due_date' => now()->addDay()->toDateString(),
            'due_time' => '10:00:00',
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        Task::create([
            'tenant_id' => $tenant1->id,
            'assigned_to' => $agent1->id,
            'created_by' => $agent1->id,
            'customer_id' => null,
            'lead_id' => $lead3->id,
            'property_id' => null,
            'activity_type' => 'follow_up',
            'subject' => 'Send Tribeca loft listings to Alexandria Wong',
            'description' => 'Email newly curated brochure of 2BHK listings.',
            'due_date' => now()->subDay()->toDateString(), // Overdue test!
            'due_time' => '16:00:00',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        // 17. Expenses
        Expense::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'recorded_by' => $accountant->id,
            'title' => 'Zillow & Realtor.com Featured Listings Subscription',
            'category' => 'marketing',
            'amount' => 1450.00,
            'expense_date' => now()->subDays(5)->toDateString(),
            'payment_method' => 'Corporate Credit Card',
            'notes' => 'Monthly advertising syndication fee.',
        ]);

        Expense::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branchDowntown->id,
            'recorded_by' => $accountant->id,
            'title' => 'Professional Drone & Matterport 3D Photography',
            'category' => 'marketing',
            'amount' => 850.00,
            'expense_date' => now()->subDays(10)->toDateString(),
            'payment_method' => 'Bank Transfer',
            'notes' => 'Drone shoot for Hudson Penthouse & Meadowbrook Villa.',
        ]);

        // 18. Documents
        Document::create([
            'tenant_id' => $tenant1->id,
            'documentable_type' => Property::class,
            'documentable_id' => $prop1->id,
            'title' => 'Hudson Penthouse Title Deed & Floor Plan.pdf',
            'document_type' => 'title_deed',
            'file_path' => 'documents/properties/hudson_deed.pdf',
            'file_size' => 2450120,
            'file_extension' => 'pdf',
            'is_private' => false,
            'uploaded_by' => $propertyManager->id,
        ]);

        Document::create([
            'tenant_id' => $tenant1->id,
            'documentable_type' => Booking::class,
            'documentable_id' => $booking1->id,
            'title' => 'Signed Escrow Reservation Agreement BKG-2026-0001.pdf',
            'document_type' => 'agreement',
            'file_path' => 'documents/bookings/bkg_0001_agreement.pdf',
            'file_size' => 1845000,
            'file_extension' => 'pdf',
            'is_private' => true,
            'uploaded_by' => $agencyAdmin->id,
        ]);

        // 19. Audit Logs
        AuditLog::create([
            'tenant_id' => $tenant1->id,
            'user_id' => $agencyAdmin->id,
            'action' => 'Property Status Changed',
            'auditable_type' => Property::class,
            'auditable_id' => $prop4->id,
            'old_values' => ['status' => 'available'],
            'new_values' => ['status' => 'reserved'],
            'ip_address' => '127.0.0.1',
        ]);
    }
}
