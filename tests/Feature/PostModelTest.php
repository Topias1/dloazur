<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('slug auto-generates from title on create', function () {
    $post = Post::create([
        'title' => 'Mon Super Article',
        'body'  => 'Contenu du test.',
    ]);

    expect($post->slug)->toBe('mon-super-article');
});

it('slug is not overwritten when already provided', function () {
    $post = Post::create([
        'title' => 'Titre quelconque',
        'slug'  => 'slug-custom',
        'body'  => 'Contenu du test.',
    ]);

    expect($post->slug)->toBe('slug-custom');
});

it('scopePublished excludes draft rows', function () {
    Post::create(['title' => 'Brouillon', 'body' => 'Texte.', 'status' => 'draft']);

    $results = Post::published()->get();

    expect($results)->toHaveCount(0);
});

it('scopePublished includes published rows', function () {
    Post::create(['title' => 'Article publié', 'body' => 'Texte.', 'status' => 'published']);
    Post::create(['title' => 'Autre brouillon', 'body' => 'Texte.', 'status' => 'draft']);

    $results = Post::published()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toBe('Article publié');
});

it('getFirstMediaUrl cover returns empty string when no cover media exists', function () {
    $post = Post::create(['title' => 'Sans couverture', 'body' => 'Texte.']);

    // HasMedia must be wired: getFirstMediaUrl should return '' when no media is attached.
    expect($post->getFirstMediaUrl('cover', 'thumbnail'))->toBe('');
});
