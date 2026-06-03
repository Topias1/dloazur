<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Produit extends Model
{
    protected $fillable = [
        'sku',
        'libelle',
        'description',
        'prix_ht',
        'unite',
        'actif',
    ];

    protected $casts = [
        'prix_ht' => 'decimal:2',
        'actif'   => 'boolean',
    ];

    public function passages(): BelongsToMany
    {
        return $this->belongsToMany(Passage::class, 'passage_produit')
            ->withPivot(['quantite', 'prix_snapshot'])
            ->withTimestamps();
    }
}
