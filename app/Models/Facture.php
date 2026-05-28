<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facture extends Model
{
    protected $fillable = [
        'uuid',
        'numero',
        'client_id',
        'contrat_id',
        'passage_id',
        'lignes',
        'total_ht',
        'tva',
        'total_ttc',
        'tva_rate',
        'statut',
        'odoo_id',
        'odoo_synced_at',
        'odoo_sync_error',
        'date_echeance',
    ];

    protected $casts = [
        'uuid'          => 'string',
        'numero'        => 'string',
        'odoo_id'       => 'integer',
        'lignes'        => 'array',
        'total_ht'      => 'decimal:2',
        'tva'           => 'decimal:2',
        'total_ttc'     => 'decimal:2',
        'tva_rate'      => 'decimal:2',
        'odoo_synced_at'=> 'datetime',
        'date_echeance' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }

    public function passage(): BelongsTo
    {
        return $this->belongsTo(Passage::class);
    }
}
