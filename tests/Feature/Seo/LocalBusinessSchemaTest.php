<?php

/**
 * LocalBusinessSchemaTest — Plan 999.1-01 Task 1 (RED).
 *
 * Locks the multi-type LocalBusiness+HomeAndConstructionBusiness JSON-LD
 * per D-01..D-05. Written against the target builder, fails RED because the
 * current builder emits @type:"Plumber" and lacks founder/email.
 */

use function Pest\Laravel\get;

/**
 * Helper: fetch home page JSON-LD, extract the script block containing "Dlo Azur Piscines",
 * and return the decoded array.
 */
function getHomeJsonLd(): array
{
    $content = get('/')->getContent();

    // Match ALL application/ld+json script blocks (home may have more than one)
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches);

    foreach ($matches[1] as $raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && isset($decoded['name']) && $decoded['name'] === 'Dlo Azur Piscines') {
            return $decoded;
        }
    }

    return [];
}

it('home JSON-LD @type is an array containing LocalBusiness and HomeAndConstructionBusiness', function () {
    $json = getHomeJsonLd();

    expect($json)->not->toBeEmpty('Expected JSON-LD script tag for "Dlo Azur Piscines" in home page');
    expect($json['@type'])->toBeArray('D-01: @type must be an array, not a string');
    expect($json['@type'])->toContain('LocalBusiness');
    expect($json['@type'])->toContain('HomeAndConstructionBusiness');
});

it('home JSON-LD @type is NOT the string Plumber', function () {
    $json = getHomeJsonLd();

    expect($json)->not->toBeEmpty();
    expect($json['@type'])->not->toBe('Plumber', 'D-01 supersedes D-26: Plumber must be gone');
});

it('home JSON-LD has founder Pierre ADAM (D-02)', function () {
    $json = getHomeJsonLd();

    expect($json)->not->toBeEmpty();
    expect($json)->toHaveKey('founder');
    expect($json['founder']['name'])->toBe('Pierre ADAM');
});

it('home JSON-LD has email contact@dloazurpiscines.com (D-03)', function () {
    $json = getHomeJsonLd();

    expect($json)->not->toBeEmpty();
    expect($json)->toHaveKey('email');
    expect($json['email'])->toBe('contact@dloazurpiscines.com');
});

it('home JSON-LD address has no streetAddress and no postalCode (D-04)', function () {
    $json = getHomeJsonLd();

    expect($json)->not->toBeEmpty();
    expect($json)->toHaveKey('address');

    $address = $json['address'];
    expect($address)->not->toHaveKey('streetAddress', 'D-04: SAB must not mark up streetAddress');
    expect($address)->not->toHaveKey('postalCode', 'D-04: SAB must not mark up postalCode');
    expect($address['addressLocality'])->toBe('Fort-de-France');
    expect($address['addressRegion'])->toBe('Martinique');
    expect($address['addressCountry'])->toBe('FR');
});

it('home JSON-LD does NOT contain aggregateRating (D-05)', function () {
    $json = getHomeJsonLd();

    expect($json)->not->toBeEmpty();
    expect($json)->not->toHaveKey('aggregateRating', 'D-05: must not add aggregateRating before real GBP reviews exist');
});
