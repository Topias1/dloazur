<?php

/**
 * DepannageRouteTest — Plan 08-01 Task 1 (stub RED).
 *
 * These tests FAIL intentionally until Plan 02 creates the /services/depannage route.
 * Covers V5 requirements: route 200, h1 Dépannage, WhatsApp CTA, BreadcrumbList JSON-LD,
 * services-grid card link, sitemap entry.
 */

it('GET /services/depannage returns 200', function () {
    $this->get('/services/depannage')->assertStatus(200);
});

it('depannage page contains h1 with Dépannage', function () {
    $this->get('/services/depannage')->assertSee('Dépannage', false);
});

it('depannage page contains WhatsApp CTA link wa.me/596696940054', function () {
    $this->get('/services/depannage')->assertSee('wa.me/596696940054', false);
});

it('depannage page contains BreadcrumbList JSON-LD', function () {
    $this->get('/services/depannage')->assertSee('"@type":"BreadcrumbList"', false);
});

it('depannage card in services-grid links to /services/depannage', function () {
    $this->get('/services')->assertSee(route('services.depannage'), false);
});

it('sitemap.xml includes /services/depannage', function () {
    $this->get('/sitemap.xml')->assertSee('/services/depannage', false);
});
