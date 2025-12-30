<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CandidatureValideeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $candidat;
    public $candidature;
    public $concours;
    public $downloadUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($candidat, $candidature, $concours, $downloadUrl)
    {
        $this->candidat = $candidat;
        $this->candidature = $candidature;
        $this->concours = $concours;
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('✅ Candidature Validée - ' . $this->concours->libelle_concours)
                    ->view('emails.candidature_validee');
    }
}
