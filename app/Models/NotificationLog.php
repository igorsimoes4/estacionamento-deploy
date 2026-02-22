<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'recipient',
        'title',
        'message',
        'status',
        'notifiable_type',
        'notifiable_id',
        'scheduled_at',
        'sent_at',
        'provider_response',
        'error_message',
    ];

    protected $casts = [
        'notifiable_id' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
