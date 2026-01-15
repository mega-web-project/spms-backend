<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuthMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($fullName, $resetUrl, $loginUrl, $temporaryPassword)
    {
        $this->fullName = $fullName;
        $this->resetUrl = $resetUrl;
        $this->loginUrl = $loginUrl;
        $this->temporaryPassword = $temporaryPassword;
    }

   
    /**
     * Get the message content definition.
     */
    public function build(){
        return $this->subject('Welcome to SPMS - Set Your Password')
                    ->view('emails.auth_mail');             
    }
}
