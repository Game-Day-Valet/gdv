<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'responder_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function unreadMessages()
    {
        return $this->messages()->where('is_read', false)
            ->where('sender_id', '!=', auth()->id());
    }
}
