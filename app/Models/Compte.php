<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;

class Compte extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'numero_compte',
        'type',
        'solde_initial',
        'devise',
        'statut',
        'client_id',
        'debut_blocage',
        'fin_blocage',
        'metadata',
    ];

    protected $casts = [
        'solde_initial' => 'decimal:2',
        'debut_blocage' => 'datetime',
        'fin_blocage' => 'datetime',
        'metadata' => 'array',
    ];

    // -------------------------
    // Scopes globaux et locaux
    // -------------------------

    protected static function booted()
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->whereNull('deleted_at');
        });
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('statut', '!=', 'archivé');
    }

    public function scopeNumero(Builder $query, string $numero): Builder
    {
        return $query->where('numero_compte', $numero);
    }

    public function scopeClient(Builder $query, string $telephone): Builder
    {
        return $query->whereHas('client', function ($q) use ($telephone) {
            $q->where('telephone', $telephone);
        });
    }

    // -------------------------
    // Scopes génériques
    // -------------------------

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if (in_array($key, ['page', 'limit', 'search', 'sort', 'order'])) continue;
            if ($value !== null && $value !== '' && Schema::hasColumn($this->getTable(), $key)) {
                $query->where($key, $value);
            }
        }
        return $query;
    }

    public function scopeSearch(Builder $query, ?string $term, array $fields): Builder
    {
        if (!$term || empty($fields)) return $query;

        $query->where(function ($q) use ($term, $fields) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'LIKE', "%$term%");
            }
        });

        return $query;
    }

    public function scopeSort(Builder $query, ?string $sort, ?string $order): Builder
    {
        $sort = $sort ?? 'id';
        $order = strtolower($order ?? 'desc');

        if ((Schema::hasColumn($this->getTable(), $sort))) {
            $query->orderBy($sort, $order);
        }

        return $query;
    }

    /**
     * Relation vers Client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation vers Transactions.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Alias pour compatibilité.
     */
    public function operations()
    {
        return $this->transactions();
    }

    /**
     * Attribut calculé pour le solde.
     */
    public function getSoldeAttribute()
    {
        return $this->transactions()->where('type', 'depot')->sum('montant')
            - $this->transactions()->where('type', 'retrait')->sum('montant');
    }

    /**
     * Accesseurs pour les dates de blocage.
     */
    public function getDateDebutBlocageAttribute()
    {
        return $this->debut_blocage;
    }

    public function getDateFinBlocageAttribute()
    {
        return $this->fin_blocage;
    }
}
