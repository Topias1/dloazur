<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\PhotoMeta;

class Passage extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_uuid',
        'piscine_id',
        'client_id',
        'visited_at',
        'status',
        'ph_avant',
        'ph_apres',
        'chlore_libre',
        'chlore_total',
        'tac',
        'th',
        'sel_g_l',
        'actions',
        'notes',
        'pdf_path',
        'signature_path',
        'synced_at',
    ];

    protected $casts = [
        'client_uuid' => 'string',
        'actions'     => 'array',
        'visited_at'  => 'datetime',
        'synced_at'   => 'datetime',
        'ph_avant'    => 'decimal:2',
        'ph_apres'    => 'decimal:2',
        'chlore_libre'  => 'decimal:2',
        'chlore_total'  => 'decimal:2',
        'tac'           => 'decimal:2',
        'th'            => 'decimal:2',
        'sel_g_l'       => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function piscine(): BelongsTo
    {
        return $this->belongsTo(Piscine::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PhotoMeta::class);
    }

    public function signature(): HasOne
    {
        return $this->hasOne(Signature::class);
    }

    public function latestPhoto(): HasOne
    {
        return $this->hasOne(PhotoMeta::class)->latestOfMany('captured_at');
    }

    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'passage_produit')
            ->withPivot(['quantite', 'prix_snapshot'])
            ->withTimestamps();
    }
}
