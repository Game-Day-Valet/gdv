<?php

namespace App\Mail;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Mail\Mailable;

class CouponEmail extends Mailable
{
    public $user;
    public $coupon;

    public function __construct(User $user, Coupon $coupon)
    {
        $this->user = $user;
        $this->coupon = $coupon;
    }

    public function build()
    {
        return $this->subject('Special Offer: ' . $this->coupon->code)
            ->view('emails.coupon')
            ->with([
                'name' => $this->user->name,
                'coupon' => $this->coupon,
            ]);
    }
}
