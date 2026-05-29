<?php

namespace App\Support\SchemaOrg;

use Spatie\SchemaOrg\Schema;

/**
 * BreadcrumbList JSON-LD builder (D-10, D-11).
 *
 * Builds a <script type="application/ld+json"> BreadcrumbList block.
 * Consumed by VitrineController methods via per-method DI; emitted ONCE
 * through the $breadcrumbJsonLd slot in layouts/app.blade.php.
 *
 * Blade pages MUST NOT @push('head') this variable — doing so creates a
 * second BreadcrumbList <script> and trips Google's duplicate-structured-data flag.
 */
final class BreadcrumbSchema
{
    /**
     * Build a BreadcrumbList JSON-LD script tag.
     *
     * @param array<int, array{name: string, url: string}> $crumbs
     *   Each entry: ['name' => 'Services', 'url' => url('/services')]
     *   Last entry's url is the current page canonical.
     */
    public function toScript(array $crumbs): string
    {
        $items = [];
        foreach ($crumbs as $position => $crumb) {
            $items[] = Schema::listItem()
                ->position($position + 1)
                ->name($crumb['name'])
                ->item($crumb['url']);
        }

        return Schema::breadcrumbList()->itemListElement($items)->toScript();
    }
}
