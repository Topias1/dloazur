<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Passage;
use App\Models\Piscine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Agenda du jour — Pierre voit ses piscines attendues aujourd'hui (admin-1, Plan 07-02).
 *
 * L'agenda est DÉRIVÉ de frequence_jour portée par la piscine (zéro CRUD de RDV).
 * Remonte aussi les flags « à revoir » depuis les notes internes des passages récents.
 */
class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        // Jour courant en français minuscule, ex. 'lundi', 'mardi', …
        $today = Carbon::now()->locale('fr')->isoFormat('dddd');

        // Piscines attendues aujourd'hui (dérivées de frequence_jour)
        $piscinesAujourdhui = Piscine::query()
            ->where('frequence_jour', $today)
            ->with(['client:id,name'])
            ->orderBy('nom')
            ->get();

        // Flags « à revoir » : passages des 7 derniers jours avec notes_privees non nulles
        $aRevoir = Passage::query()
            ->whereNotNull('notes_privees')
            ->where('visited_at', '>=', Carbon::now()->subDays(7))
            ->with(['client:id,name', 'piscine:id,nom'])
            ->orderByDesc('visited_at')
            ->get();

        return view('admin.agenda.index', compact('piscinesAujourdhui', 'aRevoir', 'today'));
    }
}
