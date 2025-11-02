<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    public $incrementing = false;
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    protected $fillable = [
        'numero',
        'compte_id',
        'type',
        'montant',
        'devise',
        'statut',
        'date_transaction',
        'metadata',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_transaction' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Relation vers Compte.
     */
    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
