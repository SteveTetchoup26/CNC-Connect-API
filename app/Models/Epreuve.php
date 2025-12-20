<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epreuve extends Model
{
    use HasFactory;

    protected $fillable = [
        'intitule',
        'session',
        'url_epreuve',
        'matiere_id',
    ];

    protected $casts = [
        'session' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }
}
