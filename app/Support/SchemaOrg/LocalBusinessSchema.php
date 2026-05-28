<?php

namespace App\Support\SchemaOrg;

use Spatie\SchemaOrg\Schema;

/**
 * LocalBusiness JSON-LD builder — Plumber sub-type (D-26).
 *
 * Returns the <script type="application/ld+json"> tag for the home page.
 * Uses config + helpers (asset, url) so staging/prod URLs resolve correctly.
 */
final class LocalBusinessSchema
{
    public function toScript(): string
    {
        $schema = Schema::plumber()
            ->name('Dlo Azur Piscines')
            ->image(asset('assets/brand/photos/hero-pierre-piscine.jpg'))
            ->url(url('/'))
            ->telephone('+596696940054')
            ->priceRange('€€')
            ->sameAs([])
            ->address(
                Schema::postalAddress()
                    ->addressCountry('FR')
                    ->addressRegion('Martinique')
                    ->addressLocality('Fort-de-France')
            )
            ->geo(
                Schema::geoCoordinates()
                    ->latitude(14.6037)
                    ->longitude(-61.0594)
            )
            ->areaServed([
                Schema::city()->name('Fort-de-France'),
                Schema::city()->name('Le Lamentin'),
                Schema::city()->name('Schoelcher'),
                Schema::city()->name('Les Trois-Îlets'),
                Schema::administrativeArea()->name('Martinique'),
            ])
            ->openingHoursSpecification([
                Schema::openingHoursSpecification()
                    ->dayOfWeek(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                    ->opens('08:00')
                    ->closes('17:00'),
                Schema::openingHoursSpecification()
                    ->dayOfWeek('Saturday')
                    ->opens('09:00')
                    ->closes('12:00'),
            ]);

        // toScript() already returns the full <script type="application/ld+json">...</script> tag.
        return $schema->toScript();
    }
}
