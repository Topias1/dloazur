<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Diagnostic piscine — Plan 05-01 (DIAG-01, Req9)
 *
 * Public route /diagnostic — aucune authentification requise.
 * La route PDF (/diagnostic/{id}/pdf) sera ajoutée en Plan 05-05.
 */
final class DiagnosticController extends Controller
{
    /**
     * GET /diagnostic — landing brand S1 + wizard Livewire.
     *
     * Page indexable (Req9 : SPEC §9, extends 999.1 SEO work).
     * SEO vars mirroring VitrineController::contact() idiom.
     */
    public function show(): View
    {
        return view('vitrine.diagnostic', [
            'title'       => 'Diagnostic piscine gratuit · Dlo Azur Piscines',
            'description' => "Ton eau est trouble, verte ou ton électrolyseur ne produit plus ? Lance le diagnostic piscine gratuit de Dlo Azur et reçois un plan d'action adapté à ton problème — en quelques clics.",
            'canonical'   => url('/diagnostic'),
            'ogImage'     => asset('assets/brand/og-default.jpg'),
        ]);
    }
}
