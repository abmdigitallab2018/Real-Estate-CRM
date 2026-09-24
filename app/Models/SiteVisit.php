<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'customer_id',
        'assigned_agent_id',
        'lead_id',
        'deal_id',
        'visit_code',
        'scheduled_date',
        'scheduled_time',
        'status',
        'interest_level',
        'customer_feedback',
        'agent_notes',
        'next_action',
        'reminder_sent',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'reminder_sent' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
