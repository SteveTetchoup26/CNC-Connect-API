<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtatSession extends Model
{
    use HasFactory;

    const OUVERTE = 'OUVERTE';
    const FERMEE = 'FERMEE';

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
        return $this->hasMany(HistoriqueSession::class, 'etat_id');
    }
}
