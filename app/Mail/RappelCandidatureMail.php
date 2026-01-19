<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelCandidatureMail extends Mailable
{
    use Queueable, SerializesModels;

    public $candidature;

    public function __construct($candidature)
    {
        $this->candidature = $candidature;
    }

    public function build()
    {
        return $this->subject('Rappel : Examen à venir')
            ->view('emails.rappel-candidature');
    }
}

