<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'owner_id',
        'listing_agent_id',
        'property_code',
        'title',
        'slug',
        'description',
        'property_type',
        'listing_purpose',
        'status',
        'price',
        'rent_amount',
        'security_deposit',
        'maintenance_charges',
        'is_negotiable',
        'address',
        'locality',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'bedrooms',
        'bathrooms',
        'balconies',
        'carpet_area',
        'built_up_area',
        'area_unit',
        'furnishing',
        'floor',
        'total_floors',
        'parking_spaces',
        'construction_status',
        'year_built',
        'available_from',
        'is_featured',
        'is_published',
        'views_count',
        'featured_image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rent_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'maintenance_charges' => 'decimal:2',
        'is_negotiable' => 'boolean',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'available_from' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected static function booted(): void
    {
        static::creating(function ($property) {
            if (empty($property->slug) && !empty($property->title)) {
                $property->slug = \Illuminate\Support\Str::slug($property->title) . '-' . \Illuminate\Support\Str::random(6);
            }
            if (empty($property->property_code)) {
                $property->property_code = 'PROP-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(Customer::class, 'owner_id');
    }

    public function listingAgent()
    {
        return $this->belongsTo(User::class, 'listing_agent_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function amenities()
    {
        return $this->hasMany(PropertyAmenity::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function propertyMatches()
    {
        return $this->hasMany(PropertyMatch::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->listing_purpose === 'rent' || $this->listing_purpose === 'lease'
            ? ($this->rent_amount ?: $this->price)
            : $this->price);
    }
}
