<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrat extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'libelle',
        'prix_ht_mensuel',
        'jour_facturation',
        'date_debut',
        'date_fin',
        'actif',
    ];

    protected $casts = [
        'prix_ht_mensuel' => 'decimal:2',
        'date_debut'      => 'date',
        'date_fin'        => 'date',
        'actif'           => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}
