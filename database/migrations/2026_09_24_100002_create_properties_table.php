<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('listing_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('property_code');
            $table->string('title');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->enum('property_type', [
                'apartment',
                'flat',
                'villa',
                'bungalow',
                'plot',
                'commercial_office',
                'shop',
                'warehouse',
                'agricultural_land'
            ])->default('apartment');
            $table->enum('listing_purpose', ['sale', 'rent', 'lease', 'resale'])->default('sale');
            $table->enum('status', [
                'draft',
                'available',
                'under_negotiation',
                'reserved',
                'sold',
                'rented',
                'leased',
                'inactive'
            ])->default('available');
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('rent_amount', 15, 2)->nullable();
            $table->decimal('security_deposit', 15, 2)->nullable();
            $table->decimal('maintenance_charges', 15, 2)->nullable();
            $table->boolean('is_negotiable')->default(true);
            $table->text('address');
            $table->string('locality')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->integer('balconies')->default(0);
            $table->decimal('carpet_area', 10, 2)->nullable();
            $table->decimal('built_up_area', 10, 2)->nullable();
            $table->string('area_unit')->default('sqft');
            $table->enum('furnishing', ['unfurnished', 'semi_furnished', 'fully_furnished'])->default('unfurnished');
            $table->integer('floor')->nullable();
            $table->integer('total_floors')->nullable();
            $table->integer('parking_spaces')->default(0);
            $table->enum('construction_status', ['ready_to_move', 'under_construction', 'new_launch'])->default('ready_to_move');
            $table->integer('year_built')->nullable();
            $table->date('available_from')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('views_count')->default(0);
            $table->string('featured_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'property_code']);
            $table->index(['tenant_id', 'status', 'listing_purpose']);
            $table->index(['tenant_id', 'property_type']);
            $table->index(['tenant_id', 'city']);
            $table->index(['tenant_id', 'price']);
        });

        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'property_id']);
        });

        Schema::create('property_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index(['tenant_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_amenities');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('properties');
    }
};
