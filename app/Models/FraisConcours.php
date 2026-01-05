<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraisConcours extends Model
{
    protected $table = 'frais_concours';

    protected $fillable = [
        'montant_frais',
        'nom_banque',
        'numero_compte',
        'concours_id',
    ];

    public function concours()
    {
        return $this->belongsTo(Concours::class, 'concours_id');
    }
}
