<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subjectText;
    public $bodyMessage;

    public function __construct(User $user, $subjectText, $bodyMessage)
    {
        $this->user = $user;
        $this->subjectText = $subjectText;
        $this->bodyMessage = $bodyMessage;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.admin_message');
    }
} 