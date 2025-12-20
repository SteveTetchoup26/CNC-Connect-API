<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Centre extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle_centre',
        'type_centre',
        'ville_centre',
        'capacite',
        'region_id',
    ];

    protected $casts = [
        'capacite' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
