<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Passage;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PassageProduitController extends Controller
{
    /**
     * POST /api/passages/produits
     *
     * Synchronise le pivot passage_produit pour un passage donné.
     * Idempotent : un second appel avec une liste différente REMPLACE l'ensemble du pivot (sync).
     * prix_snapshot = prix_ht du produit au moment de la synchro (franchise 293 B — pas de TVA).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'passage_client_uuid'       => ['required', 'uuid', 'exists:passages,client_uuid'],
            'produits'                  => ['nullable', 'array'],
            'produits.*.produit_id'     => ['required', 'integer', 'exists:produits,id'],
            'produits.*.quantite'       => ['nullable', 'numeric', 'min:0', 'max:9999'],
        ]);

        $passage = Passage::where('client_uuid', $data['passage_client_uuid'])->firstOrFail();

        // Construire la map sync : produit_id → [quantite, prix_snapshot]
        $sync = [];
        foreach ($data['produits'] ?? [] as $item) {
            $produit = Produit::find($item['produit_id']);
            $sync[$item['produit_id']] = [
                'quantite'      => $item['quantite'] ?? null,
                'prix_snapshot' => $produit?->prix_ht,
            ];
        }

        // sync() remplace l'intégralité du pivot (idempotent)
        $passage->produits()->sync($sync);

        return response()->json(['ok' => true], 200);
    }
}
