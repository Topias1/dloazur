<?php

/**
 * CallCenterVoixTest — Plan 08-01 Task 1 (stub RED).
 *
 * These tests FAIL intentionally until Plan 03 applies V12/V14 brand voice corrections.
 * Covers V12: ≤2 call-center/standard mentions, V14: "Notre approche" section present,
 * "Nos engagements" heading removed.
 */

it('home call-center occurrences ≤ 2', function () {
    $content = $this->get('/')->getContent();
    $count = substr_count($content, 'call-center')
        + substr_count($content, "centre d'appel")
        + substr_count($content, 'standard');
    expect($count)->toBeLessThanOrEqual(2);
});

it('home contains Notre approche section heading', function () {
    $this->get('/')->assertSee('Notre approche', false);
});

it('home does not contain Nos engagements heading', function () {
    $this->get('/')->assertDontSee('Nos engagements', false);
});
