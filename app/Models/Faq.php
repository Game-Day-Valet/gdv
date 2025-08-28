<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'type',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('sort_order', function ($query) {
            $query->orderByRaw('COALESCE(sort_order, 999999) ASC')->orderByDesc('created_at');
        });
    }
}
