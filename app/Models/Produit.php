<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
