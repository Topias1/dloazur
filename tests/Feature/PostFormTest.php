<?php

/**
 * PostFormTest — blog admin write component (Phase 06, Plan 04, CONTENT-01).
 *
 * Covers PostForm create/edit behaviour:
 *  - create: title+body submit → Post row, slug = Str::slug(title), default draft
 *  - mount(postId): hydrates title/slug/body/excerpt/status (NOT cover)
 *  - slug-lock-on-publish: editing a published post never overwrites the persisted slug
 *  - cache flush: submit after a status change calls Cache::forget('blog.index')
 *  - validation: missing title or body → errors, no row created
 *  - cover MIME: a non-image upload is rejected by the image|mimes rule
 *
 * Cover persistence uses Storage::fake('s3') so $cover->store('livewire-tmp','s3')
 * and addMediaFromDisk(...,'s3') stay deterministic without a real Scaleway bucket.
 */

use App\Livewire\PostForm;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

// ─── Create ─────────────────────────────────────────────────────────────────

it('creates a post from title + body with an auto slug and draft status', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'Entretien de printemps')
        ->set('body', 'Contenu Markdown de l’article.')
        ->call('submit')
        ->assertHasNoErrors();

    $post = Post::firstWhere('slug', 'entretien-de-printemps');

    expect($post)->not->toBeNull()
        ->and($post->title)->toBe('Entretien de printemps')
        ->and($post->body)->toBe('Contenu Markdown de l’article.')
        ->and($post->status)->toBe('draft');
});

// ─── Mount / hydrate (edit) ────────────────────────────────────────────────

it('mount hydrates an existing post fields but not cover', function () {
    $post = Post::create([
        'title'   => 'Titre existant',
        'slug'    => 'titre-existant',
        'body'    => 'Corps existant.',
        'excerpt' => 'Extrait.',
        'status'  => 'published',
    ]);

    Livewire::test(PostForm::class, ['postId' => $post->id])
        ->assertSet('title', 'Titre existant')
        ->assertSet('slug', 'titre-existant')
        ->assertSet('body', 'Corps existant.')
        ->assertSet('excerpt', 'Extrait.')
        ->assertSet('status', 'published')
        ->assertSet('cover', null);
});

// ─── Slug lock on publish ────────────────────────────────────────────────────

it('keeps the persisted slug locked when editing a published post', function () {
    $post = Post::create([
        'title'  => 'Article publié',
        'slug'   => 'article-publie',
        'body'   => 'Corps.',
        'status' => 'published',
    ]);

    Livewire::test(PostForm::class, ['postId' => $post->id])
        // Attempt to tamper with the slug while published.
        ->set('slug', 'slug-pirate')
        ->set('title', 'Article publié modifié')
        ->call('submit')
        ->assertHasNoErrors();

    expect($post->fresh()->slug)->toBe('article-publie');
});

// ─── Cache flush on status change ─────────────────────────────────────────────

it('flushes the blog.index cache on submit', function () {
    Cache::put('blog.index', ['stale'], 3600);

    Livewire::test(PostForm::class)
        ->set('title', 'Nouvel article publié')
        ->set('body', 'Corps.')
        ->set('status', 'published')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Cache::has('blog.index'))->toBeFalse();
});

// ─── Validation ───────────────────────────────────────────────────────────────

it('rejects a submit with a missing title', function () {
    Livewire::test(PostForm::class)
        ->set('title', '')
        ->set('body', 'Corps présent.')
        ->call('submit')
        ->assertHasErrors(['title' => 'required']);

    expect(Post::count())->toBe(0);
});

it('rejects a submit with a missing body', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'Titre présent')
        ->set('body', '')
        ->call('submit')
        ->assertHasErrors(['body' => 'required']);

    expect(Post::count())->toBe(0);
});

// ─── Cover upload ─────────────────────────────────────────────────────────────

it('rejects a cover whose type is not an allowed image mime', function () {
    Storage::fake('s3');

    // A .gif IS an image (so Livewire's preview-mime guard accepts the upload),
    // but it is NOT in the allowed list jpg,jpeg,png,webp — so the #[Validate]
    // mimes rule must reject it on submit. This exercises the validation rule
    // itself rather than Livewire's pre-validation preview guard.
    Livewire::test(PostForm::class)
        ->set('title', 'Avec gif interdit')
        ->set('body', 'Corps.')
        ->set('cover', UploadedFile::fake()->image('animation.gif', 600, 600))
        ->call('submit')
        ->assertHasErrors(['cover']);

    expect(Post::whereSlug('avec-gif-interdit')->exists())->toBeFalse();
});

it('persists an image cover to the cover media collection on s3', function () {
    Storage::fake('s3');

    Livewire::test(PostForm::class)
        ->set('title', 'Avec couverture')
        ->set('body', 'Corps.')
        ->set('cover', UploadedFile::fake()->image('cover.jpg', 1200, 630))
        ->call('submit')
        ->assertHasNoErrors();

    $post = Post::firstWhere('slug', 'avec-couverture');

    expect($post)->not->toBeNull()
        ->and($post->getMedia('cover'))->toHaveCount(1);
});
