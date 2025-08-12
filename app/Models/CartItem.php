<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'item_id',
        'is_bundle',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'is_bundle' => 'boolean',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'item_id');
    }
}


