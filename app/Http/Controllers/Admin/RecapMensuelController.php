<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RecapMensuelController extends Controller
{
    /**
     * Page « Récap mensuel par client » (admin-5, Plan 07-04).
     *
     * Agrège, par client, le nombre de passages et la chimie consommée
     * sur un mois donné (filtre via whereBetween visited_at).
     *
     * Scope fence : AUCUNE logique de facturation / PDF / Odoo / TVA.
     * `prix_snapshot` est affiché HT brut (franchise 293 B — Phase 3).
     */
    public function index(Request $request): View
    {
        $mois  = $request->integer('mois', now()->month);
        $annee = $request->integer('annee', now()->year);

        // Borne le mois/année à des valeurs valides
        $mois  = max(1, min(12, $mois));
        $annee = max(2020, min(2100, $annee));

        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = $debut->copy()->endOfMonth();

        $clients = Client::query()
            ->withCount([
                'passages as nb_passages' => fn ($q) => $q->whereBetween('visited_at', [$debut, $fin]),
            ])
            ->having('nb_passages', '>', 0)
            ->with([
                'passages' => fn ($q) => $q
                    ->whereBetween('visited_at', [$debut, $fin])
                    ->with('produits'),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.recap.index', compact('clients', 'mois', 'annee', 'debut'));
    }
}
