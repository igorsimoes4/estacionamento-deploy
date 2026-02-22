<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'check_key',
        'status',
        'message',
        'details',
        'checked_at',
    ];

    protected $casts = [
        'details' => 'array',
        'checked_at' => 'datetime',
    ];
}
