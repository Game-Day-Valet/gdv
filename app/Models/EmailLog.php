<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_email', 'subject', 'body_preview', 'status', 'error_reason', 'sent_at', 'meta'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'meta' => 'array',
    ];
}


