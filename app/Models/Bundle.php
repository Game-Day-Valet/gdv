<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ItemStatus;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class Bundle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'status',
        'sort_order',
        'is_most_popular',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => ItemStatus::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope('sort_order', function ($query) {
            $query->orderByRaw('COALESCE(sort_order, 999999) ASC')->orderByDesc('created_at');
        });
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'bundle_item')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function cart_items()
    {
        $userId = Auth::id();
        return $this->hasMany(CartItem::class, 'item_id')
            ->where('is_bundle', true)
            ->where('user_id', $userId);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
