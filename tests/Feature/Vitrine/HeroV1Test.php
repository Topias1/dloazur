<?php

/**
 * HeroV1Test — Plan 08-01 Task 1 (RED → Task 2 makes GREEN).
 *
 * Covers V1 geo-honest corrections:
 * - D-01 : Hero 3rd person (no "ma tournée")
 * - D-03 : Invitation to call preserved ("Un appel suffit")
 * - D-04 : Purge "toute la Martinique" from service pages
 */

it('hero is in 3rd person: no occurrence of "ma tournée" in GET /', function () {
    $this->get('/')->assertDontSee('ma tournée', false);
});

it('hero contains "notre zone" in GET /', function () {
    $this->get('/')->assertSee('notre zone', false);
});

it('hero contains call-to-call invitation "Un appel suffit" in GET /', function () {
    $this->get('/')->assertSee('Un appel suffit', false);
});

it('GET /services/entretien-recurrent does not contain "toute la Martinique"', function () {
    $this->get('/services/entretien-recurrent')->assertDontSee('toute la Martinique', false);
});

it('GET /services/analyse-eau does not contain "toute la Martinique"', function () {
    $this->get('/services/analyse-eau')->assertDontSee('toute la Martinique', false);
});
