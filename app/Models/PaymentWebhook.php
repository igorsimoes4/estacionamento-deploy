<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'event_type',
        'external_id',
        'payload',
        'signature',
        'status',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
