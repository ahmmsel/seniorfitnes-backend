<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'charge_id',
        'amount',
        'currency',
        'status',
        'reference',
        'metadata',
        'raw'
    ];

    protected $casts = [
        'reference' => 'array',
        'metadata' => 'array',
        'raw' => 'array',
        'amount' => 'float',
    ];
}
