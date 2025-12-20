<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ecole extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_ecole',
        'code_ecole',
        'localisation',
        'logo_url',
        'bp_ecole',
        'email_ecole',
        'siteweb_ecole',
        'devise',
        'telephone_ecole',
        'embleme_ecole',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }
}
