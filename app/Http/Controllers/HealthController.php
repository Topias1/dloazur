<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Health endpoint used by Laravel Cloud probes and any external monitor.
 *
 * Returns the minimum signal needed to confirm app+DB liveness without
 * leaking driver, version, or host (threat T-1-07 mitigation).
 */
final class HealthController extends Controller
{
    public function ping(): JsonResponse
    {
        $db = 'ok';

        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $db = 'fail';
        }

        return response()->json(
            ['app' => 'ok', 'db' => $db],
            $db === 'ok' ? 200 : 503,
        );
    }
}
