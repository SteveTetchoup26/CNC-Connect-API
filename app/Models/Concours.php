<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concours extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_concours',
        'date_limite',
        'date_examen',
        'frais_inscription',
    ];

    protected $casts = [
        'date_limite' => 'datetime',
        'date_examen' => 'datetime',
        'frais_inscription' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'concours_filieres')
                    ->withPivot('nbre_places')
                    ->withTimestamps();
    }

    public function concoursFilieres()
    {
        return $this->hasMany(ConcoursFiliere::class);
    }

    public function specification()
    {
        return $this->hasOne(SpecificationConcours::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class, 'concours_sessions');
    }

    public function fraisConcours()
    {
        return $this->hasOne(FraisConcours::class, 'concours_id');
    }
}
