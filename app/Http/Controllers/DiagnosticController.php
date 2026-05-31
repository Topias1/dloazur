<?php

namespace App\Http\Controllers;

use App\Models\Diagnostic;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Diagnostic piscine — Plan 05-01 (DIAG-01, Req9) + Plan 05-05 (Req8, D-06)
 *
 * Public routes :
 *   GET /diagnostic       — landing brand S1 + wizard Livewire
 *   GET /diagnostic/{id}/pdf — rapport PDF session-gatée (D-06)
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
            'description' => "Votre eau est trouble, verte ou votre électrolyseur ne produit plus ? Lancez le diagnostic piscine gratuit de Dlo Azur et recevez un plan d'action adapté à votre problème — en quelques clics.",
            'canonical'   => url('/diagnostic'),
            'ogImage'     => asset('assets/brand/og-default.jpg'),
        ]);
    }

    /**
     * GET /diagnostic/{diagnostic}/pdf — rapport DomPDF téléchargeable (Req8).
     *
     * D-06 : accès anonyme verrouillé sur la session.
     * L'id du diagnostic est injecté dans session('diagnostic_ids') à la persistance (Plan 03).
     * Un client authentifié peut accéder à son propre diagnostic via client_id.
     * Tout autre accès → 403 (prévient l'énumération séquentielle d'ids).
     *
     * D-05 : génération synchrone via DomPDF (pas de queue, pas de Node/Chrome).
     * Compatible Laravel Cloud serverless.
     */
    public function pdf(Diagnostic $diagnostic): Responsable
    {
        // D-06 : session gate — abort_unless en session OU client authentifié propriétaire
        // Remarque : client_id null ne doit jamais matcher un visiteur non authentifié (null === null serait vrai).
        // On vérifie explicitement l'authentification avant de comparer les ids.
        $ownedByClient = auth('clients')->check()
            && $diagnostic->client_id !== null
            && $diagnostic->client_id === auth('clients')->id();

        abort_unless(
            in_array($diagnostic->id, session('diagnostic_ids', []), true) || $ownedByClient,
            403
        );

        return Pdf::view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])
            ->driver('dompdf')
            ->name("diagnostic-{$diagnostic->id}.pdf")
            ->download();
    }
}
