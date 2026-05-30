<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Support\BlogRepository;
use Illuminate\Database\Seeder;

/**
 * PostMigrationSeeder — Idempotent migration of the 3 canonical .md blog articles to the posts table.
 *
 * Per D-05/D-06: NOT registered in DatabaseSeeder (which is env-gated to local/testing).
 * Safe to re-run: updateOrCreate keyed on slug yields exactly 1 row per slug.
 * Production-safe: existing rows are updated with the same data, never duplicated.
 *
 * Run via: php artisan db:seed --class=PostMigrationSeeder --force
 *
 * The 3 canonical slugs:
 *   - bienvenue-dlo-azur
 *   - de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines
 *   - les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique
 *
 * Cover stays null for all 3 articles — SEO og-default.jpg fallback covers it (D-05).
 * Resources/content/blog/*.md are kept as backup (D-06: config flag 'source' controls read path).
 */
class PostMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $repo = new BlogRepository();

        $files = [
            resource_path('content/blog/2026-05-bienvenue-dlo-azur.md'),
            resource_path('content/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines.md'),
            resource_path('content/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique.md'),
        ];

        foreach ($files as $path) {
            // Reuse the now-public BlogRepository::parse() — preserves byte-for-byte
            // title, slug, date (Carbon), show_date, excerpt, author, body, reading_time.
            $parsed = $repo->parse($path);

            Post::updateOrCreate(
                ['slug' => $parsed['slug']],
                [
                    'title'     => $parsed['title'],
                    'body'      => $parsed['body'],
                    'excerpt'   => $parsed['excerpt'] ?: null,
                    'author'    => $parsed['author'],
                    // Carbon date → date string (column cast is 'date')
                    'date'      => $parsed['date']->toDateString(),
                    'show_date' => $parsed['show_date'],
                    'status'    => 'published',
                    // cover stays null — og-default.jpg fallback in buildArticleSchema()
                ]
            );
        }
    }
}
