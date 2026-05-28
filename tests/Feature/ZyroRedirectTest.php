<?php

declare(strict_types=1);

/**
 * Zyro legacy URL redirect verification (D-24 — Plan 01-06).
 *
 * Phase 1 inherits 7 indexed URLs from the old Zyro site. 5 legacy URLs still
 * 301-redirect to their Phase 1 equivalents. The 2 article slugs are no longer
 * redirected — they are served as real blog posts (SEO recovery).
 *
 * Inventory captured 2026-05-28 from https://dloazurpiscines.com/sitemap.xml.
 * Full mapping table + SEO recovery in:
 *   .planning/phases/01-vitrine-fondations/ZYRO-URL-INVENTORY.md
 */

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

dataset('zyro_redirects', [
    ['/services-et-nettoyage',                               '/services'],
    ['/nos-realisations',                                    '/realisations'],
    ['/blog-list-nettoyage-piscine-professionnel',           '/blog'],
    ['/page-article-blog-vierge',                            '/blog'],
    // Typo variant (missing trailing 's') → canonical article slug
    [
        '/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine',
        '/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines',
    ],
]);

it('redirects legacy Zyro URL with 301 to its Phase 1 equivalent', function (string $from, string $to) {
    $response = $this->get($from);
    $response->assertStatus(301);
    $response->assertRedirect($to);
})->with('zyro_redirects');

it('recovered Zyro article — Pierre histoire — responds 200 with stable content', function () {
    $response = $this->get('/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines');
    $response->assertOk();
    $response->assertSeeText('Dlo Azur');
});

it('recovered Zyro article — 3 étapes entretien — responds 200 with stable content', function () {
    $response = $this->get('/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique');
    $response->assertOk();
    $response->assertSeeText('entretien');
});
