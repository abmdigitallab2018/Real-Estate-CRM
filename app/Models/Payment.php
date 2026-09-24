<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'booking_id',
        'deal_id',
        'customer_id',
        'payment_reference',
        'amount',
        'payment_type',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'receipt_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
