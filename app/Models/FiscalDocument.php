<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FiscalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'number',
        'series',
        'status',
        'source_type',
        'source_id',
        'customer_name',
        'customer_tax_id',
        'total_cents',
        'issued_at',
        'pdf_url',
        'xml_url',
        'access_key',
        'error_message',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'total_cents' => 'integer',
        'issued_at' => 'datetime',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
