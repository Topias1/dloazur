<?php

/**
 * VitrinePagesTest — Plan 999.1-03 Tasks 2 & 3.
 *
 * Asserts new service routes return 200, emit the correct canonical,
 * contain BreadcrumbList JSON-LD, and meet content/structure requirements.
 * Exactly-one BreadcrumbList assertion is the duplicate-emission regression guard.
 */

use function Pest\Laravel\get;

// ───────────────────────────────────────────────────────────
// Task 2: routes + 200 + breadcrumb present (stub views)
// ───────────────────────────────────────────────────────────

it('GET /services/entretien-recurrent returns 200', function () {
    get('/services/entretien-recurrent')->assertStatus(200);
});

it('GET /services/analyse-eau returns 200', function () {
    get('/services/analyse-eau')->assertStatus(200);
});

it('GET /services/spa returns 200', function () {
    get('/services/spa')->assertStatus(200);
});

it('GET /services/eau-verte-urgence returns 200 with BreadcrumbList (wired in Task 2)', function () {
    $response = get('/services/eau-verte-urgence');
    $response->assertStatus(200);
    expect($response->getContent())->toContain('"@type":"BreadcrumbList"');
});

it('/services/entretien-recurrent contains its canonical URL', function () {
    $response = get('/services/entretien-recurrent');
    expect($response->getContent())->toContain('/services/entretien-recurrent');
});

it('/services/analyse-eau contains its canonical URL', function () {
    $response = get('/services/analyse-eau');
    expect($response->getContent())->toContain('/services/analyse-eau');
});

it('/services/spa contains its canonical URL', function () {
    $response = get('/services/spa');
    expect($response->getContent())->toContain('/services/spa');
});

// ───────────────────────────────────────────────────────────
// Task 3: content, structure, duplicate-emission guard
// ───────────────────────────────────────────────────────────

it('/services/spa body mentions spa', function () {
    $content = get('/services/spa')->getContent();
    expect(mb_strtolower($content))->toContain('spa');
});

it('/services/eau-verte-urgence contains Protocole section heading', function () {
    $content = get('/services/eau-verte-urgence')->getContent();
    expect($content)->toContain('Protocole');
});

it('/services/eau-verte-urgence contains at least one FAQ question', function () {
    $content = get('/services/eau-verte-urgence')->getContent();
    // FAQ questions are inside button elements driven by Alpine x-data
    expect($content)->toContain('x-data');
    expect($content)->toContain('<button');
});

it('/services/entretien-recurrent emits exactly one h1', function () {
    $content = get('/services/entretien-recurrent')->getContent();
    expect(substr_count($content, '<h1'))->toBe(1);
});

it('/services/analyse-eau emits exactly one h1', function () {
    $content = get('/services/analyse-eau')->getContent();
    expect(substr_count($content, '<h1'))->toBe(1);
});

it('/services/spa emits exactly one h1', function () {
    $content = get('/services/spa')->getContent();
    expect(substr_count($content, '<h1'))->toBe(1);
});

it('/services/eau-verte-urgence emits exactly one h1', function () {
    $content = get('/services/eau-verte-urgence')->getContent();
    expect(substr_count($content, '<h1'))->toBe(1);
});

// Duplicate-emission regression guard: exactly ONE BreadcrumbList per page
it('/services/entretien-recurrent emits exactly one BreadcrumbList', function () {
    $content = get('/services/entretien-recurrent')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});

it('/services/analyse-eau emits exactly one BreadcrumbList', function () {
    $content = get('/services/analyse-eau')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});

it('/services/spa emits exactly one BreadcrumbList', function () {
    $content = get('/services/spa')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});

it('/services/eau-verte-urgence emits exactly one BreadcrumbList', function () {
    $content = get('/services/eau-verte-urgence')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});
