<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'status',
        'notes',
        'image_paths',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'image_paths' => 'array',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getImageUrlsAttribute()
    {
        if ($this->image_paths && is_array($this->image_paths)) {
            return collect($this->image_paths)->map(function($path) {
                return asset('storage/' . $path);
            })->toArray();
        }
        return [];
    }

    public function getFirstImageUrlAttribute()
    {
        $urls = $this->getImageUrlsAttribute();
        return !empty($urls) ? $urls[0] : null;
    }
}
