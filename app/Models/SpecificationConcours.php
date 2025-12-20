<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecificationConcours extends Model
{
    use HasFactory;

    protected $fillable = [
        'concours_id',
        'desc_infos_concours',
        'carte_nationale_identite',
        'diplomes',
        'certificat_nationalite',
        'releve_notes',
        'acte_naissance',
        'photo',
    ];

    protected $casts = [
        'carte_nationale_identite' => 'boolean',
        'diplomes' => 'boolean',
        'certificat_nationalite' => 'boolean',
        'releve_notes' => 'boolean',
        'acte_naissance' => 'boolean',
        'photo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function concours()
    {
        return $this->belongsTo(Concours::class);
    }
}
