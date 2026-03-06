<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TournamentStatus;

class Tournament extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sport_id',
        'name',
        'image',
        'start_date',
        'description',
        'end_date',
        'game_date',
        'game_time',
        'location',
        'status',
        'tax_rate',
        'sort_order',
    ];

    protected $casts = [
        'status' => TournamentStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'tax_rate' => 'float',
    ];

    protected static function booted()
    {
        static::addGlobalScope('sort_order', function ($query) {
            $query->orderByRaw('COALESCE(sort_order, 999999) ASC')->orderByDesc('created_at');
        });
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'tournament_item')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'tournament_bundle')
            ->withPivot('price')
            ->withTimestamps();
    }
}
