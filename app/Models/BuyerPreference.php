<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerPreference extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'purpose',
        'property_types',
        'min_budget',
        'max_budget',
        'preferred_locations',
        'min_bedrooms',
        'min_bathrooms',
        'min_area',
        'possession_timeline',
        'furnishing',
    ];

    protected $casts = [
        'property_types' => 'array',
        'preferred_locations' => 'array',
        'min_budget' => 'decimal:2',
        'max_budget' => 'decimal:2',
        'min_area' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
