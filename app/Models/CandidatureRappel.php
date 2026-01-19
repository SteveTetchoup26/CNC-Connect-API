<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidatureRappel extends Model
{
    protected $fillable = [
        'candidature_id',
        'date_rappel',
        'heure_rappel',
        'email_envoye',
        'date_envoi_email',
    ];

    protected $casts = [
        'date_rappel' => 'date',
        'email_envoye' => 'boolean',
        'date_envoi_email' => 'datetime',
    ];

    public function candidature()
    {
        return $this->belongsTo(Candidature::class);
    }
}
