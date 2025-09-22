<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tournament_id',
        'team_name_with_age_group',
        'coach_name',
        'phone_number',
        'email',
        'field_number',
        'items',
        'bundles',
        'instructions',
        'drop_off_time',
        'promo_code',
        'insurance_option',
        'damage_waiver',
        'rental_date',
        'booking_days',
        'delivery_assigned_to',
        'payment_method',
        'payment_status',
        'total_amount',
        'tax_rate',
        'tax_amount',
        'status',
        'return_instruction',
        'estimated_delivery_time',
        'assigned_manager_id',
        'sort_order',
    ];

    protected $casts = [
        'items' => 'array',
        'bundles' => 'array',
        'rental_date' => 'date',
        'drop_off_time' => 'datetime',
        'insurance_option' => 'decimal:2',
        'damage_waiver' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'estimated_delivery_time' => 'datetime',
        'booking_days' => 'integer',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function photos()
    {
        return $this->hasMany(RentalPhoto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(RentalReview::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(RentalStatusLog::class)->orderBy('created_at', 'desc');
    }

    public function assignedManager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('sort_order', function ($query) {
            $query->orderByRaw('COALESCE(sort_order, 999999) ASC')->orderByDesc('created_at');
        });
    }
}
