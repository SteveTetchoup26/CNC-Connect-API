<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_matiere',
        'code_matiere',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class, 'niveau_matieres');
    }

    public function epreuves()
    {
        return $this->hasMany(Epreuve::class);
    }
}
