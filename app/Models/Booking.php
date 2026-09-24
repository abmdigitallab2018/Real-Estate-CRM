<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'customer_id',
        'agent_id',
        'deal_id',
        'booking_number',
        'booking_date',
        'expiry_date',
        'total_amount',
        'booking_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'payment_status',
        'cancellation_reason',
        'refund_amount',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'expiry_date' => 'date',
        'total_amount' => 'decimal:2',
        'booking_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BKG-' . date('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public static function hasActiveReservation(int $propertyId, ?int $excludeBookingId = null): bool
    {
        $query = static::where('property_id', $propertyId)
            ->whereIn('status', ['pending', 'confirmed']);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }
}
