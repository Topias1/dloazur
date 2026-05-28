<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnostic extends Model
{
    protected $fillable = [
        'client_id',
        'piscine_id',
        'volume_m3',
        'type_probleme',
        'mesures',
        'recommandations',
        'disclaimer_accepted_at',
        'created_via',
    ];

    protected $casts = [
        'volume_m3'              => 'decimal:2',
        'mesures'                => 'array',
        'recommandations'        => 'array',
        'disclaimer_accepted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function piscine(): BelongsTo
    {
        return $this->belongsTo(Piscine::class);
    }
}
