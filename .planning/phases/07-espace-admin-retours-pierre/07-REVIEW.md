---
phase: 07
status: issues_found
critical: 0
warning: 3
info: 2
reviewed_at: 2026-06-03
---

## Summary

Phase 7 delivers: `notes_privees` bug fix (migration + upsert + model), `frequence_jour` on piscines, pivot `passage_produit` with produit sync API, agenda du jour view, recap mensuel view, and 3 new test files. The privacy invariant (notes_privees absent from portail) is correctly enforced — PassageTimeline query and all portail Blade templates contain no reference to the field. The upsert SQL uses named bindings, not string interpolation. No critical issues found.

Three warnings and two info items follow.

## Findings

### Warning

#### WR-01: PassageProduitController performs N+1 Produit queries inside the sync loop

**File:** `app/Http/Controllers/Api/PassageProduitController.php:33-38`

**Issue:** `Produit::find($item['produit_id'])` is called inside a `foreach`, issuing one SELECT per product. With 10 products that is 10 queries; with 50 it becomes noticeable. The list of `produit_id` values is already known from validation so they can be fetched in one shot.

**Fix:**
```php
$produitIds = array_column($data['produits'] ?? [], 'produit_id');
$produits   = Produit::whereIn('id', $produitIds)->get()->keyBy('id');

$sync = [];
foreach ($data['produits'] ?? [] as $item) {
    $sync[$item['produit_id']] = [
        'quantite'      => $item['quantite'] ?? null,
        'prix_snapshot' => $produits[$item['produit_id']]?->prix_ht,
    ];
}
```

---

#### WR-02: PassageProduitController does not guard closed passages — produits can be synced on a clos passage

**File:** `app/Http/Controllers/Api/PassageProduitController.php:29-42`

**Issue:** The `store` method fetches the passage by `client_uuid` and calls `sync()` unconditionally, regardless of `status`. If the passage has already been closed (`status != 'draft'`), the product pivot is still overwritten. The passage upsert enforces `WHERE status = 'draft'` (D-38/D-40), but the produit sync endpoint does not mirror this guard. An operator retrying after a 409 on the passage could still alter the pivot of a closed record.

**Fix:**
```php
$passage = Passage::where('client_uuid', $data['passage_client_uuid'])->firstOrFail();

if ($passage->status !== 'draft') {
    return response()->json(['error' => 'already_closed'], 409);
}

$passage->produits()->sync($sync);
```

---

#### WR-03: AgendaController "à revoir" query has no upper bound — old notes never expire from the flag list

**File:** `app/Http/Controllers/Admin/AgendaController.php:33-38`

**Issue:** The query uses `visited_at >= now()->subDays(7)` with no upper bound on `notes_privees` age. The intent is "recent flags", but the 7-day window only constrains `visited_at`, not when the note was written. More practically: a note added to a passage from last week stays visible indefinitely once it passes the 7-day mark — the operator has no way to dismiss a flag short of clearing `notes_privees`. There is no `reviewed` / `vu` flag. This is a UX dead-end rather than a crash, but it will accumulate stale entries as usage grows. Consider either a `notes_privees_seen_at` column or limiting the window to 30 days with a "marquer vu" action.

**Fix (minimal — extend window and document the known gap):**
```php
// Extend to 30 days to reduce noise; a "marquer vu" action is the real fix (Phase 3).
->where('visited_at', '>=', Carbon::now()->subDays(30))
```

---

### Info

#### IN-01: RecapMensuelController — chimie aggregation done in Blade PHP, not in the model/controller

**File:** `resources/views/admin/recap/index.blade.php:63-87`

**Issue:** The `@php` block inside the `@foreach` aggregates pivot data using a `collect()` loop per client. This is business logic in a view — it cannot be unit-tested and will silently break if the produit relationship changes. Move the aggregation to a method on the `Client` model or a view-model helper.

**Fix:** Extract to a private method in `RecapMensuelController` that returns `$clients` with a `chimie` attribute already computed, or add a `chimieConsommeeSur(Carbon $debut, Carbon $fin)` method to the Client model.

---

#### IN-02: AgendaTest repeats AdminSeeder bootstrap with copy-pasted putenv blocks

**File:** `tests/Feature/AgendaTest.php:29-33`, `71-75`, `99-103`

**Issue:** All three tests duplicate the same 4-line `putenv` + `AdminSeeder::run()` pattern. If the seeder contract changes (env key renamed, etc.) all three must be updated. Extract to a `beforeEach` or a shared helper function matching the pattern already used in `PassageNotesPriveesTest` (`makeAdminNP()`).

**Fix:**
```php
// At top of AgendaTest.php
function makeAdminAgenda(): User
{
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();
    return User::where('email', 'admin@dloazurtest.local')->first();
}
```
