<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'expires_at' => 'date',
    ];

    // Check if the coupon is valid
    public function isValid(): bool
    {
        $now = Carbon::now();

        return (!$this->starts_at || $this->starts_at <= $now)
            && (!$this->expires_at || $this->expires_at >= $now)
            && (!$this->max_uses || $this->used < $this->max_uses);
    }

    // Apply the discount to a given amount
    public function applyDiscount($amount): float
    {
        if ($this->type === 'fixed') {
            return max(0, $amount - $this->value);
        } elseif ($this->type === 'percent') {
            return max(0, $amount - ($amount * ($this->value / 100)));
        }

        return $amount;
    }
}
