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

it('home contains the single primary hero CTA (devis)', function () {
    $response = $this->get('/');
    $response->assertSeeText('Demander un devis gratuit');
    // Le bouton secondaire « Nous écrire » a été retiré du hero (feedback Pierre :
    // redondant avec le devis). WhatsApp reste dans la nav, le footer et le CTA final.
});

it('home contains the trust bullets (photo claim removed)', function () {
    $response = $this->get('/');
    $response->assertSeeText('Réponse rapide');
    $response->assertSeeText('Suivi en ligne de vos interventions');
    // « Photos à chaque passage » retiré : Pierre ne prend pas de photo systématiquement.
    $response->assertDontSeeText('Photos à chaque passage');
});

it('home contains every section in order', function () {
    $this->get('/')
        ->assertSeeInOrder(['id="services"', 'id="avant-apres"', 'id="hospitality"', 'id="pierre"', 'id="contact"'], false);
});

it('home no longer renders the removed sections (feedback Pierre)', function () {
    $response = $this->get('/');
    $response->assertDontSee('id="realisations"', false);   // « Nos chantiers » retiré
    $response->assertDontSeeText('Urgence eau verte');       // section dédiée retirée
    $response->assertDontSeeText("Simple, du premier message"); // how-it-works retiré
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
        ->assertSeeText('Un entretien complet, pour une eau toujours claire.');
    // « pensé pour le climat antillais » retiré (feedback Pierre).
    $this->get('/')->assertDontSeeText('pensé pour le climat antillais');
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

it('hero keeps a single primary (devis) CTA, no secondary WhatsApp button', function () {
    $response = $this->get('/');
    // Isolate the hero <section> (it ends at the wave SVG's </section>).
    $hero = \Illuminate\Support\Str::before(
        \Illuminate\Support\Str::after($response->getContent(), 'min-h-[92vh]'),
        '</section>'
    );
    expect($hero)->toContain('Demander un devis gratuit');
    // Le bouton WhatsApp secondaire a été retiré du hero (feedback Pierre).
    expect($hero)->not->toContain('wa.me/596696940054');
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

it('home renders the animated avant/après section linking to the eau-verte page', function () {
    $response = $this->get('/');
    $response->assertSee('id="avant-apres"', false);
    $response->assertSeeText("D'une eau verte à une eau de baignade.");
    $response->assertSee(route('services.eau-verte-urgence'), false);
});

it('home renders Nos engagements section before final CTA (D-35)', function () {
    $response = $this->get('/');
    $response->assertSeeText('Nos engagements');
    $response->assertSeeText('Compte-rendu après chaque passage');
});
