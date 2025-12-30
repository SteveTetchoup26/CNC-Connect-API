<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_niveau',
        'code_niveau',
        'description_niveau',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'niveau_matieres')
                    ->withTimestamps();
    }

    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'filiere_niveaux');
    }
}
