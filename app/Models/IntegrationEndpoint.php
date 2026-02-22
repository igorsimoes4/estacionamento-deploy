<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'base_url',
        'auth_token',
        'auth_secret',
        'settings',
        'is_active',
        'last_health_status',
        'last_health_message',
        'last_checked_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];
}
