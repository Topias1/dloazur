<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'address',
        'notes',
        'magic_link_token',
        'magic_link_expires_at',
    ];

    protected $casts = [
        'uuid'                  => 'string',
        'magic_link_expires_at' => 'datetime',
    ];

    public function piscines(): HasMany
    {
        return $this->hasMany(Piscine::class);
    }

    public function passages(): HasMany
    {
        return $this->hasMany(Passage::class);
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}
