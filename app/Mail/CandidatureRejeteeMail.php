<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CandidatureRejeteeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $candidat;
    public $candidature;
    public $concours;
    public $motifRejet;

    /**
     * Create a new message instance.
     */
    public function __construct($candidat, $candidature, $concours, $motifRejet)
    {
        $this->candidat = $candidat;
        $this->candidature = $candidature;
        $this->concours = $concours;
        $this->motifRejet = $motifRejet;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Candidature non retenue - ' . $this->concours->libelle_concours)
                    ->view('emails.candidature_rejetee');
    }
}
