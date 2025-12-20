<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcoursSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'concours_id',
        'session_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function concours()
    {
        return $this->belongsTo(Concours::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
