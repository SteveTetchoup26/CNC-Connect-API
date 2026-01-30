<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidature extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidat_id',
        'concours_id',
        'centre_depot_id',
        'centre_examen_id',
        'code_candidat_temp',
        'code_candidat_def',
        'date_candidature',
        'date_inscription',
        'date_depot_physique',
        'date_validation',
        'qr_code',
        'motif_rejet',
    ];

    protected $casts = [
        'date_candidature' => 'datetime',
        'date_inscription' => 'date',
        'date_depot_physique' => 'date',
        'date_validation' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function candidat()
    {
        return $this->belongsTo(Candidat::class);
    }

    public function concours()
    {
        return $this->belongsTo(Concours::class);
    }

    public function historiques()
    {
        return $this->hasMany(HistoriqueCandidature::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function centreDepot()
    {
        return $this->belongsTo(Centre::class, 'centre_depot_id');
    }

    public function centreExamen()
    {
        return $this->belongsTo(Centre::class, 'centre_examen_id');
    }

    public function rappel()
    {
        return $this->hasOne(CandidatureRappel::class);
    }


    /**
     * Scope pour filtrer par statut
     */
    public function scopeAvecStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les candidatures du candidat connecté
     */
    public function scopeDuCandidat($query, int $candidatId)
    {
        return $query->where('candidat_id', $candidatId);
    }

    /**
     * Scope pour charger toutes les relations nécessaires
     */
    public function scopeAvecRelations($query)
    {
        return $query->with([
            'candidat.utilisateur',
            'concours.concoursFilieres.filiere.departement.ecole',
            'concours.session',
            'centreDepot',
            'centreExamen',
            'historiques.etat',
            'rappel'
        ]);
    }

    /**
     * Vérifier si la candidature a un rappel actif
     */
    public function hasRappelActif(): bool
    {
        return $this->rappel()->where('email_envoye', false)->exists();
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatutFormate(): string
    {
        $statuts = [
            'EN_ATTENTE' => 'En attente',
            'VALIDEE' => 'Validée',
            'REJETEE' => 'Rejetée',
            'ADMIS' => 'Admis',
            'NON_ADMIS' => 'Non admis',
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    /**
     * Vérifier si la candidature peut recevoir un rappel
     */
    public function peutRecevoirRappel(): bool
    {
        // Ne peut recevoir un rappel que si validée et examen pas encore passé
        return $this->statut === 'VALIDEE'
            && $this->concours->date_examen > now();
    }
}
