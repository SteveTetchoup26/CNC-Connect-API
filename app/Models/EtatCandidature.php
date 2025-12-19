<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtatCandidature extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_etat',
        'description_etat',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function historiques()
    {
        return $this->hasMany(HistoriqueCandidature::class, 'etat_id');
    }
}
