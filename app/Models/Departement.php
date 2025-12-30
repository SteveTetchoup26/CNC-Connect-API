<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_departement',
        'code_departement',
        'description_departement',
        'ecole_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }

    public function filieres()
    {
        return $this->hasMany(Filiere::class);
    }
}
