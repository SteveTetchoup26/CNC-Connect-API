<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_filiere',
        'code_filiere',
        'description_filiere',
        'departement_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class, 'filiere_niveaux');
    }

    public function concours()
    {
        return $this->belongsToMany(Concours::class, 'concours_filieres')
                    ->withPivot('nbre_places')
                    ->withTimestamps();
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }
}
