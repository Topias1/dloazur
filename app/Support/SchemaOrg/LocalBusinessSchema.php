<?php

namespace App\Support\SchemaOrg;

use Spatie\SchemaOrg\MultiTypedEntity;
use Spatie\SchemaOrg\Schema;

/**
 * LocalBusiness JSON-LD builder — LocalBusiness+HomeAndConstructionBusiness multi-type (D-01, supersedes D-26).
 *
 * Returns the <script type="application/ld+json"> tag for the home page.
 * Uses config + helpers (asset, url) so staging/prod URLs resolve correctly.
 *
 * Deliberately omits:
 *   - streetAddress / postalCode — SAB hides its address (D-04)
 *   - aggregateRating — gated on real GBP reviews (D-05)
 *   - hasOfferCatalog — second wave, post-GBP (D-06)
 */
final class LocalBusinessSchema
{
    public function toScript(): string
    {
        $mte = new MultiTypedEntity();

        $mte->localBusiness(function (\Spatie\SchemaOrg\LocalBusiness $biz) {
            $biz
                ->name('Dlo Azur Piscines')
                ->image(asset('assets/brand/photos/hero-pierre-piscine.jpg'))
                ->url(url('/'))
                ->telephone('+596696940054')
                ->email('contact@dloazurpiscines.com')
                ->priceRange('€€')
                ->founder(Schema::person()->name('Pierre ADAM'))
                ->sameAs([]) // GBP URL added later per D-09
                ->address(
                    Schema::postalAddress()
                        ->addressCountry('FR')
                        ->addressRegion('Martinique')
                        ->addressLocality('Fort-de-France')
                    // NO streetAddress — SAB hides address (D-04)
                    // NO postalCode — SAB hides address (D-04)
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
        });

        // Adds HomeAndConstructionBusiness as second @type (D-01)
        // Use chained method form — NOT ->add() which throws TypeAlreadyInMultiTypedEntity
        $mte->homeAndConstructionBusiness();

        return $mte->toScript();
    }
}
