<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'provider',
        'method',
        'status',
        'type',
        'amount_cents',
        'fee_cents',
        'net_amount_cents',
        'currency',
        'car_id',
        'monthly_subscriber_id',
        'parking_reservation_id',
        'monthly_billing_cycle_id',
        'external_id',
        'payment_url',
        'barcode',
        'digitable_line',
        'pix_payload',
        'gateway_payload',
        'due_date',
        'paid_at',
        'reconciled_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'fee_cents' => 'integer',
        'net_amount_cents' => 'integer',
        'car_id' => 'integer',
        'monthly_subscriber_id' => 'integer',
        'parking_reservation_id' => 'integer',
        'monthly_billing_cycle_id' => 'integer',
        'gateway_payload' => 'array',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Cars::class);
    }

    public function monthlySubscriber(): BelongsTo
    {
        return $this->belongsTo(MonthlySubscriber::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(ParkingReservation::class, 'parking_reservation_id');
    }

    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(MonthlyBillingCycle::class, 'monthly_billing_cycle_id');
    }
}
