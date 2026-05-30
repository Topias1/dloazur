<?php

/**
 * Decision Tree completeness tests — Plan 05-01 (DIAG-01, Nyquist Wave 0)
 *
 * Asserts on config('diagnostic-tree.*') — no database, no HTTP.
 * Fully implemented here; will pass once config/diagnostic-tree.php (Task 2) is created.
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
            if (!$next) {
                continue;
            }
            $nextId = $next['id'] ?? null;
            if (!$nextId) {
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

    // Also handle sub-branches (nested 'options' at any level)
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

    expect($tree)->toBeArray()
        ->and($tree)->toHaveKey('version');
});

it('has a questions key and a results key at the top level', function () {
    $tree = config('diagnostic-tree');

    expect($tree)->toHaveKey('questions')
        ->and($tree)->toHaveKey('results');
});

it('all 8 top-level symptom problems at the start node reach at least one result leaf', function () {
    $questions = config('diagnostic-tree.questions');
    $results   = config('diagnostic-tree.results');

    // The 8 top-level symptoms per RESEARCH + EXPERT-AUDIT
    $expectedTopLevel = [
        'green-1',   // Eau verte
        'cloudy-1',  // Eau trouble
        'brown-1',   // Eau marron
        'clear-1',   // Eau claire mais problème
        'electro-1', // Problème d'électrolyseur
    ];

    $startOptions = $questions['start']['options'] ?? [];
    expect(count($startOptions))->toBeGreaterThanOrEqual(5);

    // Each branch from start must reach at least one leaf
    foreach ($startOptions as $option) {
        $nextId = $option['next']['id'] ?? null;
        expect($nextId)->not()->toBeNull("Start option '{$option['label']}' has no next node");

        $kind = $option['next']['kind'] ?? 'question';
        if ($kind === 'result') {
            expect($results)->toHaveKey($nextId);
        } else {
            $visited = [];
            $leaves = collectLeaves($nextId, $questions, $results, $visited);
            expect(count($leaves))->toBeGreaterThan(0,
                "Branch '{$option['label']}' → '$nextId' reaches no result leaf"
            );
        }
    }
});

it('the electrolyser sub-tree exposes exactly its 5 documented fault leaves', function () {
    $results = config('diagnostic-tree.results');

    $requiredElectroLeaves = [
        'electro-debit',
        'electro-entartree',
        'electro-usee',
        'electro-panne',
        'electro-sel-bas',
    ];

    foreach ($requiredElectroLeaves as $leafId) {
        expect($results)->toHaveKey($leafId,
            "Missing electrolyser fault leaf: '$leafId'"
        );
    }
});

it('the electrolyser branch from electro-1 can reach all 5 fault leaves', function () {
    $questions = config('diagnostic-tree.questions');
    $results   = config('diagnostic-tree.results');

    $visited = [];
    $leaves = collectLeaves('electro-1', $questions, $results, $visited);

    $requiredLeaves = ['electro-debit', 'electro-entartree', 'electro-usee', 'electro-panne', 'electro-sel-bas'];

    foreach ($requiredLeaves as $leafId) {
        expect($leaves)->toContain($leafId,
            "Leaf '$leafId' is not reachable from electro-1"
        );
    }
});

it('the cartouche (cartridge) filter path contains zero occurrences of the word floculant', function () {
    $tree = config('diagnostic-tree');

    // Find the cartouche sub-branch within the tree
    // It must live under cloudy-1 → filter-type node → cartouche branch
    // We locate it by finding any key/sub-array explicitly representing the cartouche path

    // Recursive search: find any array node that represents the cartouche branch
    // The floculant string must NOT appear in any string value within the cartouche sub-array
    $cartouchePath = findCartoucheBranch($tree);

    expect($cartouchePath)->not()->toBeNull('Could not locate the cartouche sub-branch in the decision tree');

    $strings = extractStrings($cartouchePath);
    foreach ($strings as $str) {
        expect(mb_strtolower($str))->not()->toContain('floculant',
            "The word 'floculant' was found in the cartouche path: \"$str\""
        );
    }
});

it('the sable/verre filter path recommends floculant choc', function () {
    $tree = config('diagnostic-tree');

    $sablePath = findSableBranch($tree);
    expect($sablePath)->not()->toBeNull('Could not locate the sable/verre sub-branch in the decision tree');

    $strings = extractStrings($sablePath);
    $found = false;
    foreach ($strings as $str) {
        if (str_contains(mb_strtolower($str), 'floculant')) {
            $found = true;
            break;
        }
    }
    expect($found)->toBeTrue('The sable/verre path should recommend floculant choc but no occurrence found');
});

it('green-1 has a branch testing for surstabilisation (chlore-lock)', function () {
    $results = config('diagnostic-tree.results');

    // Per expert audit P0: green-1 must test stabilisant → chlore-lock leaf
    expect($results)->toHaveKey('chlore-lock',
        "Missing chlore-lock/surstabilisation leaf (expert audit P0: green-1 stabilisant branch)"
    );
});

it('green-1 has a branch detecting low stabilisant (manque-de-stabilisant)', function () {
    $results = config('diagnostic-tree.results');

    expect($results)->toHaveKey('manque-de-stabilisant',
        "Missing manque-de-stabilisant leaf (expert audit P0: green-1 stabilisant low branch)"
    );
});

it('cloudy-1 has a bifurcation for eau calcaire (eau-calcaire leaf)', function () {
    $results = config('diagnostic-tree.results');

    expect($results)->toHaveKey('eau-calcaire',
        "Missing eau-calcaire leaf (expert audit P1: cloudy-1 bifurcation)"
    );
});

it('all result leaves have at minimum a diagnostic key and a plan array', function () {
    $results = config('diagnostic-tree.results');

    expect($results)->not()->toBeEmpty();

    foreach ($results as $leafId => $leaf) {
        expect($leaf)->toHaveKey('diagnostic',
            "Leaf '$leafId' missing 'diagnostic' key"
        );
        expect($leaf)->toHaveKey('plan',
            "Leaf '$leafId' missing 'plan' key"
        );
        expect($leaf['plan'])->toBeArray(
            "Leaf '$leafId' plan is not an array"
        );
    }
});

// ──────────────────────────────────────────────────────────────────────────────
// Internal helpers for branch searching
// ──────────────────────────────────────────────────────────────────────────────

function findCartoucheBranch(array $tree): ?array
{
    // The cartouche branch is stored under questions as the options array
    // for the filter-type node (e.g. 'filter-type' node option with value 'cartouche')
    // We search recursively for an array key or option label matching 'cartouche'
    // and return that sub-array
    return searchBranchByKey($tree, 'cartouche');
}

function findSableBranch(array $tree): ?array
{
    return searchBranchByKey($tree, 'sable');
}

function searchBranchByKey(array $arr, string $filterType): ?array
{
    // Look for an option whose 'filter_type' or 'value' matches $filterType
    // or whose 'label' contains $filterType
    foreach ($arr as $key => $value) {
        if (!is_array($value)) {
            continue;
        }

        // Check if this element represents the target filter type
        if (
            (isset($value['filter_type']) && $value['filter_type'] === $filterType) ||
            (isset($value['value']) && $value['value'] === $filterType) ||
            (isset($value['label']) && str_contains(mb_strtolower((string) $value['label']), $filterType))
        ) {
            return $value;
        }

        // Also check for keys named by filter type directly (e.g. 'methods' => ['cartouche' => [...]])
        if ($key === $filterType && is_array($value)) {
            return $value;
        }

        $found = searchBranchByKey($value, $filterType);
        if ($found !== null) {
            return $found;
        }
    }

    return null;
}
