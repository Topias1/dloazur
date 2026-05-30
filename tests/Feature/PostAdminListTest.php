<?php

/**
 * PostAdminListTest — blog admin CRUD shell (Phase 06, Plan 03).
 *
 * Covers:
 *  - Auth gate: unauthenticated → 302 redirect to /login
 *  - Auth gate: authenticated → 200 on /admin/blog
 *  - PostIndex list: shows all posts (draft + published) with badge labels
 *  - PostIndex list: empty state when no posts
 *  - PostIndex list: search filters by title
 */

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ─── Auth gate ────────────────────────────────────────────────────────────────

it('admin blog redirects unauthenticated users to login', function () {
    $response = $this->get('/admin/blog');

    $response->assertRedirect('/login');
});

it('admin blog returns 200 for authenticated operator', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/blog');

    $response->assertStatus(200);
});

// ─── PostIndex Livewire list ───────────────────────────────────────────────────

it('PostIndex renders all posts including drafts', function () {
    User::factory()->create();
    $published = Post::factory()->create(['title' => 'Article Publié', 'status' => 'published']);
    $draft     = Post::factory()->create(['title' => 'Article Brouillon', 'status' => 'draft']);

    Livewire::test(\App\Livewire\PostIndex::class)
        ->assertSee($published->title)
        ->assertSee($draft->title);
});

it('PostIndex shows Publié badge label for published post', function () {
    Post::factory()->create(['title' => 'Article publié', 'status' => 'published']);

    Livewire::test(\App\Livewire\PostIndex::class)
        ->assertSee('Publié');
});

it('PostIndex shows Brouillon badge label for draft post', function () {
    Post::factory()->create(['title' => 'Article brouillon', 'status' => 'draft']);

    Livewire::test(\App\Livewire\PostIndex::class)
        ->assertSee('Brouillon');
});

it('PostIndex shows empty state copy when no posts', function () {
    Livewire::test(\App\Livewire\PostIndex::class)
        ->assertSee('Aucun article pour');
});

it('PostIndex filters by title on search', function () {
    Post::factory()->create(['title' => 'Entretien piscine', 'status' => 'published']);
    Post::factory()->create(['title' => 'Traitement eau', 'status' => 'published']);

    Livewire::test(\App\Livewire\PostIndex::class)
        ->set('search', 'Entretien')
        ->assertSee('Entretien piscine')
        ->assertDontSee('Traitement eau');
});

it('PostIndex shows no-result state on empty search', function () {
    Post::factory()->create(['title' => 'Entretien piscine', 'status' => 'published']);

    Livewire::test(\App\Livewire\PostIndex::class)
        ->set('search', 'inexistant-xyz')
        ->assertSee('Aucun résultat pour');
});
