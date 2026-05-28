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
    $response->assertSee('livewire:contact-form', false);
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
