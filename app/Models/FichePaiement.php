<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichePaiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'fichier_url',
        'candidat_id',
        'ocr_verifie',
        'ocr_donnees',
        'ocr_date_verification',
        'ocr_erreurs',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function candidat()
    {
        return $this->belongsTo(Candidat::class);
    }
}
