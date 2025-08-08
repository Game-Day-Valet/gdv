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
        'team_name',
        'coach_name',
        'field_number',
        'items',
        'bundles',
        'instructions',
        'drop_off_time',
        'promo_code',
        'insurance_option',
        'damage_waiver',
        'rental_date',
        'delivery_assigned_to',
        'payment_method',
        'payment_status',
        'total_amount',
        'status',
        'return_instruction',
    ];

    protected $casts = [
        'items' => 'array',
        'bundles' => 'array',
        'rental_date' => 'date',
        'drop_off_time' => 'datetime',
        'damage_waiver' => 'boolean',
        'total_amount' => 'decimal:2',
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
}
