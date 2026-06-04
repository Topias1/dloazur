<?php

namespace App\Http\Controllers;

use App\Support\SchemaOrg\BreadcrumbSchema;
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

    public function eauVerteUrgence(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.services.eau-verte-urgence', [
            'title'            => "Traitement eau verte d'urgence · Dlo Azur Piscines",
            'description'      => "Votre piscine est verte ? Intervention sous 48h en Martinique. Eau claire garantie en 5 à 7 jours. Devis gratuit sur WhatsApp.",
            'canonical'        => url('/services/eau-verte-urgence'),
            'ogImage'          => asset('assets/brand/photos/avant-apres.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',          'url' => url('/')],
                ['name' => 'Services',         'url' => url('/services')],
                ['name' => 'Eau verte urgence','url' => url('/services/eau-verte-urgence')],
            ]),
        ]);
    }

    public function entretienRecurrent(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.services.entretien-recurrent', [
            'title'            => 'Entretien régulier de piscine en Martinique · Dlo Azur Piscines',
            'description'      => "Forfaits d'entretien hebdomadaire, bimensuel ou à la demande pour votre piscine en Martinique. Eau saine, équipements vérifiés, zéro contrainte.",
            'canonical'        => url('/services/entretien-recurrent'),
            'ogImage'          => asset('assets/brand/photos/entretien-dos-logo.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',            'url' => url('/')],
                ['name' => 'Services',           'url' => url('/services')],
                ['name' => 'Entretien récurrent','url' => url('/services/entretien-recurrent')],
            ]),
        ]);
    }

    public function analyseEau(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.services.analyse-eau', [
            'title'            => "Analyse de l'eau de piscine en Martinique · Dlo Azur Piscines",
            'description'      => "Analyse complète de l'eau de votre piscine en Martinique : pH, chlore, TAC, sel, alcalinité. Ajustement professionnel pour une eau saine et cristalline.",
            'canonical'        => url('/services/analyse-eau'),
            'ogImage'          => asset('assets/brand/photos/hero-pierre-piscine.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',         'url' => url('/')],
                ['name' => 'Services',        'url' => url('/services')],
                ['name' => "Analyse de l'eau",'url' => url('/services/analyse-eau')],
            ]),
        ]);
    }

    public function spa(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.services.spa', [
            'title'            => 'Entretien de spa en Martinique · Dlo Azur Piscines',
            'description'      => 'Entretien et dépannage de spa et jacuzzi en Martinique. Traitement de l\'eau, vérification des équipements, eau saine garantie. Service sur mesure.',
            'canonical'        => url('/services/spa'),
            'ogImage'          => asset('assets/brand/og-default.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil', 'url' => url('/')],
                ['name' => 'Services','url' => url('/services')],
                ['name' => 'Spa',     'url' => url('/services/spa')],
            ]),
        ]);
    }

    public function depannage(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.services.depannage', [
            'title'            => 'Dépannage piscine en Martinique · Dlo Azur Piscines',
            'description'      => 'Panne de pompe, filtration HS, eau trouble : dépannage rapide en Martinique. Contactez Dlo Azur sur WhatsApp pour une intervention le jour même.',
            'canonical'        => url('/services/depannage'),
            'ogImage'          => asset('assets/brand/og-default.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',    'url' => url('/')],
                ['name' => 'Services',   'url' => url('/services')],
                ['name' => 'Dépannage',  'url' => url('/services/depannage')],
            ]),
        ]);
    }

    // ───────────────────────────────────────────────────────────
    // City hub pages — Plan 999.1-04 (D-12)
    // 2-level breadcrumb: Accueil › [Commune]
    // ───────────────────────────────────────────────────────────

    public function fortDeFrance(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.zones.fort-de-france', [
            'title'            => 'Pisciniste à Fort-de-France · Dlo Azur Piscines',
            'description'      => 'Entretien et dépannage de piscines à Fort-de-France, Martinique. Intervention rapide, eau saine garantie. Devis gratuit pour particuliers et villas.',
            'canonical'        => url('/zones/fort-de-france'),
            'ogImage'          => asset('assets/brand/og-default.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',        'url' => url('/')],
                ['name' => 'Fort-de-France', 'url' => url('/zones/fort-de-france')],
            ]),
        ]);
    }

    public function leLamentin(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.zones.le-lamentin', [
            'title'            => 'Pisciniste au Lamentin · Dlo Azur Piscines',
            'description'      => 'Entretien et dépannage de piscines au Lamentin, Martinique. Service sur mesure pour particuliers, villas et résidences. Devis gratuit.',
            'canonical'        => url('/zones/le-lamentin'),
            'ogImage'          => asset('assets/brand/og-default.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',    'url' => url('/')],
                ['name' => 'Le Lamentin','url' => url('/zones/le-lamentin')],
            ]),
        ]);
    }

    public function schoelcher(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.zones.schoelcher', [
            'title'            => 'Pisciniste à Schoelcher · Dlo Azur Piscines',
            'description'      => 'Entretien et dépannage de piscines à Schoelcher, Martinique. Traitement de l\'eau, maintenance et dépannage équipements. Devis gratuit.',
            'canonical'        => url('/zones/schoelcher'),
            'ogImage'          => asset('assets/brand/og-default.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',   'url' => url('/')],
                ['name' => 'Schoelcher','url' => url('/zones/schoelcher')],
            ]),
        ]);
    }

    public function lesTroisIlets(BreadcrumbSchema $breadcrumb): View
    {
        return view('vitrine.zones.les-trois-ilets', [
            'title'            => 'Pisciniste aux Trois-Îlets · Dlo Azur Piscines',
            'description'      => 'Entretien et dépannage de piscines aux Trois-Îlets, Martinique. Service premium pour villas et propriétés de prestige. Devis gratuit.',
            'canonical'        => url('/zones/les-trois-ilets'),
            'ogImage'          => asset('assets/brand/og-default.jpg'),
            'breadcrumbJsonLd' => $breadcrumb->toScript([
                ['name' => 'Accueil',      'url' => url('/')],
                ['name' => 'Les Trois-Îlets','url' => url('/zones/les-trois-ilets')],
            ]),
        ]);
    }
}
