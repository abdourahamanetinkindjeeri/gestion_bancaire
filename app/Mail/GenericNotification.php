<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $messageBody;
    public string $subjectLine;

    public function __construct(string $subject, string $message)
    {
        $this->subjectLine = $subject;
        $this->messageBody = $message;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.generic')
            ->with(['messageBody' => $this->messageBody]);
    }
}
