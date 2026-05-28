<?php

/**
 * Walking Skeleton smoke tests — Plan 01-01 Task 3b behavior contract.
 *
 * Asserts the home route returns a styled placeholder page with brand identity,
 * WhatsApp CTA, French locale, Vite-emitted stylesheet, and skip link.
 */

it('GET / returns 200 with HTML content-type', function () {
    $response = $this->get('/');
    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
});

it('home page sets the canonical page title', function () {
    $this->get('/')
        ->assertSee('<title>Dlo Azur Piscines · Entretien de piscines en Martinique</title>', false);
});

it('home page exposes the WhatsApp CTA with the canonical phone number', function () {
    $this->get('/')
        ->assertSee('wa.me/596696940054', false);
});

it('home page wires Vite stylesheet (compiled from resources/css/app.css)', function () {
    // Vite rewrites the source path to a build hash (e.g. /build/assets/app-XXXX.css);
    // we assert the @vite directive produced the link, not the source path itself.
    $body = $this->get('/')->getContent();
    expect($body)->toMatch('#/build/assets/app-[A-Za-z0-9]+\.css#');
});

it('home page declares <html lang="fr">', function () {
    $this->get('/')
        ->assertSee('lang="fr"', false);
});

it('home page includes the accessible skip link before <main>', function () {
    $this->get('/')
        ->assertSeeInOrder(['Aller au contenu principal', '<main', '</main>'], false);
});
