<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable,HasUuids;

    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'code_client',
        'titulaire',
        'nci',
        'email',
        'telephone',
        'adresse',
        'actif',
        'metadata',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relation vers User (polymorphique).
     */
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Relation vers Comptes.
     */
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
