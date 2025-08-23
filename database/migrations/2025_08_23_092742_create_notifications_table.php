<?php

  namespace App\Models;

  use Illuminate\Database\Eloquent\Model;
  use Illuminate\Notifications\DatabaseNotification;

  class Notification extends DatabaseNotification
  {
      protected $table = 'notifications';

      protected $fillable = [
          'id',
          'type',
          'notifiable_id',
          'notifiable_type',
          'data',
          'read_at',
      ];

      protected $casts = [
          'data' => 'array',
          'read_at' => 'datetime',
      ];
  }