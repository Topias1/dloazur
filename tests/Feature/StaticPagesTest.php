<?php

/**
 * StaticPagesTest — Plan 01-03 Task 1 (RED).
 *
 * Covers SITE-02 (services), SITE-03 (realisations), legal pages + contact shell.
 */

it('services page renders 200 with own title', function () {
    $response = $this->get('/services');
    $response->assertOk();
    $response->assertSee('<title>Services', false);
});

it('realisations page renders 200 with own title', function () {
    $response = $this->get('/realisations');
    $response->assertOk();
    $response->assertSee('<title>Réalisations', false);
});

it('legal pages return 200', function () {
    $this->get('/mentions-legales')->assertOk();
    $this->get('/cgv')->assertOk();
    $this->get('/confidentialite')->assertOk();
});

it('contact page renders 200 with form shell ready for Plan 04', function () {
    $response = $this->get('/contact');
    $response->assertOk();
    $response->assertSeeText('Nous contacter');
    // livewire:contact-form is in the Blade source with class_exists guard;
    // until Plan 04 registers the component it renders the fallback placeholder.
    // Assert either the actual livewire tag OR the fallback text is visible.
    $content = $response->getContent();
    // Either: (a) Plan 04 registered the component → Livewire emits wire:submit / wire:id
    // or (b) ContactForm class missing → fallback placeholder "Formulaire en cours de chargement"
    $hasLivewire = str_contains($content, 'wire:submit') || str_contains($content, 'wire:id=');
    $hasFallback = str_contains($content, 'Formulaire en cours de chargement');
    expect($hasLivewire || $hasFallback)->toBeTrue(
        'contact page must render either the wired Livewire form (wire:submit) or its fallback placeholder'
    );
});

it('robots.txt is reachable and contains Sitemap directive', function () {
    $response = $this->get('/robots.txt');
    $response->assertOk();
    $response->assertSee('Sitemap:', false);
});

it('eau-verte-urgence dedicated page exists with WhatsApp CTA and Service JSON-LD (D-34)', function () {
    $response = $this->get('/services/eau-verte-urgence');
    $response->assertOk();
    $response->assertSeeText("Traitement eau verte d'urgence");
    $response->assertSee('wa.me/596696940054', false);
    $response->assertSee('"@type": "Service"', false);
});
