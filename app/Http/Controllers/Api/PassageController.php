<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Passage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PassageController extends Controller
{
    /**
     * POST /api/passages
     *
     * UPSERT idempotent conditionnel sur client_uuid (D-38, D-39).
     * - 200 si INSERT ou UPDATE succeed (status === 'draft')
     * - 409 si le passage existe avec status !== 'draft' (D-40)
     * - 422 si validation échoue
     *
     * Pitfall 7 : DB::affectingStatement() retourne le nombre de lignes affectées.
     * Ne PAS utiliser DB::statement() qui retourne un bool.
     *
     * T-6-05 : paramètres liés nommés (:client_uuid, etc.) — pas d'interpolation string.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_uuid'   => ['required', 'uuid'],
            'piscine_id'    => ['nullable', 'integer', 'exists:piscines,id'],
            'client_id'     => ['nullable', 'integer', 'exists:clients,id'],
            'visited_at'    => ['nullable', 'date'],
            'ph_avant'      => ['nullable', 'numeric'],
            'ph_apres'      => ['nullable', 'numeric'],
            'chlore_libre'  => ['nullable', 'numeric'],
            'chlore_total'  => ['nullable', 'numeric'],
            'tac'           => ['nullable', 'numeric'],
            'sel_g_l'       => ['nullable', 'numeric'],
            'th'            => ['nullable', 'numeric'],
            'actions'       => ['nullable', 'array'],
            'actions.*'     => ['string', 'max:60'],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'notes_privees' => ['nullable', 'string', 'max:2000'],
        ]);

        $visitedAt   = $data['visited_at'] ?? now()->toIso8601String();
        $actionsJson = json_encode($data['actions'] ?? []);
        $now         = now()->toDateTimeString();

        // UPSERT conditionnel sur status='draft' (D-38).
        // La clause WHERE passages.status = 'draft' empêche la mise à jour d'un passage clos (T-6-01).
        // Note : :actions est passé sans ::jsonb pour compatibilité SQLite (tests) + Postgres (prod).
        // Postgres accepte du JSON texte dans une colonne JSONB via binding PDO.
        $affected = DB::affectingStatement(
            <<<SQL
            INSERT INTO passages (
                client_uuid, piscine_id, client_id, visited_at, status,
                ph_avant, ph_apres, chlore_libre, chlore_total, tac, th, sel_g_l,
                actions, notes,
                synced_at, created_at, updated_at
            )
            VALUES (
                :client_uuid, :piscine_id, :client_id, :visited_at, 'draft',
                :ph_avant, :ph_apres, :chlore_libre, :chlore_total, :tac, :th, :sel_g_l,
                :actions, :notes,
                :synced_at, :created_at, :updated_at
            )
            ON CONFLICT (client_uuid) DO UPDATE SET
                piscine_id    = EXCLUDED.piscine_id,
                client_id     = EXCLUDED.client_id,
                visited_at    = EXCLUDED.visited_at,
                ph_avant      = EXCLUDED.ph_avant,
                ph_apres      = EXCLUDED.ph_apres,
                chlore_libre  = EXCLUDED.chlore_libre,
                chlore_total  = EXCLUDED.chlore_total,
                tac           = EXCLUDED.tac,
                th            = EXCLUDED.th,
                sel_g_l       = EXCLUDED.sel_g_l,
                actions       = EXCLUDED.actions,
                notes         = EXCLUDED.notes,
                synced_at     = :synced_at2,
                updated_at    = :updated_at2
            WHERE passages.status = 'draft'
            SQL,
            [
                'client_uuid'  => $data['client_uuid'],
                'piscine_id'   => $data['piscine_id']   ?? null,
                'client_id'    => $data['client_id']    ?? null,
                'visited_at'   => $visitedAt,
                'ph_avant'     => $data['ph_avant']     ?? null,
                'ph_apres'     => $data['ph_apres']     ?? null,
                'chlore_libre' => $data['chlore_libre'] ?? null,
                'chlore_total' => $data['chlore_total'] ?? null,
                'tac'          => $data['tac']          ?? null,
                'th'           => $data['th']           ?? null,
                'sel_g_l'      => $data['sel_g_l']      ?? null,
                'actions'      => $actionsJson,
                'notes'        => $data['notes']        ?? null,
                'synced_at'    => $now,
                'created_at'   => $now,
                'updated_at'   => $now,
                'synced_at2'   => $now,
                'updated_at2'  => $now,
            ]
        );

        if ($affected === 0) {
            // Passage déjà clos (D-40) — retourner l'état serveur pour que le client
            // puisse afficher le message "Ce passage a déjà été clos" et purger sa queue IDB.
            $serverState = Passage::where('client_uuid', $data['client_uuid'])->first();

            return response()->json([
                'error'        => 'already_closed',
                'server_state' => $serverState,
            ], 409);
        }

        return response()->json(['ok' => true], 200);
    }
}
