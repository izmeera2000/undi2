<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class FirstTimeLoginMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $temporaryPassword;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->temporaryPassword = $user->password_plain ?? null;
    }

    public function build()
    {
        return $this->subject('Welcome to the System - First Time Login')
                    ->view('emails.first_time_login')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'password' => $this->temporaryPassword,
                        'loginUrl' => url('/login'),
                    ]);
    }
}
