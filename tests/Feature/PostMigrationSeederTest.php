<?php

use App\Models\Post;
use Database\Seeders\PostMigrationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Worktree: explicitly bind TestCase since Pest.php->in('Feature') resolves against main repo root.
// Strip `uses(\Tests\TestCase::class)` on merge (tests/Pest.php already covers Feature/).
uses(\Tests\TestCase::class, RefreshDatabase::class);

it('seeder lands exactly 3 posts with the canonical slugs', function () {
    (new PostMigrationSeeder())->run();

    expect(Post::count())->toBe(3);

    $slugs = Post::pluck('slug')->sort()->values()->all();

    expect($slugs)->toContain('bienvenue-dlo-azur');
    expect($slugs)->toContain('de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines');
    expect($slugs)->toContain('les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique');
});

it('seeder is idempotent — running it twice still yields 3 rows', function () {
    (new PostMigrationSeeder())->run();
    (new PostMigrationSeeder())->run();

    expect(Post::count())->toBe(3);
});

it('migrated posts have status published', function () {
    (new PostMigrationSeeder())->run();

    $allPublished = Post::where('status', 'published')->count();

    expect($allPublished)->toBe(3);
});

it('migrated posts preserve title and default author Pierre ADAM', function () {
    (new PostMigrationSeeder())->run();

    $post = Post::where('slug', 'bienvenue-dlo-azur')->firstOrFail();

    expect($post->title)->toBe('Bienvenue chez Dlo Azur Piscines');
    expect($post->author)->toBe('Pierre ADAM');
});

it('migrated posts preserve date and show_date from front matter', function () {
    (new PostMigrationSeeder())->run();

    $post = Post::where('slug', 'bienvenue-dlo-azur')->firstOrFail();

    expect($post->date)->not->toBeNull();
    // The bienvenue article has date: 2026-05-28
    expect($post->date->toDateString())->toBe('2026-05-28');
    // show_date defaults to true in front matter for this article
    expect($post->show_date)->toBeTrue();
});
