<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'nom',
        'prenom',
        'telephone',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function candidat()
    {
        return $this->hasOne(Candidat::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'utilisateur_roles');
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }

    public function hasRole($role)
    {
        return $this->roles()->where('libelle_role', $role)->exists();
    }

    public function hasPermission($permission)
    {
        return $this->roles()->whereHas('permissions', function($q) use ($permission) {
            $q->where('libelle_permission', $permission);
        })->exists();
    }
}
