<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'level',
        'description',
        'actor_type',
        'actor_id',
        'request_method',
        'request_path',
        'route_name',
        'url',
        'status_code',
        'ip_address',
        'user_agent',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];
}

