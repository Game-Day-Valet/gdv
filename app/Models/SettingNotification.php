<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SettingNotification extends Model
{
    protected $fillable = ['email_enabled','sms_enabled','fcm_enabled'];

    public static function current(): self
    {
        return Cache::remember('setting_notifications_current', now()->addMinutes(5), function () {
            return self::query()->orderBy('id','asc')->first() ?? new self(['email_enabled'=>true,'sms_enabled'=>true,'fcm_enabled'=>true]);
        });
    }

    public function refreshCache(): void
    {
        Cache::forget('setting_notifications_current');
        self::current();
    }
}


