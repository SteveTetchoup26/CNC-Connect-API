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
}
