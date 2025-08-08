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
        'end_date',
        'location',
        'status',
    ];

    protected $casts = [
        'status' => TournamentStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
}
