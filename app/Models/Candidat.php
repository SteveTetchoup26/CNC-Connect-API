<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidat extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        // Informations personnelles
        'adresse',
        'nationalite',
        'sexe',
        'age',
        'date_naissance',
        'lieu_naissance',
        'region_origine',
        'ville_origine',
        'departement_origine',
        'statut_matrimonial',
        'handicap',
        // Pièce d'identité
        'numero_cni',
        'date_delivrance_cni',
        // Informations familiales
        'nom_mere',
        'telephone_mere',
        'nom_pere',
        'telephone_pere',
        'nom_tuteur',
        'telephone_tuteur',
        // Informations scolaires
        'niveau_scolaire',
        'filiere_origine',
        'diplome_admission',
        'mention',
        'annee_diplome',
        // Paiement
        'num_recu',
    ];

    protected $casts = [
        'age' => 'integer',
        'date_naissance' => 'date',
        'date_delivrance_cni' => 'date',
        'annee_diplome' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    public function fichePaiements()
    {
        return $this->hasMany(FichePaiement::class);
    }
}
