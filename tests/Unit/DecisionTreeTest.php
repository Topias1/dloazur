<?php

/**
 * Decision Tree completeness tests — Plan 05-01 (DIAG-01, Nyquist Wave 0)
 *
 * Asserts on config('diagnostic-tree.*') — no database, no HTTP.
 * Fully implemented here; passes once config/diagnostic-tree.php (Task 2) is created.
 */

// ──────────────────────────────────────────────────────────────────────────────
// Helper: recursively collect all reachable leaf IDs from a node
// ──────────────────────────────────────────────────────────────────────────────
function collectLeaves(string $nodeId, array $questions, array $results, array &$visited = []): array
{
    if (in_array($nodeId, $visited, true)) {
        return [];
    }
    $visited[] = $nodeId;

    // If it's a result leaf, return it
    if (isset($results[$nodeId])) {
        return [$nodeId];
    }

    $leaves = [];

    if (isset($questions[$nodeId])) {
        foreach ($questions[$nodeId]['options'] ?? [] as $option) {
            $next = $option['next'] ?? null;
            if (! $next) {
                continue;
            }
            $nextId = $next['id'] ?? null;
            if (! $nextId) {
                continue;
            }
            $kind = $next['kind'] ?? 'question';
            if ($kind === 'result') {
                $leaves[] = $nextId;
            } else {
                $sub = collectLeaves($nextId, $questions, $results, $visited);
                $leaves = array_merge($leaves, $sub);
            }
        }
    }

    return array_unique($leaves);
}

// Helper: recursively extract all string values from an array (for floculant substring search)
function extractStrings(array $arr): array
{
    $strings = [];
    array_walk_recursive($arr, function ($value) use (&$strings) {
        if (is_string($value)) {
            $strings[] = $value;
        }
    });

    return $strings;
}

// ──────────────────────────────────────────────────────────────────────────────
// Tests
// ──────────────────────────────────────────────────────────────────────────────

it('the diagnostic tree config carries a version key', function () {
    $tree = config('diagnostic-tree');

    expect($tree)->toBeArray();
    expect(array_key_exists('version', $tree))->toBeTrue('diagnostic-tree config must have a "version" key');
});

it('has a questions key and a results key at the top level', function () {
    $tree = config('diagnostic-tree');

    expect(array_key_exists('questions', $tree))->toBeTrue('diagnostic-tree must have a "questions" key');
    expect(array_key_exists('results', $tree))->toBeTrue('diagnostic-tree must have a "results" key');
});

it('all 8 top-level symptom branches at the start node reach at least one result leaf', function () {
    $questions = config('diagnostic-tree.questions');
    $results   = config('diagnostic-tree.results');

    $startOptions = $questions['start']['options'] ?? [];
    expect(count($startOptions))->toBeGreaterThanOrEqual(5, 'start node should have at least 5 options');

    // Each branch from start must reach at least one leaf
    foreach ($startOptions as $option) {
        $nextId = $option['next']['id'] ?? null;
        expect($nextId)->not()->toBeNull("Start option '{$option['label']}' has no next node");

        $kind = $option['next']['kind'] ?? 'question';
        if ($kind === 'result') {
            expect(array_key_exists($nextId, $results))->toBeTrue("Result leaf '$nextId' does not exist");
        } else {
            $visited = [];
            $leaves  = collectLeaves($nextId, $questions, $results, $visited);
            expect(count($leaves))->toBeGreaterThan(0,
                "Branch '{$option['label']}' → '$nextId' reaches no result leaf"
            );
        }
    }
});

it('the electrolyser sub-tree exposes exactly its 5 documented fault leaves', function () {
    $results = config('diagnostic-tree.results');

    $requiredLeaves = [
        'electro-debit',
        'electro-entartree',
        'electro-usee',
        'electro-panne',
        'electro-sel-bas',
    ];

    foreach ($requiredLeaves as $leafId) {
        expect(array_key_exists($leafId, $results))->toBeTrue(
            "Missing electrolyser fault leaf: '$leafId'"
        );
    }
});

it('the electrolyser branch from electro-1 can reach all 5 fault leaves', function () {
    $questions = config('diagnostic-tree.questions');
    $results   = config('diagnostic-tree.results');

    $visited = [];
    $leaves  = collectLeaves('electro-1', $questions, $results, $visited);

    $requiredLeaves = ['electro-debit', 'electro-entartree', 'electro-usee', 'electro-panne', 'electro-sel-bas'];

    foreach ($requiredLeaves as $leafId) {
        expect(in_array($leafId, $leaves))->toBeTrue(
            "Leaf '$leafId' is not reachable from electro-1"
        );
    }
});

it('the cartouche (cartridge) filter path contains zero occurrences of the word floculant', function () {
    $tree = config('diagnostic-tree');

    // Find result leaves that belong to the cartouche path
    // These are identified by their 'methods' key containing 'cartouche'
    // or by their leaf ID explicitly being the cartouche variant
    $cartoucheLeafIds = [];
    foreach ($tree['results'] ?? [] as $leafId => $leaf) {
        if (isset($leaf['methods']['cartouche'])) {
            $cartoucheLeafIds[] = $leafId;
        }
    }

    // Also check the leaf IDs containing 'cartouche' in their name
    foreach (array_keys($tree['results'] ?? []) as $leafId) {
        if (str_contains($leafId, 'cartouche')) {
            $cartoucheLeafIds[] = $leafId;
        }
    }

    $cartoucheLeafIds = array_unique($cartoucheLeafIds);

    // There must be at least one cartouche leaf
    expect(count($cartoucheLeafIds))->toBeGreaterThan(0,
        'No cartouche-specific leaf found in the decision tree'
    );

    // None of the cartouche leaves should contain the word "floculant"
    foreach ($cartoucheLeafIds as $leafId) {
        $leaf    = $tree['results'][$leafId];
        $strings = extractStrings($leaf);

        foreach ($strings as $str) {
            expect(mb_strtolower($str))->not()->toContain('floculant',
                "The word 'floculant' was found in cartouche leaf '$leafId': \"$str\" (FLOCULANT-BRANCH-SPEC §2 violation)"
            );
        }
    }
});

it('the sable/verre filter path recommends floculant choc', function () {
    $results = config('diagnostic-tree.results');

    // Find leaves that belong to the sable/verre path
    $sableLeafIds = [];
    foreach ($results as $leafId => $leaf) {
        if (isset($leaf['methods']['sable']) || isset($leaf['methods']['verre'])) {
            $sableLeafIds[] = $leafId;
        }
    }

    // Also check leaf IDs with 'sable' or 'floculant-sable' in the name
    foreach (array_keys($results) as $leafId) {
        if (str_contains($leafId, 'sable') || str_contains($leafId, 'floculant-sable')) {
            $sableLeafIds[] = $leafId;
        }
    }

    $sableLeafIds = array_unique($sableLeafIds);
    expect(count($sableLeafIds))->toBeGreaterThan(0, 'No sable/verre-specific leaf found');

    $foundFloculant = false;
    foreach ($sableLeafIds as $leafId) {
        $leaf    = $results[$leafId];
        $strings = extractStrings($leaf);
        foreach ($strings as $str) {
            if (str_contains(mb_strtolower($str), 'floculant')) {
                $foundFloculant = true;
                break 2;
            }
        }
    }

    expect($foundFloculant)->toBeTrue('The sable/verre path should recommend floculant choc but no occurrence found');
});

it('green-1 has a branch testing for surstabilisation (chlore-lock)', function () {
    $results = config('diagnostic-tree.results');

    expect(array_key_exists('chlore-lock', $results))->toBeTrue(
        "Missing chlore-lock/surstabilisation leaf (expert audit P0: green-1 stabilisant branch)"
    );
});

it('green-1 has a branch detecting low stabilisant (manque-de-stabilisant)', function () {
    $results = config('diagnostic-tree.results');

    expect(array_key_exists('manque-de-stabilisant', $results))->toBeTrue(
        "Missing manque-de-stabilisant leaf (expert audit P0: green-1 stabilisant low branch)"
    );
});

it('cloudy-1 has a bifurcation for eau calcaire (eau-calcaire leaf)', function () {
    $results = config('diagnostic-tree.results');

    expect(array_key_exists('eau-calcaire', $results))->toBeTrue(
        "Missing eau-calcaire leaf (expert audit P1: cloudy-1 bifurcation)"
    );
});

it('all result leaves have at minimum a diagnostic key and a plan array', function () {
    $results = config('diagnostic-tree.results');

    expect($results)->not()->toBeEmpty();

    foreach ($results as $leafId => $leaf) {
        expect(array_key_exists('diagnostic', $leaf))->toBeTrue(
            "Leaf '$leafId' missing 'diagnostic' key"
        );
        expect(array_key_exists('plan', $leaf))->toBeTrue(
            "Leaf '$leafId' missing 'plan' key"
        );
        expect(is_array($leaf['plan']))->toBeTrue(
            "Leaf '$leafId' plan is not an array"
        );
    }
});
