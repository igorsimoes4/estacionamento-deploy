<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_type',
        'storage_disk',
        'path',
        'status',
        'size_bytes',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
