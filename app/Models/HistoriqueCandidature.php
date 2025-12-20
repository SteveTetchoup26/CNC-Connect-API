<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueCandidature extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_etat',
        'etat_id',
        'candidature_id',
    ];

    protected $casts = [
        'date_etat' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function etat()
    {
        return $this->belongsTo(EtatCandidature::class, 'etat_id');
    }

    public function candidature()
    {
        return $this->belongsTo(Candidature::class);
    }
}
