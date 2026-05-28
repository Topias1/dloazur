# Phase 2: MVP Suivi Offline-First — Pattern Map

**Mapped:** 2026-05-28
**Files analyzed:** 32 fichiers nouveaux / modifiés
**Analogs found:** 28 / 32

---

## File Classification

| Nouveau / Modifié | Rôle | Data Flow | Analog le plus proche | Qualité |
|-------------------|------|-----------|----------------------|---------|
| `app/Models/Client.php` (modif) | model | CRUD | `app/Models/User.php` | exact |
| `app/Models/Passage.php` (modif) | model | CRUD | `app/Models/Piscine.php` | exact |
| `app/Models/PhotoMeta.php` (modif) | model | CRUD | `app/Models/Passage.php` | exact |
| `app/Models/Piscine.php` (lecture) | model | CRUD | soi-même | — |
| `database/migrations/…_add_client_uuid_to_photos_meta.php` | migration | batch | `database/migrations/2026_05_28_000006_create_photos_meta_table.php` | exact |
| `app/Http/Controllers/Api/PassageController.php` | controller | request-response | `app/Http/Controllers/Admin/DashboardController.php` | role-match |
| `app/Http/Controllers/Api/PassagePhotoController.php` | controller | file-io | `app/Http/Controllers/Admin/DashboardController.php` | role-match |
| `app/Http/Controllers/Portail/MagicLinkController.php` | controller | request-response | `app/Http/Controllers/Admin/DashboardController.php` | role-match |
| `app/Http/Middleware/RedirectIfClientAuthenticated.php` | middleware | request-response | `app/Http/Middleware/CacheHeaders.php` | role-match |
| `app/Http/Middleware/ServiceWorkerHeaders.php` | middleware | request-response | `app/Http/Middleware/CacheHeaders.php` | exact |
| `app/Livewire/ClientIndex.php` | component | CRUD | `app/Livewire/ContactForm.php` | role-match |
| `app/Livewire/ClientForm.php` | component | CRUD | `app/Livewire/ContactForm.php` | exact |
| `app/Livewire/PiscineForm.php` | component | CRUD | `app/Livewire/ContactForm.php` | exact |
| `app/Livewire/PassageIndex.php` | component | CRUD | `app/Livewire/GoogleReviews.php` | role-match |
| `app/Livewire/Portail/PassageTimeline.php` | component | request-response | `app/Livewire/GoogleReviews.php` | role-match |
| `app/Providers/FortifyServiceProvider.php` (modif) | provider | config | soi-même | exact |
| `resources/views/admin/clients/index.blade.php` | view | request-response | `resources/views/admin/dashboard.blade.php` | exact |
| `resources/views/admin/clients/show.blade.php` | view | CRUD | `resources/views/admin/dashboard.blade.php` | exact |
| `resources/views/admin/passages/index.blade.php` | view | CRUD | `resources/views/admin/dashboard.blade.php` | exact |
| `resources/views/admin/passages/create.blade.php` | view | event-driven | `resources/views/admin/dashboard.blade.php` | role-match |
| `resources/views/portail/confirm.blade.php` | view | request-response | `resources/views/auth/login.blade.php` | role-match |
| `resources/views/portail/magic-link-request.blade.php` | view | request-response | `resources/views/auth/login.blade.php` | exact |
| `resources/views/portail/passages.blade.php` | view | request-response | `resources/views/admin/dashboard.blade.php` | role-match |
| `resources/views/offline.blade.php` | view | request-response | aucun | — |
| `resources/views/components/admin/sidebar.blade.php` (modif) | component | request-response | soi-même | exact |
| `resources/views/components/admin/topbar.blade.php` (modif) | component | event-driven | soi-même | exact |
| `resources/views/components/admin/mobile-bottom-nav.blade.php` (modif) | component | event-driven | soi-même | exact |
| `resources/views/components/admin/stat-card.blade.php` (modif) | component | request-response | soi-même | exact |
| `resources/js/app.js` (modif) | utility | event-driven | soi-même | exact |
| `resources/js/passage-form.js` | utility | event-driven | aucun (nouveau pattern Alpine) | — |
| `resources/js/offline-queue.js` | utility | event-driven | aucun (nouveau pattern IDB) | — |
| `resources/js/photo-pipeline.js` | utility | file-io | aucun (nouveau pattern Canvas+WASM) | — |
| `vite.config.js` (modif) | config | build | soi-même | exact |
| `routes/api.php` | route | request-response | `routes/admin.php` | role-match |
| `routes/portail.php` | route | request-response | `routes/admin.php` | exact |
| `bootstrap/app.php` (modif) | config | config | soi-même | exact |
| `tests/Feature/MagicLinkTest.php` | test | request-response | `tests/Feature/AuthLoginTest.php` | exact |
| `tests/Feature/ClientCrudTest.php` | test | CRUD | `tests/Feature/ContactFormTest.php` | role-match |
| `tests/Feature/PassageApiTest.php` | test | request-response | `tests/Feature/MigrationsTest.php` | role-match |
| `tests/Feature/PhotoUploadTest.php` | test | file-io | `tests/Feature/MigrationsTest.php` | role-match |
| `tests/Feature/PassageIndexTest.php` | test | CRUD | `tests/Feature/AdminShellTest.php` | role-match |
| `tests/Feature/PortailAccessTest.php` | test | request-response | `tests/Feature/AuthLoginTest.php` | exact |

---

## Pattern Assignments

### `app/Models/Client.php` — MODIFICATION CRITIQUE

**Rôle :** model, CRUD  
**Analog :** `app/Models/User.php` (Authenticatable) + soi-même (relations existantes)

**Problème flaggé (RESEARCH §A5, Open Questions §3) :** Le modèle actuel étend `Model`. Le guard `clients` de Laravel requiert `Illuminate\Contracts\Auth\Authenticatable`. Sans ce changement, `Auth::guard('clients')->login($client)` lève une `TypeError`.

**Pattern actuel — imports (lignes 1-8) :**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;
```

**Pattern cible — imports à substituer :**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;
```

**Source du pattern `Authenticatable` :** `app/Models/User.php` (lignes 1-10) — même structure que le modèle User généré par Laravel. Copier les imports `Authenticatable` + `Notifiable` de là.

**Colonnes `$fillable` à ajouter** (requis par `cesargb/laravel-magiclink` v2.27) : le package utilise ses propres colonnes de token via `MagicLink::create()`, pas `magic_link_token`/`magic_link_expires_at` directement dans le modèle. Ces colonnes Phase 1 peuvent rester pour compatibilité ascendante.

**Relations existantes à conserver** (lignes 29-47) : `piscines()`, `passages()`, `contrats()`, `factures()` — aucune modification.

---

### `app/Models/Passage.php` — MODIFICATION (ajout HasMedia)

**Rôle :** model, CRUD + file-io  
**Analog :** soi-même (déjà bien structuré) + pattern RESEARCH §Pitfall 6

**Pattern existant (lignes 1-68) :** Complet — `$fillable`, `$casts`, relations. À conserver tel quel.

**Ajout requis (RESEARCH §Pitfall 6, Open Questions §2) :**

Decision finale RESEARCH : utiliser `Storage::disk('r2')` directement + remplir `photos_meta` manuellement (pas medialibrary). Donc **pas d'ajout HasMedia** sur `Passage`. Le modèle reste inchangé structurellement, sauf ajout d'une méthode helper :

```php
// Ajouter après la relation signature() (ligne 67)
public function latestPhoto(): HasOne
{
    return $this->hasOne(PhotoMeta::class)->latestOfMany('captured_at');
}
```

**Pattern `$casts` existant à copier pour nouveaux Livewire components** (lignes 36-47) :
```php
protected $casts = [
    'client_uuid' => 'string',
    'actions'     => 'array',
    'visited_at'  => 'datetime',
    'synced_at'   => 'datetime',
    'ph_avant'    => 'decimal:2',
    // ...
];
```

---

### `app/Models/PhotoMeta.php` — MODIFICATION (ajout client_uuid)

**Rôle :** model, CRUD  
**Analog :** soi-même

**Pattern existant :** `$table = 'photos_meta'` (override required — PHP pluralise en `photo_metas`). À conserver.

**Modification requise (D-42) :**
```php
// Ajouter dans $fillable après 'passage_id'
'client_uuid',

// Ajouter dans $casts
'client_uuid' => 'string',
```

**Note :** `client_uuid` arrivera de la migration `add_client_uuid_to_photos_meta`.

---

### `database/migrations/…_add_client_uuid_to_photos_meta.php` — NOUVEAU

**Rôle :** migration, batch  
**Analog :** `database/migrations/2026_05_28_000006_create_photos_meta_table.php`

**Pattern imports + structure (lignes 1-10 de l'analog) :**
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos_meta', function (Blueprint $table) {
```

**Pattern cible complet (RESEARCH §Code Examples §Migration photos_meta.client_uuid) :**
```php
return new class extends Migration {
    public function up(): void {
        Schema::table('photos_meta', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->unique()->after('passage_id');
        });
    }
    public function down(): void {
        Schema::table('photos_meta', function (Blueprint $table) {
            $table->dropColumn('client_uuid');
        });
    }
};
```

**Nommage :** `2026_05_28_000011_add_client_uuid_to_photos_meta.php` (numérotation `000011` suite aux 10 migrations Phase 1).

---

### `app/Http/Controllers/Api/PassageController.php` — NOUVEAU

**Rôle :** controller, request-response  
**Analog :** `app/Http/Controllers/Admin/DashboardController.php` (structure namespace/imports) + RESEARCH §Pattern 5 (logique métier)

**Pattern imports (analog lignes 1-8) :**
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Passage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
```

**Pattern validation + UPSERT (RESEARCH §Pattern 5, lignes 492-538) :**
```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'client_uuid'  => ['required', 'uuid'],
        'piscine_id'   => ['nullable', 'integer'],
        'client_id'    => ['nullable', 'integer'],
        'visited_at'   => ['nullable', 'date'],
        'ph_avant'     => ['nullable', 'numeric'],
        // ... autres mesures
    ]);

    $affected = DB::affectingStatement("
        INSERT INTO passages (client_uuid, ..., status, synced_at, created_at, updated_at)
        VALUES (:client_uuid, ..., 'draft', NOW(), NOW(), NOW())
        ON CONFLICT (client_uuid) DO UPDATE SET
            ph_avant = EXCLUDED.ph_avant,
            ...
            synced_at = NOW(), updated_at = NOW()
        WHERE passages.status = 'draft'
    ", [...$data, 'actions' => json_encode($data['actions'] ?? [])]);

    if ($affected === 0) {
        return response()->json([
            'error'        => 'already_closed',
            'server_state' => Passage::where('client_uuid', $data['client_uuid'])->first(),
        ], 409);
    }

    return response()->json(['ok' => true], 200);
}
```

**Pattern erreur handling :** `DB::affectingStatement()` (pas `DB::statement()` qui retourne bool). Retourner 409 si `$affected === 0`.

---

### `app/Http/Controllers/Api/PassagePhotoController.php` — NOUVEAU

**Rôle :** controller, file-io  
**Analog :** `app/Http/Controllers/Admin/DashboardController.php` (structure) + RESEARCH §Code Examples §Medialibrary

**Pattern validation + Storage::disk (RESEARCH §Code Examples lignes 926-952) :**
```php
public function store(Request $request, string $passageUuid): JsonResponse
{
    $request->validate([
        'photo'       => ['required', 'file', 'mimes:jpeg,jpg', 'max:10240'],
        'client_uuid' => ['required', 'uuid'],
        'captured_at' => ['nullable', 'date'],
    ]);

    $passage = Passage::where('client_uuid', $passageUuid)->firstOrFail();
    $file    = $request->file('photo');
    $path    = Storage::disk('r2')->putFile("passages/{$passageUuid}/photos", $file);

    PhotoMeta::updateOrCreate(
        ['client_uuid' => $request->input('client_uuid')],
        [
            'passage_id'  => $passage->id,
            'disk'        => 'r2',
            'path'        => $path,
            'mime_type'   => 'image/jpeg',
            'size_bytes'  => $file->getSize(),
            'captured_at' => $request->input('captured_at'),
        ]
    );

    return response()->json(['ok' => true]);
}
```

**Note sécurité :** Filtrer `where('client_uuid', $passageUuid)` — ne jamais exposer les photos d'un autre passage.

---

### `app/Http/Controllers/Portail/MagicLinkController.php` — NOUVEAU

**Rôle :** controller, request-response  
**Analog :** `app/Providers/FortifyServiceProvider.php` (pattern rate limiter) + RESEARCH §Pattern 6

**Pattern guard clients + création magic link (RESEARCH §Pattern 6, lignes 544-580) :**
```php
// send() — POST /auth/magic
public function send(Request $request): \Illuminate\Http\RedirectResponse
{
    // Rate limit déjà appliqué par middleware 'throttle:magic-link'
    $request->validate(['email' => ['required', 'email']]);

    // Anti-énumération : message générique + timing constant (D-52)
    sleep(random_int(1, 3));

    $client = \App\Models\Client::where('email', $request->email)->first();
    if ($client) {
        $action = new \MagicLink\Actions\LoginAction($client, guard: 'clients', remember: false);
        \MagicLink\MagicLink::create($action, lifetime: 2880, numMaxVisits: 3);
        // Envoyer l'email magic link via Brevo (driver configuré Phase 1)
    }

    return back()->with('status', 'Si cet email correspond à un compte, un lien de connexion a été envoyé.');
}

// confirmView() — GET /auth/confirm?token=…
// Retourne vue statique uniquement — NE consomme PAS le token (D-50, SafeLinks M365)
public function confirmView(Request $request): \Illuminate\View\View
{
    return view('portail.confirm', ['token' => $request->query('token')]);
}

// confirm() — POST /auth/confirm
// Seul le POST consomme le token
public function confirm(Request $request): \Illuminate\Http\RedirectResponse
{
    // cesargb/laravel-magiclink gère la validation + consommation du token
    // via son middleware ou route model binding selon config
    // → rediriger vers le portail
    return redirect()->route('portail.passages');
}
```

**Pattern rate limiter (copier de `FortifyServiceProvider::boot()` lignes 42-50) :**
```php
// Dans AppServiceProvider::boot() ou FortifyServiceProvider::boot()
RateLimiter::for('magic-link', function (Request $request) {
    return [
        Limit::perHour(5)->by($request->ip()),
        Limit::perDay(3)->by($request->input('email', '')),
    ];
});
```

---

### `app/Http/Middleware/ServiceWorkerHeaders.php` — NOUVEAU

**Rôle :** middleware, request-response  
**Analog :** `app/Http/Middleware/CacheHeaders.php` (structure de base)

**Pattern (copier structure de `CacheHeaders.php`) :**
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceWorkerHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('build/sw.js')) {
            $response->headers->set('Service-Worker-Allowed', '/');
            $response->headers->set('Cache-Control', 'no-cache');
        }

        return $response;
    }
}
```

**Enregistrement :** dans `bootstrap/app.php` via `$middleware->append()` — copier le pattern existant (lignes 34-42 de `bootstrap/app.php`).

---

### `app/Livewire/ClientIndex.php` — NOUVEAU

**Rôle :** component, CRUD  
**Analog :** `app/Livewire/ContactForm.php` (structure Livewire 3) + RESEARCH §Pattern 8

**Pattern imports + WithPagination (RESEARCH §Pattern 8, lignes 663-690) :**
```php
namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): \Illuminate\View\View
    {
        $clients = Client::query()
            ->when($this->search, fn ($q) =>
                $q->where(
                    DB::raw("name || ' ' || COALESCE(email,'') || ' ' || COALESCE(phone,'') || ' ' || COALESCE(address,'')"),
                    'ILIKE',
                    '%' . $this->search . '%'
                )
            )
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        return view('livewire.client-index', compact('clients'));
    }
}
```

**Pattern validation Livewire 3 (de `ContactForm.php` lignes 19-29) :**
```php
// Utiliser l'attribut #[Validate] sur les propriétés publiques
#[Validate('required|string|max:80')]
public string $name = '';
```

**Anti-pattern :** NE PAS utiliser `DB::raw()` sans binding pour la recherche ILIKE (RESEARCH §Anti-Patterns). Le `DB::raw()` ici est uniquement pour la concaténation de colonnes ; le binding `'%…%'` passe via Eloquent.

---

### `app/Livewire/ClientForm.php` — NOUVEAU

**Rôle :** component, CRUD  
**Analog :** `app/Livewire/ContactForm.php` (pattern exact — validate + submit + reset)

**Pattern complet de `ContactForm.php` à copier :**

```php
// Imports (lignes 1-14) :
use Livewire\Attributes\Validate;
use Livewire\Component;

// Propriétés avec validation inline (lignes 19-29) :
#[Validate('required|string|max:80')]
public string $name = '';

#[Validate('nullable|email|max:160')]
public string $email = '';

// Pattern submit (lignes 40-85) :
public function submit(): void
{
    $this->validate();

    try {
        Client::updateOrCreate(['id' => $this->clientId], $this->getFormData());
    } catch (\Throwable $e) {
        Log::error('Client save failed', ['exception' => $e->getMessage()]);
        $this->addError('save', "L'enregistrement a échoué.");
        return;
    }

    $this->dispatch('client-saved');
}

// render() (ligne 87) :
public function render(): \Illuminate\View\View
{
    return view('livewire.client-form');
}
```

---

### `app/Livewire/PiscineForm.php` — NOUVEAU

**Rôle :** component, CRUD  
**Analog :** `app/Livewire/ContactForm.php` (même pattern exact)

**Spécificité :** Propriété `$clientId` en plus. Relations `Piscine::$fillable` depuis `app/Models/Piscine.php` (lignes 14-22) :
```php
protected $fillable = [
    'client_id', 'nom', 'volume_m3', 'type',
    'filtration', 'traitement', 'equipements', 'notes',
];
```
Copier le cast `'equipements' => 'array'` de `Piscine.php` (ligne 26).

---

### `app/Livewire/PassageIndex.php` — NOUVEAU

**Rôle :** component, CRUD  
**Analog :** `app/Livewire/GoogleReviews.php` (render avec passage de données) + RESEARCH §Pattern 8 (WithPagination)

**Pattern render avec filtres (RESEARCH §Pattern 8 adapté + D-62) :**
```php
use WithPagination;

public string $clientId = '';
public string $dateFrom = '';
public string $dateTo   = '';

public function updatedClientId(): void { $this->resetPage(); }
public function updatedDateFrom(): void { $this->resetPage(); }
public function updatedDateTo():   void { $this->resetPage(); }

public function render(): \Illuminate\View\View
{
    $passages = Passage::query()
        ->with(['client', 'piscine'])
        ->when($this->clientId, fn ($q) => $q->where('client_id', $this->clientId))
        ->when($this->dateFrom, fn ($q) => $q->whereDate('visited_at', '>=', $this->dateFrom))
        ->when($this->dateTo,   fn ($q) => $q->whereDate('visited_at', '<=', $this->dateTo))
        ->orderBy('visited_at', 'desc')
        ->paginate(25);

    $clients = Client::orderBy('name')->get(['id', 'name']);

    return view('livewire.passage-index', compact('passages', 'clients'));
}
```

---

### `app/Livewire/Portail/PassageTimeline.php` — NOUVEAU

**Rôle :** component, request-response  
**Analog :** `app/Livewire/GoogleReviews.php` (render-only, inject service-like data)

**Sécurité critique (RESEARCH §Security, ligne 1110) :** Toute query DOIT filtrer `client_id = Auth::guard('clients')->id()`.

```php
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PassageTimeline extends Component
{
    public function render(): \Illuminate\View\View
    {
        $client = Auth::guard('clients')->user();

        $passages = \App\Models\Passage::query()
            ->where('client_id', $client->id)
            ->with(['photos', 'piscine'])
            ->orderBy('visited_at', 'desc')
            ->get();

        return view('livewire.portail.passage-timeline', compact('passages', 'client'));
    }
}
```

---

### `app/Providers/FortifyServiceProvider.php` — MODIFICATION

**Rôle :** provider, config  
**Analog :** soi-même (lignes 1-52)

**Ajout dans `boot()` :** Rate limiter magic-link (D-52) + éventuellement guard `clients` session config si pas dans `config/auth.php`.

**Pattern existant à respecter (lignes 42-50) :**
```php
RateLimiter::for('login', function (Request $request) {
    $throttleKey = mb_strtolower((string) $request->input('email')).'|'.$request->ip();
    return Limit::perMinute(5)->by($throttleKey);
});
```

**Ajout (copier ce pattern, adapter pour magic-link) :**
```php
RateLimiter::for('magic-link', function (Request $request) {
    return [
        Limit::perHour(5)->by($request->ip()),
        Limit::perDay(3)->by($request->input('email', '')),
    ];
});
```

---

### `resources/views/admin/clients/index.blade.php` — NOUVEAU

**Rôle :** view, request-response  
**Analog :** `resources/views/admin/dashboard.blade.php` (exact)

**Pattern `@extends` / `@section` (lignes 1-12 de `dashboard.blade.php`) :**
```blade
@extends('layouts.admin')

@section('title', 'Clients · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <livewire:client-index />
    <x-admin.mobile-bottom-nav />
@endsection
```

**Point important :** Le layout `layouts/admin.blade.php` utilise `@yield('sidebar')` + `@yield('main')` (lignes 15-45). Ne pas utiliser `@yield('content')` (alias de `main`) — conserver `@section('main')`.

---

### `resources/views/admin/passages/create.blade.php` — NOUVEAU (Alpine, pas Livewire)

**Rôle :** view, event-driven (offline-first)  
**Analog :** `resources/views/admin/dashboard.blade.php` (structure shell) — mais le contenu principal utilise Alpine `x-data`, PAS un composant Livewire

**Pattern shell (copier de `dashboard.blade.php`) + injection Alpine :**
```blade
@extends('layouts.admin')

@section('title', 'Nouveau passage · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    {{-- Topbar Phase 2 (activé) --}}
    <x-admin.topbar />
@endsection

@section('main')
    {{-- Alpine component — CF-02 : PAS Livewire ici --}}
    <div x-data="passageForm" x-init="init()">
        {{-- ... formulaire passage offline ... --}}
        {{-- CSRF pour fetch() inline (Pitfall 5) --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </div>
    <x-admin.mobile-bottom-nav />
@endsection
```

---

### `resources/views/layouts/admin.blade.php` — NOTE (pas de modification)

**Rôle :** layout, request-response  
**Analog :** soi-même

Le layout Phase 1 (lignes 1-47) est déjà compatible Phase 2. **Aucune modification requise.** Les composants `sidebar`, `topbar`, `mobile-bottom-nav` sont consommés via `@yield` dans chaque vue.

**Pattern `@vite` + `meta[name=csrf-token]` à ajouter dans `<head>` :**
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
```
Le token CSRF est nécessaire pour les fetch() Alpine vers `/api/*` (Pitfall 5).

---

### `resources/views/portail/confirm.blade.php` — NOUVEAU

**Rôle :** view, request-response  
**Analog :** `resources/views/layouts/auth.blade.php` + `resources/views/auth/login.blade.php`

**Pattern auth layout (login.blade.php utilise `layouts/auth.blade.php`) :**
```blade
@extends('layouts.auth')

@section('title', 'Connexion à votre espace · Dlo Azur Piscines')
```

**Structure critique (D-50) : GET = HTML statique, POST consomme le token :**
```blade
<form method="POST" action="{{ route('portail.confirm') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <button type="submit" class="w-full h-13 rounded-xl bg-azure-500 text-white font-bold text-base mt-6">
        Confirmer ma connexion
    </button>
</form>
```
**Jamais de `method="GET"` sur ce formulaire.** Le token doit être dans un `<input type="hidden">`.

---

### `resources/views/components/admin/sidebar.blade.php` — MODIFICATION

**Rôle :** component, request-response  
**Analog :** soi-même (lignes 1-118)

**Pattern active item existant (lignes 29-40) — copier pour Clients et Passages :**
```blade
<a href="{{ route('admin.dashboard') }}"
    @class([
        'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
        'bg-white/10 text-white'           => request()->routeIs('admin.dashboard'),
        'hover:bg-white/8 hover:text-white' => !request()->routeIs('admin.dashboard'),
    ])
    @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
```

**Pattern badge "bientôt" existant (lignes 44-52) — garder pour Factures + Catalogue :**
```blade
<span class="ml-auto text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/8 text-navy-200">bientôt</span>
```

**Badge sync Passages (Phase 2 ajout, UI-SPEC §Admin shell sidebar) :**
```blade
{{-- Badge sync — visible si $pendingCount > 0 --}}
@if($pendingCount > 0)
<span class="ml-auto h-5 min-w-5 rounded-full bg-warn text-[oklch(0.32_0.09_70)] text-xs font-bold grid place-items-center px-1"
    aria-live="polite">{{ $pendingCount }}</span>
@endif
```

---

### `resources/views/components/admin/topbar.blade.php` — MODIFICATION

**Rôle :** component, event-driven  
**Analog :** soi-même (lignes 1-48)

**Phase 2 active :** supprimer `disabled aria-disabled="true"` du bouton "Nouveau passage" + search input (lignes 28-45).

**Bouton actif (remplacer lignes 36-45) :**
```blade
<a href="{{ route('admin.passages.create') }}"
    class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 transition-colors shadow-sm">
    <svg ...>...</svg>
    <span class="hidden sm:inline">Nouveau passage</span>
    <span class="sm:hidden">Passage</span>
</a>
```

**Badge sync (nouveau, UI-SPEC §Admin topbar) :**
```blade
{{-- Insérer avant le bouton Nouveau passage --}}
<div x-data="{ pendingCount: 0 }" x-init="/* lire IDB count */" aria-live="polite">
    <template x-if="pendingCount > 0">
        <button @click="$store.syncDrawer.open = true"
            class="flex items-center gap-1.5 h-9 px-3 rounded-xl bg-warn/15 ring-1 ring-warn/30 text-[oklch(0.5_0.11_72)] text-sm font-semibold">
            <span class="h-2 w-2 rounded-full bg-warn animate-pulse"></span>
            <span x-text="pendingCount + ' en attente'"></span>
        </button>
    </template>
</div>
```

---

### `resources/views/components/admin/mobile-bottom-nav.blade.php` — MODIFICATION

**Rôle :** component, event-driven  
**Analog :** soi-même (lignes 1-50)

**Pattern active link (ligne 12-18) — copier pour Clients et Passages :**
```blade
<a href="{{ route('admin.clients.index') }}"
    class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('admin.clients.*') ? 'text-azure-600' : 'text-ink-400' }}"
    @if(request()->routeIs('admin.clients.*')) aria-current="page" @endif>
```

**Pattern badge sync sur Passages (UI-SPEC §Admin shell Mobile bottom-nav) :**
```blade
<a href="{{ route('admin.passages.index') }}" class="relative flex flex-col items-center ...">
    {{-- badge --}}
    <span x-show="$store.offlineQueue.pendingCount > 0"
        class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-warn text-white text-[10px] font-bold grid place-items-center"
        x-text="$store.offlineQueue.pendingCount"></span>
    {{-- icône passages --}}
</a>
```

**Factures reste grisé** (Pattern ligne 40-48 de l'analog — `aria-disabled="true"`, `opacity-60`).

---

### `resources/views/components/admin/stat-card.blade.php` — MODIFICATION

**Rôle :** component, request-response  
**Analog :** soi-même (lignes 1-14)

**Ajout état `'offline'` (UI-SPEC §Dashboard admin Stat cards) :**

```blade
@props(['label', 'value' => '—', 'state' => 'default'])

<div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-5">
    <p class="text-sm text-ink-500">{{ $label }}</p>
    <p @class([
        'mt-1 font-display font-semibold text-3xl',
        'text-ink-950'                   => $state === 'default',
        'text-[oklch(0.5_0.11_72)]'      => $state === 'offline',  // ambre (jamais rouge pour sync)
        'text-danger'                    => $state === 'warn',      // eau à surveiller uniquement
    ])>
        {{ $value }}
    </p>
</div>
```

**Règle couleur (UI-SPEC §Règle ambre) :** `state='offline'` = ambre. `state='warn'` = `text-danger` (eau hors plage). Ne jamais inverser.

---

### `resources/js/app.js` — MODIFICATION

**Rôle :** utility, event-driven  
**Analog :** soi-même (lignes 1-4)

**Pattern existant :**
```javascript
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
```

**Ajout Phase 2 :**
```javascript
import Alpine from 'alpinejs';
import { passageForm } from './passage-form';
import { offlineQueue } from './offline-queue';
import { registerSW } from 'virtual:pwa-register';

// Alpine stores + data registration
Alpine.store('offlineQueue', { pendingCount: 0, errorCount: 0 });
Alpine.data('passageForm', passageForm);

// PWA update prompt (D-56 : 'prompt', protège la saisie en cours)
registerSW({
    onNeedRefresh() {
        Alpine.store('pwaUpdate', { available: true });
    },
    onOfflineReady() {
        console.log('[SW] App ready for offline use');
    },
});

window.Alpine = Alpine;
Alpine.start();
```

---

### `resources/js/passage-form.js` — NOUVEAU

**Rôle :** utility, event-driven (offline Alpine component)  
**Analog :** aucun dans le codebase — pattern entièrement nouveau

**Source de référence :** RESEARCH §Pattern 2 (lignes 353-395) + §Pattern 3 (lignes 399-444)

**Squelette complet à utiliser (RESEARCH §Pattern 2) :**
```javascript
import { openOfflineDB } from './offline-queue';

export function passageForm() {
    return {
        clientUuid: '',
        ph: null, chloreLibre: null, tac: null, sel: null,
        actions: [],
        notes: '', notesPrivees: '',
        photos: [],
        online: navigator.onLine,
        warnings: [],

        async init() {
            this.clientUuid = crypto.randomUUID();  // D-39 : côté client
            await this._saveToIDB('draft');

            window.addEventListener('online',  () => { this.online = true;  this._flushQueue(); });
            window.addEventListener('offline', () => { this.online = false; });
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && navigator.onLine) {
                    this._flushQueue();
                }
            });

            // storage.persist() (D-57)
            if (navigator.storage?.persisted) {
                const persisted = await navigator.storage.persisted();
                if (!persisted) navigator.storage.persist?.();
            }
        },

        async submit() {
            await this._saveToIDB('pending');
            if (navigator.onLine) await this._flushQueue();
        },

        _getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        },

        // ... _flushQueue(), _saveToIDB(), _uploadPhotos(), _refreshBadge()
    };
}
```

**CSRF token** : lire depuis `<meta name="csrf-token">` dans le DOM (Pitfall 5). Ne jamais stocker en IDB.

---

### `resources/js/offline-queue.js` — NOUVEAU

**Rôle :** utility, event-driven  
**Analog :** aucun dans le codebase — pattern entièrement nouveau

**Source de référence :** RESEARCH §Pattern 1 (IDB schema, lignes 292-348) + §Pattern 3 (flush queue, lignes 399-444)

**Fonction `openOfflineDB` à implémenter exactement (RESEARCH §Pattern 1) :**
```javascript
import { openDB } from 'idb';

export async function openOfflineDB() {
    return openDB('dloazur-offline-v1', 1, {
        upgrade(db) {
            const passStore = db.createObjectStore('passages', { keyPath: 'id', autoIncrement: true });
            passStore.createIndex('by-status', 'status');
            passStore.createIndex('by-created', 'created_at');

            const photoStore = db.createObjectStore('photos', { keyPath: 'id', autoIncrement: true });
            photoStore.createIndex('by-passage', 'passage_client_uuid');
            photoStore.createIndex('by-status', 'status');
        },
    });
}
```

**Schéma stores (D-59) :**
- `passages` : `{ id, client_uuid, payload_json, status, attempts, created_at, last_attempt_at }`
- `photos` : `{ id, client_uuid, passage_client_uuid, blob, status, attempts, captured_at }`

**Backoff (RESEARCH §Pattern 3 lignes 405-443) :** 3 retries, délais `2s → 8s → 30s`. Après échec : `status='error'`.

---

### `resources/js/photo-pipeline.js` — NOUVEAU

**Rôle :** utility, file-io  
**Analog :** aucun dans le codebase — pattern entièrement nouveau

**Source de référence :** RESEARCH §Pattern 4 (lignes 450-487)

**Détection HEIC par magic bytes — version corrigée (RESEARCH §Pitfall 8 lignes 859-865) :**
```javascript
const buf = await file.slice(0, 12).arrayBuffer();
const bytes = new Uint8Array(buf);
const brand = String.fromCharCode(bytes[8], bytes[9], bytes[10], bytes[11]);
const isHeic = ['heic', 'heis', 'hevc', 'mif1', 'msf1'].includes(brand);
// NE PAS utiliser file.type — iOS Safari ment (Pitfall 8)
```

**Lazy import WASM (D-43) — uniquement si HEIC détecté :**
```javascript
if (isHeic) {
    const { default: heic2any } = await import('heic2any');
    processedBlob = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.85 });
}
```

**Compression Canvas (D-44) :** max 2048px, `canvas.toBlob(resolve, 'image/jpeg', 0.80)`. **Pas de WebP** (Safari iOS 17 régressions).

---

### `vite.config.js` — MODIFICATION

**Rôle :** config, build  
**Analog :** soi-même (lignes 1-24)

**Pattern existant à conserver intégralement** (lignes 3-17). Ajouter `VitePWA` après `tailwindcss()` :

**Config complète cible (RESEARCH §Pattern 7, lignes 585-658) :**
```javascript
import { VitePWA } from 'vite-plugin-pwa';

// Dans plugins[] après tailwindcss() :
VitePWA({
    registerType: 'prompt',       // D-56 — protège la saisie en cours
    buildBase: '/build/',          // D-60 — contrainte Laravel Cloud
    // ... manifest + workbox config RESEARCH §Pattern 7
})
```

**Point critique (Pitfall 2) :** `buildBase: '/build/'` + middleware `ServiceWorkerHeaders` sont co-dépendants. Les deux doivent être déployés ensemble.

---

### `routes/api.php` — NOUVEAU

**Rôle :** route, request-response  
**Analog :** `routes/admin.php` (pattern Route + namespace)

**Pattern `routes/admin.php` (lignes 1-19) :**
```php
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
```

**Pattern cible (RESEARCH §Pattern 5 + RESEARCH §Architecture) :**
```php
use App\Http\Controllers\Api\PassageController;
use App\Http\Controllers\Api\PassagePhotoController;
use Illuminate\Support\Facades\Route;

Route::post('/passages', [PassageController::class, 'store'])->name('passages.store');
Route::post('/passages/{uuid}/photos', [PassagePhotoController::class, 'store'])->name('passages.photos.store');
```

**Enregistrement dans `bootstrap/app.php`** (copier le pattern lignes 21-26) :
```php
Route::middleware(['web', 'auth'])
    ->prefix('api')
    ->name('api.')
    ->group(base_path('routes/api.php'));
```

**CSRF exempt (Pitfall 5 + RESEARCH §Code Examples) :** ajouter dans `bootstrap/app.php` `withMiddleware()` :
```php
$middleware->validateCsrfTokens(except: ['/api/*']);
```

---

### `routes/portail.php` — NOUVEAU

**Rôle :** route, request-response  
**Analog :** `routes/admin.php` (même structure d'enregistrement dans `bootstrap/app.php`)

**Pattern cible (RESEARCH §Pattern 6, lignes 560-572) :**
```php
use App\Http\Controllers\Portail\MagicLinkController;
use App\Livewire\Portail\PassageTimeline;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:clients')->group(function () {
    Route::view('/auth/magic', 'portail.magic-link-request')->name('portail.magic-link-request');
    Route::post('/auth/magic', [MagicLinkController::class, 'send'])
         ->middleware('throttle:magic-link')->name('portail.magic-link-send');
    Route::get('/auth/confirm', [MagicLinkController::class, 'confirmView'])->name('portail.confirm-view');
    Route::post('/auth/confirm', [MagicLinkController::class, 'confirm'])->name('portail.confirm');
});

Route::middleware('auth:clients')->prefix('portail')->name('portail.')->group(function () {
    Route::get('/passages', PassageTimeline::class)->name('passages');
    Route::post('/logout', [MagicLinkController::class, 'logout'])->name('logout');
});
```

---

### Tests Feature — `MagicLinkTest.php`, `ClientCrudTest.php`, `PassageApiTest.php`, etc.

**Rôle :** tests, request-response / CRUD  
**Analog :** `tests/Feature/AuthLoginTest.php` (pattern exact à copier)

**Pattern de base Pest 4 (lignes 1-16 de `AuthLoginTest.php`) :**
```php
use App\Models\Client;
use App\Models\User;
// ...

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
```

**Pattern auth guard clients (copier de `AuthLoginTest.php` ligne 55-67) :**
```php
// actingAs avec guard spécifique :
$this->actingAs($client, 'clients')->get('/portail/passages')
    ->assertStatus(200);

// assert guest sur guard clients :
$this->assertGuest('clients');
```

**Pattern Livewire test (de `ContactFormTest.php` lignes 17-41) :**
```php
use Livewire\Livewire;

Livewire::test(\App\Livewire\ClientForm::class)
    ->set('name', 'Jean Dupont')
    ->call('submit')
    ->assertHasNoErrors();
```

**Pattern DB unique constraint test (de `MigrationsTest.php` lignes 44-66) — à copier pour tester UPSERT :**
```php
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('POST /api/passages avec même client_uuid retourne 200 (upsert)', function () {
    $user = User::factory()->create();
    $uuid = \Illuminate\Support\Str::uuid()->toString();

    $this->actingAs($user)->postJson('/api/passages', ['client_uuid' => $uuid, ...])
        ->assertStatus(200);

    // Deuxième POST avec même UUID (status='draft') → 200 upsert
    $this->actingAs($user)->postJson('/api/passages', ['client_uuid' => $uuid, ...])
        ->assertStatus(200);

    expect(\App\Models\Passage::where('client_uuid', $uuid)->count())->toBe(1);
});
```

**SAVEPOINT pattern (lignes 52-65 de `MigrationsTest.php`) :** à réutiliser dans `PassageApiTest` pour tester le 409 sans faire crasher la transaction Postgres.

---

## Shared Patterns

### Auth guard `web` (admin)
**Source :** `bootstrap/app.php` lignes 21-26, `routes/admin.php`  
**Appliqué à :** `routes/api.php`, tous les controllers `Api/`
```php
Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')
```

### Auth guard `clients` (portail)
**Source :** RESEARCH §Pattern 6 lignes 544-573  
**Appliqué à :** `routes/portail.php`, `app/Livewire/Portail/PassageTimeline.php`
```php
// config/auth.php — ajouter :
'guards'    => ['clients' => ['driver' => 'session', 'provider' => 'clients']],
'providers' => ['clients' => ['driver' => 'eloquent', 'model' => App\Models\Client::class]],
```

### Validation Livewire 3 (`#[Validate]`)
**Source :** `app/Livewire/ContactForm.php` lignes 19-29  
**Appliqué à :** `ClientForm`, `PiscineForm`, `ClientIndex`
```php
#[Validate('required|string|max:80')]
public string $name = '';
```

### Error handling Livewire (try/catch + `addError`)
**Source :** `app/Livewire/ContactForm.php` lignes 71-80  
**Appliqué à :** `ClientForm`, `PiscineForm`
```php
} catch (\Throwable $e) {
    Log::error('Save failed', ['exception' => $e->getMessage()]);
    $this->addError('save', "L'enregistrement a échoué.");
    return;
}
```

### Rate limiter (pattern FortifyServiceProvider)
**Source :** `app/Providers/FortifyServiceProvider.php` lignes 42-50  
**Appliqué à :** magic-link rate limiter dans `FortifyServiceProvider::boot()`
```php
RateLimiter::for('magic-link', function (Request $request) {
    return [
        Limit::perHour(5)->by($request->ip()),
        Limit::perDay(3)->by($request->input('email', '')),
    ];
});
```

### Layout admin (@extends + @section)
**Source :** `resources/views/admin/dashboard.blade.php` lignes 1-52  
**Appliqué à :** tous les fichiers `resources/views/admin/**/*.blade.php`
```blade
@extends('layouts.admin')
@section('sidebar') <x-admin.sidebar :user="auth()->user()" /> @endsection
@section('topbar') <x-admin.topbar /> @endsection
@section('main') ... <x-admin.mobile-bottom-nav /> @endsection
```

### CSRF meta tag (Alpine fetch offline)
**Source :** `resources/views/layouts/admin.blade.php` (à ajouter)  
**Appliqué à :** `layouts/admin.blade.php` `<head>`, référencé dans `passage-form.js`
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Pattern migration `Schema::table` + `nullable()->unique()`
**Source :** `database/migrations/2026_05_28_000005_create_passages_table.php` lignes 7-15  
**Appliqué à :** `add_client_uuid_to_photos_meta` migration
```php
return new class extends Migration {
    public function up(): void {
        Schema::table('photos_meta', function (Blueprint $table) { ... });
    }
    public function down(): void {
        Schema::table('photos_meta', function (Blueprint $table) {
            $table->dropColumn('client_uuid');
        });
    }
};
```

### Pest `RefreshDatabase` + `actingAs`
**Source :** `tests/Feature/AuthLoginTest.php` lignes 16, 55-67  
**Appliqué à :** tous les tests Feature Phase 2
```php
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
// ...
$this->actingAs($user)->get('/admin')->assertStatus(200);
$this->actingAs($client, 'clients')->get('/portail/passages')->assertStatus(200);
```

---

## No Analog Found

Fichiers sans correspondant dans le codebase existant — le planificateur doit s'appuyer sur RESEARCH.md :

| Fichier | Rôle | Data Flow | Raison |
|---------|------|-----------|--------|
| `resources/js/passage-form.js` | utility | event-driven | Aucun composant Alpine `x-data` factory n'existe encore |
| `resources/js/offline-queue.js` | utility | event-driven | Aucun usage IndexedDB / `idb` dans le codebase |
| `resources/js/photo-pipeline.js` | utility | file-io | Aucun pipeline Canvas / HEIC / WASM dans le codebase |
| `resources/views/offline.blade.php` | view | — | Page offline dédiée, aucun précédent dans le projet |

Pour ces 4 fichiers, les patterns RESEARCH §Patterns 1, 2, 3, 4 sont la référence autoritaire.

---

## Critical Flags (résumé planificateur)

1. **`app/Models/Client.php` DOIT `extends Authenticatable`** — sans cette modification, le guard `clients` lève une `TypeError` au login. Modifier en Wave 0 avant tout autre travail d'auth.

2. **`photos_meta.client_uuid` manquant** — migration `000011_add_client_uuid_to_photos_meta.php` obligatoire avant l'endpoint photo (D-42). Test `MigrationsTest` à étendre.

3. **`Service-Worker-Allowed: /` header** — `ServiceWorkerHeaders` middleware + `buildBase: '/build/'` dans `vite.config.js` sont co-dépendants. Si l'un manque, le SW est scopé à `/build/` et n'intercepte rien.

4. **CSRF exempt `/api/*`** — ajouter dans `bootstrap/app.php` `withMiddleware()` avant les routes API. Sans ça, tous les POST offline retournent 419.

5. **`stat-card.blade.php` state `'offline'` ≠ `'warn'`** — le badge "À synchroniser" utilise la couleur AMBRE (`oklch(0.5_0.11_72)`), jamais `text-danger`. Ajouter le state `'offline'` au composant existant (actuellement il n'a que `'default'` et `'warn'`).

6. **Portail — filter obligatoire** — toutes les queries Eloquent dans `PassageTimeline` DOIVENT avoir `->where('client_id', Auth::guard('clients')->id())` pour éviter la fuite inter-clients.

---

## Metadata

**Scope de recherche :** `app/`, `resources/`, `routes/`, `database/migrations/`, `tests/`, `vite.config.js`, `bootstrap/app.php`
**Fichiers lus :** 28
**Date d'extraction :** 2026-05-28
