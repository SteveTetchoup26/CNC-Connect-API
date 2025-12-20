<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    use HasFactory;

    protected $fillable = [
        'departement',
        'utilisateur_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation : Un administrateur appartient à un utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
