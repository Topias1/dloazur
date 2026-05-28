<?php

declare(strict_types=1);

/**
 * Zyro legacy URL redirect verification (D-24 — Plan 01-06).
 *
 * Phase 1 inherits 7 indexed URLs from the old Zyro site. Each must 301 to its
 * Phase 1 equivalent so Google's index updates cleanly and external backlinks
 * (Pierre's print flyers, business cards, Google My Business URL) don't break.
 *
 * Inventory captured 2026-05-28 from https://dloazurpiscines.com/sitemap.xml.
 * Full mapping table + SEO recovery options in:
 *   .planning/phases/01-vitrine-fondations/ZYRO-URL-INVENTORY.md
 */

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

dataset('zyro_redirects', [
    ['/services-et-nettoyage',                                                          '/services'],
    ['/nos-realisations',                                                               '/realisations'],
    ['/blog-list-nettoyage-piscine-professionnel',                                      '/blog'],
    ['/page-article-blog-vierge',                                                       '/blog'],
    ['/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines',                '/blog'],
    ['/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine',                 '/blog'],
    ['/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique', '/blog'],
]);

it('redirects legacy Zyro URL with 301 to its Phase 1 equivalent', function (string $from, string $to) {
    $response = $this->get($from);
    $response->assertStatus(301);
    $response->assertRedirect($to);
})->with('zyro_redirects');
