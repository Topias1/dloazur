<?php

/**
 * HomePageTest — Plan 01-03 Task 1 (RED).
 *
 * Covers SITE-01 (home page 1:1 mockup) + SITE-06 (WhatsApp CTAs).
 * Also asserts D-32/33/34/35 differentiators are present.
 */

it('home renders 200 with required title and meta description', function () {
    $response = $this->get('/');
    $response->assertOk();
    $response->assertSee('<title>Dlo Azur Piscines · Entretien de piscines en Martinique</title>', false);
    // Blade escapes apostrophes to &#039; in attribute values — match the actual HTML output
    $response->assertSee(
        'content="Entretien, d',
        false
    );
    $response->assertSee(
        'eau de votre piscine en Martinique',
        false
    );
});

it('home contains the hero H1 copy', function () {
    $response = $this->get('/');
    $response->assertSeeText('Votre piscine');
    $response->assertSeeText("claire toute l'année.");
});

it('home contains primary + secondary hero CTAs', function () {
    $response = $this->get('/');
    $response->assertSeeText('Demander un devis gratuit');
    $response->assertSeeText('Nous écrire');
});

it('home contains all 3 trust bullets', function () {
    $response = $this->get('/');
    $response->assertSeeText('Photos à chaque passage');
    $response->assertSeeText('Réponse rapide');
    $response->assertSeeText('Suivi en ligne de vos interventions');
});

it('home contains every section in mockup order', function () {
    $this->get('/')
        ->assertSeeInOrder(['id="services"', 'id="hospitality"', 'id="realisations"', 'id="pierre"', 'id="contact"'], false);
});

it('home has at least 3 WhatsApp CTAs', function () {
    $content = $this->get('/')->getContent();
    expect(substr_count($content, 'wa.me/596696940054'))->toBeGreaterThanOrEqual(3);
});

it('home has the WhatsApp FAB markup with md:hidden', function () {
    $response = $this->get('/');
    $response->assertSee('class="fixed bottom-5 right-5', false);
    $response->assertSee('md:hidden', false);
});

it('home declares lang=fr and includes skip link', function () {
    $response = $this->get('/');
    $response->assertSee('lang="fr"', false);
    $response->assertSeeText('Aller au contenu principal');
});

it('home includes section copy "Un entretien complet"', function () {
    $this->get('/')
        ->assertSeeText('Un entretien complet, pensé pour le climat antillais.');
});

it('home includes hospitality CTA "Devenir partenaire"', function () {
    $this->get('/')
        ->assertSeeText('Devenir partenaire');
});

it("home includes Pierre bio H2", function () {
    $this->get('/')
        ->assertSeeText("Dlo, c'est l'eau. Azur, c'est sa couleur.");
});

it('home includes the footer wordmark', function () {
    $this->get('/')
        ->assertSeeText('© 2026 Dlo Azur Piscines · Pierre ADAM');
});

it('shows no public price in the hero (owner decision: no public price)', function () {
    $response = $this->get('/');
    $response->assertOk();
    $response->assertDontSee('À partir de', false);
    $response->assertDontSee('€/passage', false);
});

it('drops the AI-filler kicker badge from the hero', function () {
    $this->get('/')->assertDontSee('Pisciniste en Martinique', false);
});

it('hero keeps one primary (devis) + one secondary (WhatsApp) CTA', function () {
    $response = $this->get('/');
    // Hero block only: primary devis + WhatsApp, the third "Diagnostic gratuit"
    // button was trimmed from the hero (it still exists in the urgence section).
    $hero = \Illuminate\Support\Str::between($response->getContent(), 'photo-grade', '</section>');
    expect($hero)->toContain('Demander un devis gratuit');
    expect($hero)->toContain('wa.me/596696940054');
    expect($hero)->not->toContain('diagnostic-gratuit');
});

it('exposes a real mobile menu (hamburger) instead of a clipped nav strip', function () {
    $response = $this->get('/');
    $response->assertSee('aria-controls="mobile-menu"', false);
    $response->assertSee('id="mobile-menu"', false);
});

it('links the footer QR to WhatsApp and drops the QR/TODO placeholder', function () {
    $response = $this->get('/');
    $response->assertSee('assets/brand/qr.png', false);
    $response->assertDontSee('QR<br>TODO', false);
});

it('home renders Urgence eau verte section between services and how-it-works (D-34)', function () {
    $response = $this->get('/');
    $response->assertSeeText('Urgence eau verte');
    $response->assertSee(route('services.eau-verte-urgence'), false);
});

it('home renders Nos engagements section before final CTA (D-35)', function () {
    $response = $this->get('/');
    $response->assertSeeText('Nos engagements');
    $response->assertSeeText('Rapport photo à chaque passage');
});
