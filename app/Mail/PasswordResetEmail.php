<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordResetEmail extends Mailable
{
    public $user;
    public $code;

    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
            ->view('emails.password_reset')
            ->with([
                'name' => $this->user->name,
                'code' => $this->code,
            ]);
    }
}
