<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'label', 'description', 'price', 'enabled', 'sort_order',
        'testimonial_quote', 'testimonial_author', 'support_phone_number',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'enabled' => 'boolean',
    ];
} 