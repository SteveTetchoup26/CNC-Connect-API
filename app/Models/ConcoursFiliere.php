<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcoursFiliere extends Model
{
    use HasFactory;

    protected $fillable = [
        'concours_id',
        'filiere_id',
        'nbre_places',
    ];

    protected $casts = [
        'nbre_places' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function concours()
    {
        return $this->belongsTo(Concours::class);
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }
}
