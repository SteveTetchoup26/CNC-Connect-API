<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_session',
        'description_session',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function concours()
    {
        return $this->belongsToMany(Concours::class, 'concours_sessions');
    }

    public function historiques()
    {
        return $this->hasMany(HistoriqueSession::class);
    }
}
