<?php

namespace App\Http\Controllers;

use App\Support\SchemaOrg\LocalBusinessSchema;
use Illuminate\Contracts\View\View;

/**
 * Public vitrine routes — Plan 01-03.
 *
 * Each method passes SEO variables to the shared <x-layouts.app> layout.
 * The home method additionally injects JSON-LD via the LocalBusinessSchema builder.
 */
final class VitrineController extends Controller
{
    public function home(LocalBusinessSchema $schema): View
    {
        return view('vitrine.home', [
            'title'       => 'Dlo Azur Piscines · Entretien de piscines en Martinique',
            'description' => "Entretien, dépannage et analyse de l'eau de votre piscine en Martinique. Un service à taille humaine. Devis gratuit, réponse rapide sur WhatsApp.",
            'canonical'   => url('/'),
            'ogImage'     => asset('assets/brand/photos/hero-pierre-piscine.jpg'),
            'jsonLd'      => $schema->toScript(),
        ]);
    }

    public function services(): View
    {
        return view('vitrine.services', [
            'title'       => 'Services · Dlo Azur Piscines — Entretien & dépannage piscine en Martinique',
            'description' => 'Entretien régulier, dépannage rapide, analyse de l\'eau et montage hors-sol. Votre pisciniste de confiance partout en Martinique.',
            'canonical'   => url('/services'),
            'ogImage'     => asset('assets/brand/photos/entretien-dos-logo.jpg'),
        ]);
    }

    public function realisations(): View
    {
        return view('vitrine.realisations', [
            'title'       => 'Réalisations · Dlo Azur Piscines — Chantiers en Martinique',
            'description' => 'Découvrez nos réalisations : remises en état, traitements eau verte, entretien de villas et de piscines hors-sol partout en Martinique.',
            'canonical'   => url('/realisations'),
            'ogImage'     => asset('assets/brand/photos/avant-apres.jpg'),
        ]);
    }

    public function contact(): View
    {
        return view('vitrine.contact', [
            'title'       => 'Nous contacter · Dlo Azur Piscines',
            'description' => 'Contactez Dlo Azur Piscines pour un devis gratuit, une intervention rapide ou toute question sur votre piscine en Martinique.',
            'canonical'   => url('/contact'),
            'ogImage'     => asset('assets/brand/og-default.jpg'),
        ]);
    }

    public function mentionsLegales(): View
    {
        return view('vitrine.mentions-legales', [
            'title'       => 'Mentions légales · Dlo Azur Piscines',
            'description' => 'Mentions légales du site Dlo Azur Piscines — informations légales, hébergeur et coordonnées.',
            'canonical'   => url('/mentions-legales'),
            'ogImage'     => asset('assets/brand/og-default.jpg'),
        ]);
    }

    public function cgv(): View
    {
        return view('vitrine.cgv', [
            'title'       => 'Conditions générales de vente · Dlo Azur Piscines',
            'description' => 'Conditions générales de vente applicables aux prestations de Dlo Azur Piscines en Martinique.',
            'canonical'   => url('/cgv'),
            'ogImage'     => asset('assets/brand/og-default.jpg'),
        ]);
    }

    public function confidentialite(): View
    {
        return view('vitrine.confidentialite', [
            'title'       => 'Politique de confidentialité · Dlo Azur Piscines',
            'description' => 'Politique de confidentialité de Dlo Azur Piscines — données personnelles, hébergement EU, RGPD.',
            'canonical'   => url('/confidentialite'),
            'ogImage'     => asset('assets/brand/og-default.jpg'),
        ]);
    }

    public function eauVerteUrgence(): View
    {
        return view('vitrine.services.eau-verte-urgence', [
            'title'       => "Traitement eau verte d'urgence · Dlo Azur Piscines",
            'description' => "Votre piscine est verte ? Intervention sous 48h en Martinique. Eau claire garantie en 5 à 7 jours. Devis gratuit sur WhatsApp.",
            'canonical'   => url('/services/eau-verte-urgence'),
            'ogImage'     => asset('assets/brand/photos/avant-apres.jpg'),
        ]);
    }
}
