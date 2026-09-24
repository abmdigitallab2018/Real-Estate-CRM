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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->enum('lead_type', ['buyer', 'seller', 'renter', 'landlord', 'investor'])->default('buyer');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->enum('source', [
                'website',
                'social_media',
                'referrals',
                'property_portals',
                'advertisements',
                'calls',
                'walk_ins',
                'direct'
            ])->default('website');
            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'site_visit_scheduled',
                'negotiation',
                'converted',
                'lost'
            ])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->integer('score')->default(50);
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('property_type')->nullable();
            $table->text('requirements')->nullable();
            $table->json('tags')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status', 'assigned_agent_id']);
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'source']);
        });

        Schema::create('property_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->integer('match_score')->default(0);
            $table->enum('status', ['suggested', 'shortlisted', 'shared', 'rejected', 'visited'])->default('suggested');
            $table->timestamp('shared_at')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id', 'status']);
            $table->index(['tenant_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_matches');
        Schema::dropIfExists('leads');
    }
};
