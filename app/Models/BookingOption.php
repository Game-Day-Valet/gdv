<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'label', 'price', 'enabled', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'enabled' => 'boolean',
    ];
} 