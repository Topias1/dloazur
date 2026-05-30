<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PassageCreateController — Plan 02-05 (PASS-01, PASS-02, PASS-03, PASS-06).
 *
 * La vue est rendue par Blade au premier load ; Alpine.data('passageForm') prend
 * le relais côté client (100% offline-first, IndexedDB).
 * Aucune logique Livewire — CF-02 (pas de Livewire pour la saisie offline).
 */
class PassageCreateController extends Controller
{
    /**
     * Affiche le formulaire de création de passage.
     *
     * Query params optionnels :
     *   ?client_id=X → pré-sélectionne le client (ex: lancer la saisie depuis la fiche client)
     *   Le piscine_id est auto-sélectionné si le client n'a qu'une seule piscine (D-64).
     */
    public function create(Request $request): View
    {
        $client  = $request->filled('client_id')
            ? Client::with('piscines')->find($request->integer('client_id'))
            : null;

        // Auto-pick la première piscine si le client n'en a qu'une (D-64)
        $piscine = $client?->piscines->count() === 1
            ? $client->piscines->first()
            : null;

        // Liste des clients pour le sélecteur quand la saisie est ouverte sans client_id.
        // Rendue côté serveur (donc disponible hors-ligne) et bornée — empêche les passages
        // orphelins quand l'opérateur arrive par le bouton « Nouveau passage » global.
        $clients = $client
            ? collect()
            : Client::with('piscines:id,client_id,nom')
                ->orderBy('name')
                ->get()
                ->map(fn (Client $c) => [
                    'id'       => $c->id,
                    'name'     => $c->name,
                    'piscines' => $c->piscines->map(fn ($p) => ['id' => $p->id, 'nom' => $p->nom])->values(),
                ])
                ->values();

        return view('admin.passages.create', compact('client', 'piscine', 'clients'));
    }
}
