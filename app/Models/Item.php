<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ItemStatus;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image_path',
        'availability',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'availability' => 'array',
        'status' => ItemStatus::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope('sort_order', function ($query) {
            $query->orderByRaw('COALESCE(sort_order, 999999) ASC')->orderByDesc('created_at');
        });
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function cart_items()
    {
        $userId = Auth::id();
        return $this->hasMany(CartItem::class, 'item_id')
            ->where('is_bundle', 0)
            ->where('user_id', $userId);
    }
}
