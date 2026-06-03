# Phase 07: Espace admin — agenda, récap chimie & fix notes internes - Pattern Map

**Mapped:** 2026-06-03
**Files analyzed:** 14 new/modified files
**Analogs found:** 14 / 14

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|-------------------|------|-----------|----------------|---------------|
| `database/migrations/…_add_notes_privees_to_passages.php` | migration | CRUD | `database/migrations/2026_05_30_000010_add_lead_columns_to_diagnostics_table.php` | exact |
| `database/migrations/…_add_frequence_to_piscines.php` | migration | CRUD | `database/migrations/2026_05_30_000010_add_lead_columns_to_diagnostics_table.php` | exact |
| `database/migrations/…_create_passage_produit_table.php` | migration | CRUD | `database/migrations/2026_05_28_000003_create_produits_table.php` | role-match |
| `app/Models/Passage.php` (modify) | model | CRUD | self — add `notes_privees` to `$fillable` + `$casts` | exact |
| `app/Models/Piscine.php` (modify) | model | CRUD | self — add `frequence_jour` to `$fillable` | exact |
| `app/Models/Produit.php` (modify) | model | CRUD | `app/Models/Produit.php` — add `passages()` BelongsToMany | role-match |
| `app/Http/Controllers/Api/PassageController.php` (modify) | controller | request-response | self — fix upsert SQL + bindings | exact |
| `resources/js/passage-form.js` (modify) | utility | event-driven | self — add `produits` state + `_toPayload()` extension | exact |
| `app/Http/Controllers/Admin/AgendaController.php` | controller | request-response | `app/Http/Controllers/Admin/DashboardController.php` | role-match |
| `app/Http/Controllers/Admin/RecapMensuelController.php` | controller | request-response | `app/Http/Controllers/Admin/DashboardController.php` | role-match |
| `resources/views/admin/agenda/index.blade.php` | view | request-response | `resources/views/admin/dashboard.blade.php` | role-match |
| `resources/views/admin/recap/index.blade.php` | view | request-response | `resources/views/admin/clients/show.blade.php` | role-match |
| `resources/views/admin/passages/create.blade.php` (modify) | view | event-driven | self — add produits section | exact |
| `app/Http/Controllers/Api/PassageProduitController.php` | controller | request-response | `app/Http/Controllers/Api/PassageController.php` | role-match |

---

## Pattern Assignments

### `database/migrations/…_add_notes_privees_to_passages.php` (migration, CRUD)

**Analog:** `database/migrations/2026_05_30_000010_add_lead_columns_to_diagnostics_table.php`

**Full add-column pattern** (lines 1-32):
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagnostics', function (Blueprint $table) {
            $table->string('prenom', 80)->nullable()->after('created_via');
            $table->string('commune', 80)->nullable()->after('prenom');
            $table->string('email', 160)->nullable()->after('commune');
            $table->string('site_web', 255)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('diagnostics', function (Blueprint $table) {
            $table->dropColumn(['prenom', 'commune', 'email', 'site_web']);
        });
    }
};
```

**Apply to `notes_privees`:** Use `Schema::table('passages', ...)`, add `$table->text('notes_privees')->nullable()->after('notes');`. `down()` drops `['notes_privees']`.

---

### `database/migrations/…_add_frequence_to_piscines.php` (migration, CRUD)

**Analog:** same add-column pattern above.

**Apply:** `Schema::table('piscines', ...)`, add `$table->string('frequence_jour', 16)->nullable()->after('notes');` — a simple string (e.g. `lundi`, `mardi`, `1x_semaine`) matching Claude's Discretion on exact form. `down()` drops `['frequence_jour']`.

---

### `database/migrations/…_create_passage_produit_table.php` (migration, CRUD)

**Analog:** `database/migrations/2026_05_28_000003_create_produits_table.php` (lines 1-28) + foreign key style from `database/migrations/2026_05_28_000005_create_passages_table.php` (lines 16-17).

**Foreign key pattern** (create_passages_table.php lines 16-17):
```php
$table->foreignId('piscine_id')->nullable()->constrained('piscines')->nullOnDelete();
$table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
```

**Create table pattern** (create_produits_table.php):
```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passage_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passage_id')->constrained('passages')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->decimal('quantite', 8, 2)->nullable();
            $table->decimal('prix_snapshot', 10, 2)->nullable(); // prix HT au moment du passage
            $table->timestamps();
            $table->unique(['passage_id', 'produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passage_produit');
    }
};
```

---

### `app/Models/Passage.php` — add `notes_privees` (model, CRUD)

**Analog:** self (lines 16-48).

**Current `$fillable`** (lines 16-34) — add `'notes_privees'` after `'notes'`:
```php
protected $fillable = [
    // ... existing fields ...
    'notes',
    'notes_privees',   // ADD — nullable text, invisible portail
    'pdf_path',
    // ...
];
```

**`$casts`** — `notes_privees` is plain text, no cast needed.

**Relation to add** (after `latestPhoto()`):
```php
public function produits(): BelongsToMany
{
    return $this->belongsToMany(Produit::class, 'passage_produit')
        ->withPivot(['quantite', 'prix_snapshot'])
        ->withTimestamps();
}
```

---

### `app/Models/Piscine.php` — add `frequence_jour` (model, CRUD)

**Analog:** self (lines 14-23).

**`$fillable` addition** — add `'frequence_jour'` after `'notes'`:
```php
protected $fillable = [
    // ... existing fields ...
    'notes',
    'frequence_jour',  // ADD — ex. 'lundi', 'mardi', '1x_semaine'
];
```

---

### `app/Models/Produit.php` — add `passages()` relation (model, CRUD)

**Analog:** `app/Models/Passage.php` BelongsToMany pattern above.

**Add relation:**
```php
public function passages(): BelongsToMany
{
    return $this->belongsToMany(Passage::class, 'passage_produit')
        ->withPivot(['quantite', 'prix_snapshot'])
        ->withTimestamps();
}
```

---

### `app/Http/Controllers/Api/PassageController.php` — fix upsert (controller, request-response)

**Analog:** self (full file, lines 1-125).

**Bug location — SQL INSERT column list** (lines 63-72): `notes_privees` is validated (line 48) but absent from the INSERT column list and from the ON CONFLICT SET list. The fix is a two-place addition.

**INSERT column list** (lines 63-72) — add `notes_privees` after `notes`:
```sql
INSERT INTO passages (
    client_uuid, piscine_id, client_id, visited_at, status,
    ph_avant, ph_apres, chlore_libre, chlore_total, tac, th, sel_g_l,
    actions, notes, notes_privees,          -- ADD notes_privees
    synced_at, created_at, updated_at
)
VALUES (
    :client_uuid, :piscine_id, :client_id, :visited_at, 'draft',
    :ph_avant, :ph_apres, :chlore_libre, :chlore_total, :tac, :th, :sel_g_l,
    :actions, :notes, :notes_privees,       -- ADD :notes_privees
    :synced_at, :created_at, :updated_at
)
```

**ON CONFLICT SET** (lines 73-88) — add `notes_privees = EXCLUDED.notes_privees` after `notes`:
```sql
ON CONFLICT (client_uuid) DO UPDATE SET
    ...
    notes         = EXCLUDED.notes,
    notes_privees = EXCLUDED.notes_privees, -- ADD
    synced_at     = :synced_at2,
    updated_at    = :updated_at2
WHERE passages.status = 'draft'
```

**Bindings array** (lines 92-109) — add `'notes_privees' => $data['notes_privees'] ?? null,` after `'notes'`.

**Validation rule** (line 48) — already present: `'notes_privees' => ['nullable', 'string', 'max:2000']`. No change needed.

---

### `app/Http/Controllers/Api/PassageProduitController.php` (new, controller, request-response)

**Analog:** `app/Http/Controllers/Api/PassageController.php` (lines 1-125).

**Imports pattern** (passageController.php lines 1-9):
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Passage;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
```

**Core pattern** — receives `{ passage_client_uuid, produits: [{ produit_id, quantite }] }`, resolves the passage by `client_uuid`, syncs the pivot. Price snapshot read from `Produit::find($id)->prix_ht`:
```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'passage_client_uuid' => ['required', 'uuid', 'exists:passages,client_uuid'],
        'produits'            => ['nullable', 'array'],
        'produits.*.produit_id' => ['required', 'integer', 'exists:produits,id'],
        'produits.*.quantite'   => ['nullable', 'numeric', 'min:0', 'max:9999'],
    ]);

    $passage = Passage::where('client_uuid', $data['passage_client_uuid'])->firstOrFail();

    // Sync pivot avec prix_snapshot au moment de la synchro
    $sync = [];
    foreach ($data['produits'] ?? [] as $item) {
        $produit = Produit::find($item['produit_id']);
        $sync[$item['produit_id']] = [
            'quantite'       => $item['quantite'] ?? null,
            'prix_snapshot'  => $produit?->prix_ht,
        ];
    }
    $passage->produits()->sync($sync);

    return response()->json(['ok' => true], 200);
}
```

---

### `app/Http/Controllers/Admin/AgendaController.php` (new, controller, request-response)

**Analog:** `app/Http/Controllers/Admin/DashboardController.php` (full file, lines 1-94).

**Imports + structure pattern** (lines 1-10):
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Piscine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
```

**Controller method pattern** (DashboardController lines 22-93):
```php
class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::now()->locale('fr')->isoFormat('dddd'); // 'lundi', 'mardi', …

        // Piscines attendues aujourd'hui (dérivées de frequence_jour)
        $piscinesAujourdhui = Piscine::query()
            ->where('frequence_jour', $today)
            ->with(['client:id,name', 'passages' => fn ($q) => $q->whereDate('visited_at', today())])
            ->get();

        // Flags "à revoir" : passages des 7 derniers jours avec notes_privees non nulles
        // et sans passage aujourd'hui (remonter uniquement ce qui mérite attention)
        $aRevoir = Passage::query()
            ->whereNotNull('notes_privees')
            ->where('visited_at', '>=', Carbon::now()->subDays(7))
            ->with(['client:id,name', 'piscine:id,nom'])
            ->orderByDesc('visited_at')
            ->get();

        return view('admin.agenda.index', compact('piscinesAujourdhui', 'aRevoir'));
    }
}
```

---

### `app/Http/Controllers/Admin/RecapMensuelController.php` (new, controller, request-response)

**Analog:** `app/Http/Controllers/Admin/DashboardController.php` + `app/Livewire/PassageIndex.php`.

**Imports pattern:**
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Passage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
```

**Core query pattern** — aggregate by client for a given month:
```php
public function index(Request $request): View
{
    $mois = $request->integer('mois', now()->month);
    $annee = $request->integer('annee', now()->year);

    $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
    $fin   = $debut->copy()->endOfMonth();

    $clients = Client::query()
        ->withCount(['passages as nb_passages' => fn ($q) =>
            $q->whereBetween('visited_at', [$debut, $fin])
        ])
        ->having('nb_passages', '>', 0)
        ->with(['passages' => fn ($q) =>
            $q->whereBetween('visited_at', [$debut, $fin])
              ->with('produits')
        ])
        ->orderBy('name')
        ->get();

    return view('admin.recap.index', compact('clients', 'mois', 'annee', 'debut'));
}
```

---

### `resources/views/admin/agenda/index.blade.php` (new, view, request-response)

**Analog:** `resources/views/admin/dashboard.blade.php` (full, lines 1-57).

**Layout wrapper pattern** (lines 1-13):
```blade
@extends('layouts.admin')

@section('title', 'Mon agenda du jour · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <div class="px-5 sm:px-8 py-7 space-y-7">
```

**Card pattern** (clients/show.blade.php lines 52-71):
```blade
<div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
    <h2 class="font-display font-semibold text-base text-ink-900 mb-4">…</h2>
    <dl class="space-y-3">…</dl>
</div>
```

**"Nouveau passage" link pattern** (clients/show.blade.php lines 40-47):
```blade
<a href="{{ route('admin.passages.create', ['client_id' => $client->id]) }}"
    class="h-11 px-5 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 transition-colors inline-flex items-center gap-2">
    Nouveau passage
</a>
```

**Mobile nav** (dashboard.blade.php line 54):
```blade
    </div>
    <x-admin.mobile-bottom-nav />
@endsection
```

---

### `resources/views/admin/recap/index.blade.php` (new, view, request-response)

**Analog:** `resources/views/admin/clients/show.blade.php` (layout + dl/dd pattern, lines 1-123).

**Layout pattern:** same `@extends('layouts.admin')` wrapper + `px-5 sm:px-8 py-7 max-w-3xl space-y-6`.

**Table/list pattern** (clients/show.blade.php passages section, lines 109-118):
```blade
@forelse ($client->passages()->orderBy('visited_at', 'desc')->paginate(10) as $passage)
    <div class="py-3 border-b border-sand-100 last:border-0">
        <p class="text-sm text-ink-700">{{ $passage->visited_at?->format('d/m/Y') ?? '—' }}</p>
    </div>
@empty
    <p class="text-ink-400 text-sm">Aucun passage pour ce client.</p>
@endforelse
```

**Inert CTA button style** (match azure-500 button but `disabled` or plain `span`):
```blade
<span class="h-11 px-5 rounded-xl bg-sand-200 text-ink-400 font-semibold inline-flex items-center gap-2 cursor-not-allowed">
    Générer la facture <span class="text-xs">(bientôt)</span>
</span>
```

---

### `resources/views/admin/passages/create.blade.php` — add produits section (view, event-driven)

**Analog:** self (full file, lines 1-371).

**Section pattern to copy** (lines 147-184, actions section):
```blade
<section>
    <h2 class="font-display font-semibold text-lg text-ink-950 mb-3">Actions menées</h2>
    <div class="space-y-2">
        <template x-for="action in actionsAvailable" :key="action">
            <label class="flex items-center gap-3 h-12 px-3.5 rounded-xl cursor-pointer transition-colors"
                   :class="isActionSelected(action) ? 'bg-azure-50 ring-1 ring-azure-200' : 'bg-white ring-1 ring-sand-200'">
                …checkbox custom…
            </label>
        </template>
    </div>
</section>
```

**New produits section** — insert between Actions and Notes sections. Uses same `x-for` + checkbox custom pattern, replacing `actionsAvailable` with `produitsDisponibles` and `toggleAction` with `toggleProduit`:
```blade
<section>
    <h2 class="font-display font-semibold text-lg text-ink-950 mb-3">Produits utilisés
        <span class="text-sm font-normal text-ink-400">(optionnel)</span>
    </h2>
    <div class="space-y-2">
        <template x-for="p in produitsDisponibles" :key="p.id">
            <label class="flex items-center gap-3 h-12 px-3.5 rounded-xl cursor-pointer transition-colors"
                   :class="isProduitSelected(p.id) ? 'bg-azure-50 ring-1 ring-azure-200' : 'bg-white ring-1 ring-sand-200'">
                …checkbox custom identique aux actions…
                <span class="font-medium flex-1" x-text="p.libelle"></span>
                <input type="number" inputmode="decimal" min="0" step="0.1"
                       x-show="isProduitSelected(p.id)" x-cloak
                       @click.stop
                       :x-model="`produitQuantites.${p.id}`"
                       placeholder="Qté"
                       class="w-16 h-8 rounded-lg bg-sand-50 ring-1 ring-sand-200 text-center text-sm text-ink-900 focus:ring-2 focus:ring-azure-500 outline-none" />
            </label>
        </template>
    </div>
</section>
```

**`produitsDisponibles` injected via Blade** (same pattern as `$clients` in PassageCreateController line 44):
```blade
x-data="passageForm({ …, produits: @js($produits ?? []) })"
```

---

### `resources/js/passage-form.js` — add produits to state + payload (utility, event-driven)

**Analog:** self (full file, lines 39-513).

**State additions** (after `notesPrivees: ''` at line 75) — same flat property pattern:
```js
// ---- produits utilisés (chimie) ----
produitsDisponibles: initialData.produits ?? [],  // [{id, libelle, prix_ht}] pré-injectés
produitIds: [],          // ids cochés
produitQuantites: {},    // { [produit_id]: quantite_string }
```

**Toggle helper** (after `isActionSelected`, same pattern as `toggleAction` lines 192-201):
```js
toggleProduit(id) {
    if (this.produitIds.includes(id)) {
        this.produitIds = this.produitIds.filter((x) => x !== id);
        const q = { ...this.produitQuantites };
        delete q[id];
        this.produitQuantites = q;
    } else {
        this.produitIds = [...this.produitIds, id];
    }
},

isProduitSelected(id) {
    return this.produitIds.includes(id);
},
```

**`_toPayload()` extension** (lines 291-308) — add `produits` array at end of returned object:
```js
_toPayload() {
    return {
        // … existing fields unchanged …
        notes:         this.notes        || null,
        notes_privees: this.notesPrivees || null,
        produits: this.produitIds.map((id) => ({   // ADD
            produit_id: id,
            quantite:   this._num(this.produitQuantites[id] ?? '') ,
        })),
    };
},
```

**`$watch` addition** (in `init()` after `$watch('notesPrivees', ...)` line 130):
```js
this.$watch('produitIds',      () => this._debouncedSave());
this.$watch('produitQuantites', () => this._debouncedSave());
```

**After passage sync** (`_uploadPassage` success branch after `markStatus synced`) — call the produits sync endpoint:
```js
if (res.ok) {
    await markStatus('passages', item.id, 'synced');
    await this._syncProduits(item);   // ADD — sync pivot après passage OK
    await this._uploadPhotosForPassage(item.client_uuid);
    return;
}
```

```js
async _syncProduits(item) {
    let payload = {};
    try { payload = JSON.parse(item.payload_json); } catch {}
    const produits = payload.produits ?? [];
    if (!produits.length) return;

    const res = await fetch('/api/passages/produits', {
        method:      'POST',
        headers:     this._headers(true),
        credentials: 'same-origin',
        body:        JSON.stringify({
            passage_client_uuid: item.client_uuid,
            produits,
        }),
    });
    // Soft failure — log only, ne bloque pas la synchro principale
    if (!res.ok) console.warn('[passage-form] produits sync failed', res.status);
},
```

---

### `app/Livewire/Portail/PassageTimeline.php` — INVARIANT, do not touch

**Do not modify.** The invariant is: `notes_privees` must never appear in this component's query or view.

**Current query** (lines 22-27) — `notes_privees` is absent from `->with([...])` and from the view. Adding the column to the model does NOT expose it here — Eloquent only includes it if explicitly selected or rendered. **Verify after migration** that no `select('*')` or column dump passes `notes_privees` to the Blade view.

---

## Shared Patterns

### Admin layout wrapper
**Source:** `resources/views/admin/dashboard.blade.php` lines 1-56
**Apply to:** All new admin Blade views (`agenda/index`, `recap/index`)
```blade
@extends('layouts.admin')
@section('title', '…')
@section('sidebar') <x-admin.sidebar :user="auth()->user()" /> @endsection
@section('topbar') <x-admin.topbar /> @endsection
@section('main')
    <div class="px-5 sm:px-8 py-7 space-y-7">
        …
    </div>
    <x-admin.mobile-bottom-nav />
@endsection
```

### Admin controller pattern (read-only view, no Livewire)
**Source:** `app/Http/Controllers/Admin/DashboardController.php` lines 1-94
**Apply to:** `AgendaController`, `RecapMensuelController`
- Namespace `App\Http\Controllers\Admin`
- `extends Controller`, returns `view('admin.…', compact(…))`
- No Livewire — read-only views rendered by Blade, data from Eloquent queries

### Admin route registration
**Source:** `routes/admin.php` lines 21-41
**Apply to:** New admin GET routes
```php
Route::get('agenda', [AgendaController::class, 'index'])->name('agenda.index');
Route::get('recap', [RecapMensuelController::class, 'index'])->name('recap.index');
```

### API route registration (offline sync endpoints)
**Source:** `routes/api.php` lines 13-16
**Apply to:** `PassageProduitController` route
```php
Route::post('passages/produits', [PassageProduitController::class, 'store'])
     ->name('passages.produits.store');
```
Note: must be declared **before** `passages/{uuid}/photos` to avoid `produits` being captured as `{uuid}`.

### Add-column migration pattern
**Source:** `database/migrations/2026_05_30_000010_add_lead_columns_to_diagnostics_table.php` lines 1-32
**Apply to:** Both new add-column migrations (`notes_privees`, `frequence_jour`)
- `Schema::table('…', ...)` not `Schema::create`
- Use `->after('…')` for column ordering
- `down()` always calls `$table->dropColumn([…])` with array

### Offline-safe data injection (Blade → Alpine)
**Source:** `app/Http/Controllers/Admin/PassageCreateController.php` lines 40-50 + `create.blade.php` line 21
**Apply to:** `AgendaController` (produits list) + `PassageCreateController` modification
```php
// Controller: render once server-side so available offline
$produits = Produit::where('actif', true)->orderBy('libelle')->get(['id', 'libelle', 'prix_ht']);
return view('admin.passages.create', compact(…, 'produits'));
```
```blade
{{-- View: inject as @js() into Alpine x-data --}}
x-data="passageForm({ …, produits: @js($produits) })"
```

### Parametric PDO binding (no string interpolation)
**Source:** `app/Http/Controllers/Api/PassageController.php` lines 59-110
**Apply to:** Any raw SQL in `PassageProduitController` (if needed)
- Named bindings `:name` only — never string interpolation
- `DB::affectingStatement()` for INSERT/UPDATE (returns row count), NOT `DB::statement()` (returns bool)

---

## No Analog Found

All files have analogs. No new technical patterns (event sourcing, streaming, etc.) are introduced in this phase.

---

## Metadata

**Analog search scope:** `app/Http/Controllers/`, `app/Models/`, `database/migrations/`, `resources/js/`, `resources/views/admin/`, `routes/`
**Files scanned:** 22
**Pattern extraction date:** 2026-06-03
