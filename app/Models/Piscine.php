<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piscine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'nom',
        'volume_m3',
        'type',
        'filtration',
        'traitement',
        'equipements',
        'notes',
    ];

    protected $casts = [
        'volume_m3'   => 'decimal:2',
        'equipements' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function passages(): HasMany
    {
        return $this->hasMany(Passage::class);
    }
}
