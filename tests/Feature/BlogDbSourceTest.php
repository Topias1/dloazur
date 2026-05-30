<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Support\BlogRepository;

uses(RefreshDatabase::class);

// -------------------------------------------------------------------------
// Task 1: BlogRepository DB read path (config/blog.php + cacheablePayloadFromDb)
// -------------------------------------------------------------------------

it('BlogRepository::all() with db source returns only published posts ordered by date desc', function () {
    config()->set('blog.source', 'db');

    $early = Post::create([
        'title'     => 'Article ancien',
        'slug'      => 'article-ancien',
        'body'      => 'Corps de l\'article ancien.',
        'excerpt'   => 'Extrait ancien.',
        'status'    => 'published',
        'author'    => 'Pierre ADAM',
        'date'      => '2024-01-01',
        'show_date' => true,
    ]);

    $recent = Post::create([
        'title'     => 'Article récent',
        'slug'      => 'article-recent',
        'body'      => 'Corps de l\'article récent.',
        'excerpt'   => 'Extrait récent.',
        'status'    => 'published',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-06-01',
        'show_date' => true,
    ]);

    Post::create([
        'title'     => 'Brouillon',
        'slug'      => 'brouillon',
        'body'      => 'Corps du brouillon.',
        'excerpt'   => 'Extrait brouillon.',
        'status'    => 'draft',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-07-01',
        'show_date' => true,
    ]);

    $all = (new BlogRepository())->all();

    // Only published posts
    expect($all)->toHaveCount(2);
    expect($all->pluck('slug')->all())->toBe(['article-recent', 'article-ancien']);
});

it('BlogRepository::all() with db source excludes draft posts', function () {
    config()->set('blog.source', 'db');

    Post::create([
        'title'  => 'Published',
        'slug'   => 'published-post',
        'body'   => 'Body.',
        'status' => 'published',
        'date'   => '2025-01-01',
    ]);

    Post::create([
        'title'  => 'Draft',
        'slug'   => 'draft-post',
        'body'   => 'Body.',
        'status' => 'draft',
        'date'   => '2025-02-01',
    ]);

    $all = (new BlogRepository())->all();

    $slugs = $all->pluck('slug')->all();
    expect($slugs)->toContain('published-post');
    expect($slugs)->not->toContain('draft-post');
});

it('cacheablePayloadFromDb returns a plain array surviving serializable_classes=false round-trip', function () {
    config()->set('blog.source', 'db');

    Post::create([
        'title'     => 'Mon article',
        'slug'      => 'mon-article',
        'body'      => 'Contenu de test avec des mots pour le reading_time.',
        'excerpt'   => 'Un extrait.',
        'status'    => 'published',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-05-01',
        'show_date' => true,
    ]);

    $repo    = new BlogRepository();
    $payload = $repo->cacheablePayloadFromDb();

    expect($payload)->toBeArray()->not->toBeEmpty();

    // Must survive unserialize with allowed_classes=false (no objects in cache)
    $roundTripped = unserialize(serialize($payload), ['allowed_classes' => false]);
    expect($roundTripped)->toEqual($payload);

    foreach ($roundTripped as $post) {
        expect($post)->toBeArray();
        expect($post['date'])->toBeString(); // Carbon flattened to ISO-8601 string
        expect($post['cover'] === null || is_string($post['cover']))->toBeTrue();
    }
});

it('cacheablePayloadFromDb array keys exactly match the file-path shape (title..filepath=null)', function () {
    config()->set('blog.source', 'db');

    Post::create([
        'title'     => 'Shape test',
        'slug'      => 'shape-test',
        'body'      => 'Un peu de contenu pour calculer le temps de lecture.',
        'excerpt'   => 'Extrait.',
        'status'    => 'published',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-04-01',
        'show_date' => true,
    ]);

    $repo    = new BlogRepository();
    $payload = $repo->cacheablePayloadFromDb();

    expect($payload)->not->toBeEmpty();

    $post = $payload[0];
    $expectedKeys = ['title', 'slug', 'date', 'show_date', 'excerpt', 'author', 'cover', 'body', 'reading_time', 'filepath'];

    foreach ($expectedKeys as $key) {
        expect($post)->toHaveKey($key);
    }

    // filepath must be null on the DB path (no file on disk)
    expect($post['filepath'])->toBeNull();

    // reading_time must be a positive integer
    expect($post['reading_time'])->toBeInt()->toBeGreaterThanOrEqual(1);
});

// -------------------------------------------------------------------------
// Task 2: BlogController 410-vs-404 + sitemap published-only
// -------------------------------------------------------------------------

it('GET /blog/{published-slug} returns 200 with og:type article and Article JSON-LD when source=db', function () {
    config()->set('blog.source', 'db');

    Post::create([
        'title'     => 'Article publié',
        'slug'      => 'article-publie',
        'body'      => "# Titre\n\nCorps de l'article.",
        'excerpt'   => 'Un article publié.',
        'status'    => 'published',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-05-15',
        'show_date' => true,
    ]);

    $response = $this->get('/blog/article-publie');

    $response->assertStatus(200);
    $response->assertSee('og:type', false);
    $response->assertSee('content="article"', false);
    $response->assertSee('"@type":"Article"', false);
});

it('GET /blog/{draft-slug-in-db} returns 410 when source=db', function () {
    config()->set('blog.source', 'db');

    Post::create([
        'title'     => 'Brouillon interne',
        'slug'      => 'brouillon-interne',
        'body'      => 'Corps du brouillon.',
        'excerpt'   => 'Extrait.',
        'status'    => 'draft',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-05-20',
        'show_date' => true,
    ]);

    $response = $this->get('/blog/brouillon-interne');

    $response->assertStatus(410);
});

it('GET /blog/never-existed returns 404 when source=db', function () {
    config()->set('blog.source', 'db');

    $response = $this->get('/blog/this-slug-never-existed-at-all');

    $response->assertStatus(404);
});

it('sitemap.xml lists published slug but NOT draft slug when source=db', function () {
    config()->set('blog.source', 'db');

    Post::create([
        'title'     => 'Article visible',
        'slug'      => 'article-visible',
        'body'      => 'Corps visible.',
        'excerpt'   => 'Extrait.',
        'status'    => 'published',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-06-01',
        'show_date' => true,
    ]);

    Post::create([
        'title'     => 'Article masqué',
        'slug'      => 'article-masque',
        'body'      => 'Corps masqué.',
        'excerpt'   => 'Extrait.',
        'status'    => 'draft',
        'author'    => 'Pierre ADAM',
        'date'      => '2025-06-02',
        'show_date' => true,
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    $response->assertSee('article-visible', false);
    $response->assertDontSee('article-masque', false);
});
