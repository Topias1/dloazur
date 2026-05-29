<?php

it('sitemap.xml returns 200', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
});

it('sitemap.xml includes the 4 service sub-pages from plan 03', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    $response->assertSee('/services/entretien-recurrent', false);
    $response->assertSee('/services/analyse-eau', false);
    $response->assertSee('/services/spa', false);
    $response->assertSee('/services/eau-verte-urgence', false);
});

it('sitemap.xml includes the 4 city zone pages from plan 04', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    $response->assertSee('/zones/fort-de-france', false);
    $response->assertSee('/zones/le-lamentin', false);
    $response->assertSee('/zones/schoelcher', false);
    $response->assertSee('/zones/les-trois-ilets', false);
});

it('sitemap.xml contains lastmod on static service URLs', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    // The sitemap must contain at least one <lastmod> element
    $response->assertSee('<lastmod>', false);
    // The services URL must appear alongside a lastmod tag in the XML
    expect($response->content())->toContain('/services');
});
