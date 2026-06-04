<div class="px-5 sm:px-8 py-7 space-y-7">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">Passages</h1>
        {{-- Bouton "Nouveau passage" désactivé jusqu'à Plan 02-05 (route admin.passages.create non encore créée) --}}
        <span aria-disabled="true" tabindex="-1"
              class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-azure-500/40 text-white font-semibold cursor-not-allowed opacity-50 select-none"
              title="Disponible bientôt">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            <span class="hidden sm:inline">Nouveau passage</span>
            <span class="sm:hidden">Passage</span>
        </span>
        {{-- TODO Plan 02-05: remplacer par <a href="{{ route('admin.passages.create') }}" ...> quand la route existe --}}
    </div>

    {{-- Filtres --}}
    <div class="flex flex-wrap gap-3 items-end">
        {{-- Select client --}}
        <div class="flex flex-col gap-1">
            <label for="filter-client" class="text-xs font-semibold text-ink-500 uppercase tracking-wide">Client</label>
            <select id="filter-client" wire:model.live="clientId"
                    class="h-10 px-3 rounded-xl ring-1 ring-sand-200 bg-white text-sm text-ink-900 min-w-[160px]">
                <option value="">Tous les clients</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Date from --}}
        <div class="flex flex-col gap-1">
            <label for="filter-from" class="text-xs font-semibold text-ink-500 uppercase tracking-wide">Depuis</label>
            <input id="filter-from" wire:model.live.debounce.300ms="dateFrom"
                   type="date" aria-label="Depuis"
                   class="h-10 px-3 rounded-xl ring-1 ring-sand-200 bg-white text-sm text-ink-900" />
        </div>

        {{-- Date to --}}
        <div class="flex flex-col gap-1">
            <label for="filter-to" class="text-xs font-semibold text-ink-500 uppercase tracking-wide">Jusqu'au</label>
            <input id="filter-to" wire:model.live.debounce.300ms="dateTo"
                   type="date" aria-label="Jusqu'au"
                   class="h-10 px-3 rounded-xl ring-1 ring-sand-200 bg-white text-sm text-ink-900" />
        </div>

        {{-- Bouton reset si filtres actifs --}}
        @if ($clientId || $dateFrom || $dateTo)
            <button wire:click="$set('clientId', ''); $set('dateFrom', ''); $set('dateTo', '')"
                    type="button"
                    class="h-10 px-3 rounded-xl text-sm text-ink-600 hover:bg-sand-100 transition-colors self-end">
                Réinitialiser
            </button>
        @endif
    </div>

    {{-- Liste passages --}}
    <div class="space-y-3">
        @forelse ($passages as $p)
            <a href="#" class="block rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-4 hover:bg-sand-50 transition-colors">
                <div class="flex items-start justify-between gap-3">
                    {{-- Date + statut --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-display font-semibold text-ink-900">
                                {{ $p->visited_at?->locale('fr')->isoFormat('dddd D MMMM') ?? '—' }}
                            </span>
                            <span class="text-sm text-ink-500">
                                {{ $p->visited_at?->format('H\hi') }}
                            </span>
                            {{-- Statut chip (UI-SPEC §Historique passages) --}}
                            @if ($p->status === 'synced')
                                <span class="rounded-full bg-success/10 text-success text-xs font-semibold px-2.5 py-0.5">Synchronisé</span>
                            @elseif ($p->status === 'draft')
                                <span class="rounded-full bg-warn/15 text-[oklch(0.5_0.11_72)] text-xs font-semibold px-2.5 py-0.5">Brouillon</span>
                            @elseif ($p->status === 'error')
                                <span class="rounded-full bg-danger/10 text-danger text-xs font-semibold px-2.5 py-0.5">Erreur</span>
                            @endif
                        </div>

                        {{-- Client + piscine --}}
                        <p class="text-sm text-ink-500 mt-0.5">
                            {{ $p->client?->name ?? '—' }}
                            @if ($p->piscine?->volume_m3)
                                · {{ $p->piscine->volume_m3 }} m³
                            @endif
                        </p>

                        {{-- Résumé mesures --}}
                        <p class="text-sm text-ink-400 tabular-nums mt-0.5">
                            pH {{ number_format((float) ($p->ph_avant ?? 0), 1) }}
                            @if ($p->chlore_libre !== null)
                                · Cl {{ number_format((float) $p->chlore_libre, 1) }} mg/L
                            @endif
                        </p>
                    </div>

                    {{-- Compteur photos --}}
                    @if ($p->photos_count > 0)
                        <span class="text-xs text-ink-400 inline-flex items-center gap-1 shrink-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            {{ $p->photos_count }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            {{-- Empty state (UI-SPEC §Empty states "Liste passages vide") --}}
            <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-8 text-center">
                <h2 class="font-display font-semibold text-xl text-ink-950">Aucun passage enregistré.</h2>
                <p class="text-ink-500 mt-2">Commence par saisir un passage sur le terrain.</p>
                {{-- CTA "Nouveau passage" activé par Plan 02-05 quand la route admin.passages.create existera --}}
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($passages->hasPages())
        <div class="pt-2">
            {{ $passages->links() }}
        </div>
    @endif

</div>
