<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Walking Skeleton placeholder home page.
 *
 * Plan 03 (SITE-01 vitrine cohérente) replaces this controller with the full
 * vitrine implementation. Until then `/` serves the skeleton-home view so the
 * brand-styled placeholder is reachable from day one on staging.
 */
final class HomeController extends Controller
{
    public function index(): View
    {
        return view('skeleton-home', [
            'title' => 'Dlo Azur Piscines · Entretien de piscines en Martinique',
            'description' => "Pisciniste d'entretien en Martinique — passages réguliers, transparence sur les interventions, portail client. Devis gratuit sur WhatsApp.",
        ]);
    }
}
