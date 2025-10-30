<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['name', 'email', 'telephone', 'password'];

    protected $casts = [
        'telephone' => 'string',
    ];
    protected $hidden = ['password', 'remember_token'];

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }
}
