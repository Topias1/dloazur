<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Passage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard with real stat-card values (D-19, Plan 02-03).
     *
     * Calculates:
     *   - $passagesThisWeek : passages created between start and end of current week
     *   - $clientsCount     : total number of clients
     *   - $eauASurveiller   : passages in the last 30 days with at least one out-of-range measure (D-63)
     *   - $aSynchroniser    : server-side placeholder = 0 (Plan 02-05 will wire Alpine $store.offlineQueue)
     */
    public function index(Request $request)
    {
        $passagesThisWeek = Passage::whereBetween('visited_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->count();

        $clientsCount = Client::count();

        // Eau à surveiller : passages des 30 derniers jours avec au moins une mesure hors plage soft (D-63)
        // Plages soft : pH [5.0, 9.0], chlore_libre [0, 10], chlore_total [0, 15],
        //               TAC [50, 300], sel_g_l [0, 8], TH [0, 50]
        $eauASurveiller = Passage::query()
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('ph_avant')
                        ->where(function ($range) {
                            $range->where('ph_avant', '<', 5.0)
                                  ->orWhere('ph_avant', '>', 9.0);
                        });
                })
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('chlore_libre')
                        ->where(function ($range) {
                            $range->where('chlore_libre', '<', 0)
                                  ->orWhere('chlore_libre', '>', 10);
                        });
                })
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('chlore_total')
                        ->where(function ($range) {
                            $range->where('chlore_total', '<', 0)
                                  ->orWhere('chlore_total', '>', 15);
                        });
                })
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('tac')
                        ->where(function ($range) {
                            $range->where('tac', '<', 50)
                                  ->orWhere('tac', '>', 300);
                        });
                })
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('sel_g_l')
                        ->where(function ($range) {
                            $range->where('sel_g_l', '<', 0)
                                  ->orWhere('sel_g_l', '>', 8);
                        });
                })
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('th')
                        ->where(function ($range) {
                            $range->where('th', '<', 0)
                                  ->orWhere('th', '>', 50);
                        });
                });
            })
            ->whereDate('visited_at', '>=', Carbon::now()->subDays(30))
            ->count();

        // À synchroniser : placeholder serveur = 0
        // Plan 02-05 branchera sur Alpine $store.offlineQueue.pendingCount côté client
        $aSynchroniser = 0;

        return view('admin.dashboard', [
            'user'              => auth()->user(),
            'passagesThisWeek'  => $passagesThisWeek,
            'clientsCount'      => $clientsCount,
            'eauASurveiller'    => $eauASurveiller,
            'aSynchroniser'     => $aSynchroniser,
        ]);
    }
}
