<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'branch_id',
        'assigned_agent_id',
        'property_id',
        'lead_type',
        'name',
        'email',
        'phone',
        'source',
        'status',
        'priority',
        'score',
        'budget_min',
        'budget_max',
        'preferred_location',
        'property_type',
        'requirements',
        'tags',
        'lost_reason',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'tags' => 'array',
        'score' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function propertyMatches()
    {
        return $this->hasMany(PropertyMatch::class);
    }

    public static function checkDuplicate(int $tenantId, string $phone, ?string $email = null, ?int $excludeId = null): ?self
    {
        $query = static::where('tenant_id', $tenantId)
            ->where(function ($q) use ($phone, $email) {
                $q->where('phone', $phone);
                if ($email) {
                    $q->orWhere('email', $email);
                }
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }
}
