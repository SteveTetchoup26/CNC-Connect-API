<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_etat',
        'etat_id',
        'session_id',
    ];

    protected $casts = [
        'date_etat' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function etat()
    {
        return $this->belongsTo(EtatSession::class, 'etat_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
