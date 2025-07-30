<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerifyEmailOTP extends Mailable
{
    public $user;
    public $otp;

    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
            ->view('emails.verify')
            ->with([
                'name' => $this->user->name,
                'otp' => $this->otp,
            ]);
            
    }
}
