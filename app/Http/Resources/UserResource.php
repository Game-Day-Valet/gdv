<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'contact_number' => $this->contact_number,
            'profile_image' => $this->profile_image,
            'email_verified_at' => $this->email_verified_at,
            'is_notification' => (bool) $this->fcm_notification,
            'is_email_notification' => (bool) ($this->email_notification ?? true),
            'is_sms_notification' => (bool) ($this->text_notification ?? true),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
