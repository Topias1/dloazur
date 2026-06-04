<div>
    {{-- Topbar sticky navy --}}
    <header class="sticky top-0 z-30 bg-navy-900 text-white h-16 flex items-center px-5">
        <div class="flex items-center gap-3 max-w-3xl w-full mx-auto">
            <span class="text-azure-400">
                <x-icon.drop :size="28" />
            </span>
            <div class="flex-1">
                <p class="font-display font-semibold text-white text-lg leading-none">Dlo Azur</p>
                <p class="text-xs text-navy-200 mt-0.5">
                    Espace de <span class="font-semibold text-white">{{ $client->name }}</span>
                </p>
            </div>
            <form method="POST" action="{{ route('portail.logout') }}" class="ml-auto">
                @csrf
                <button
                    type="submit"
                    class="h-9 px-3 rounded-xl bg-white/10 hover:bg-white/15 text-sm text-white font-medium transition-colors"
                >
                    Se déconnecter
                </button>
            </form>
        </div>
    </header>

    {{-- Contenu principal --}}
    <main class="max-w-3xl mx-auto px-5 py-8 space-y-8">

        {{-- Carte piscine navy drenched, avec photo d'en-tête (feedback Pierre : « plus sexy ») --}}
        @if ($piscine)
            <section class="rounded-3xl bg-navy-900 text-white relative overflow-hidden">
                {{-- Bandeau photo : photo réelle du dernier passage, sinon piscine générique --}}
                <div class="relative h-40 sm:h-52">
                    <img
                        src="{{ $heroPhotoUrl ?? asset('assets/brand/photos/piscine-propre.jpg') }}"
                        alt="Votre piscine"
                        class="absolute inset-0 h-full w-full object-cover photo-grade">
                    <div class="absolute inset-0 bg-gradient-to-t from-navy-900 via-navy-900/45 to-navy-900/10"></div>
                </div>
                <div class="absolute inset-0 ripple opacity-40" aria-hidden="true"></div>
                <div class="relative p-6 -mt-12">
                    <p class="text-sm text-lagon-300 font-semibold">Votre piscine</p>
                    <h2 class="font-display font-bold text-2xl text-white mt-1">
                        {{ $piscine->nom ?? 'Votre bassin' }}
                    </h2>
                    <div class="flex flex-wrap gap-2 mt-4">
                        @if ($piscine->volume_m3)
                            <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 text-sm">
                                {{ $piscine->volume_m3 }} m³
                            </span>
                        @endif
                        @if ($piscine->type)
                            <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 text-sm">
                                {{ ucfirst($piscine->type) }}
                            </span>
                        @endif
                        @if ($piscine->traitement)
                            <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 text-sm">
                                {{ ucfirst($piscine->traitement) }}
                            </span>
                        @endif
                    </div>
                    @if ($lastPassage)
                        <div class="flex items-center gap-2 mt-4">
                            <span class="rounded-full bg-success/20 ring-1 ring-success/40 text-[oklch(0.85_0.12_155)] px-3 py-1 text-sm font-semibold inline-flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-success"></span>Eau saine
                            </span>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Section "Dernier passage" --}}
        @if ($lastPassage)
            <section>
                <h2 class="font-display font-semibold text-xl text-ink-950">Dernier passage</h2>
                <p class="text-sm text-ink-500 mt-0.5">
                    {{ $lastPassage->visited_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                </p>

                <div class="mt-3 rounded-3xl bg-white ring-1 ring-navy-900/8 shadow-sm p-5 sm:p-6 space-y-6">

                    {{-- Grille mesures 4-col --}}
                    <div>
                        <p class="font-display font-semibold text-sm text-ink-900 mb-3">Mesures</p>
                        <div class="grid {{ $showSel ? 'grid-cols-4' : 'grid-cols-3' }} gap-2.5">
                            {{-- pH --}}
                            <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 py-3 text-center">
                                <p class="text-xs text-ink-400">pH</p>
                                <p class="font-display font-bold text-2xl text-ink-950 tabular-nums">
                                    {{ $lastPassage->ph_avant !== null ? number_format((float) $lastPassage->ph_avant, 1, ',', '') : '·' }}
                                </p>
                                @php
                                    $ph = (float) ($lastPassage->ph_avant ?? 0);
                                    $phOk = $ph >= 7.0 && $ph <= 7.6;
                                @endphp
                                <p class="text-[11px] {{ $phOk ? 'text-success font-semibold' : 'text-ink-400' }}">
                                    {{ $lastPassage->ph_avant !== null ? ($phOk ? 'idéal' : 'pH') : '' }}
                                </p>
                            </div>

                            {{-- Cl libre --}}
                            <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 py-3 text-center">
                                <p class="text-xs text-ink-400">Cl libre</p>
                                <p class="font-display font-bold text-2xl text-ink-950 tabular-nums">
                                    {{ $lastPassage->chlore_libre !== null ? number_format((float) $lastPassage->chlore_libre, 1, ',', '') : '·' }}
                                </p>
                                @php
                                    $cl = (float) ($lastPassage->chlore_libre ?? 0);
                                    $clOk = $cl >= 1.0 && $cl <= 3.0;
                                @endphp
                                <p class="text-[11px] {{ $clOk ? 'text-success font-semibold' : 'text-ink-400' }}">
                                    {{ $lastPassage->chlore_libre !== null ? ($clOk ? 'idéal' : 'mg/L') : '' }}
                                </p>
                            </div>

                            {{-- TAC --}}
                            <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 py-3 text-center">
                                <p class="text-xs text-ink-400">TAC</p>
                                <p class="font-display font-bold text-2xl text-ink-950 tabular-nums">
                                    {{ $lastPassage->tac !== null ? number_format((float) $lastPassage->tac, 0, ',', '') : '·' }}
                                </p>
                                @php
                                    $tac = (float) ($lastPassage->tac ?? 0);
                                    $tacOk = $tac >= 80 && $tac <= 120;
                                @endphp
                                <p class="text-[11px] {{ $tacOk ? 'text-success font-semibold' : 'text-ink-400' }}">
                                    {{ $lastPassage->tac !== null ? ($tacOk ? 'idéal' : 'mg/L') : '' }}
                                </p>
                            </div>

                            {{-- Sel — masqué pour les piscines au chlore (feedback Pierre) --}}
                            @if ($showSel)
                                <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 py-3 text-center">
                                    <p class="text-xs text-ink-400">Sel</p>
                                    <p class="font-display font-bold text-2xl text-ink-950 tabular-nums">
                                        {{ $lastPassage->sel_g_l !== null ? number_format((float) $lastPassage->sel_g_l, 1, ',', '') : '·' }}
                                    </p>
                                    <p class="text-[11px] text-ink-400">
                                        {{ $lastPassage->sel_g_l !== null ? 'g/L' : '' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions réalisées --}}
                    @if (!empty($lastPassage->actions) && count((array) $lastPassage->actions) > 0)
                        <div>
                            <p class="font-display font-semibold text-sm text-ink-900 mb-2">Actions réalisées</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ((array) $lastPassage->actions as $action)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-azure-50 text-azure-700 text-sm font-medium px-3 py-1.5">
                                        <svg class="h-3.5 w-3.5 text-azure-500" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M3 8L6.5 11.5L13 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $action }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Mot du pisciniste --}}
                    @if ($lastPassage->notes)
                        <div class="rounded-2xl bg-lagon-500/8 ring-1 ring-lagon-500/20 p-4 flex gap-3">
                            <span class="h-9 w-9 rounded-full bg-lagon-500 text-white font-display font-bold grid place-items-center shrink-0 text-sm">
                                P
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-ink-900">Mot du pisciniste</p>
                                <p class="text-sm text-ink-700 leading-relaxed mt-1">{{ $lastPassage->notes }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Photo du passage --}}
                    @if ($lastPassage->photos->isNotEmpty())
                        @php
                            $firstPhoto = $lastPassage->photos->first();
                            try {
                                $firstPhotoUrl = \Illuminate\Support\Facades\Storage::disk($firstPhoto->disk ?? 'r2')->temporaryUrl($firstPhoto->path, now()->addHour());
                            } catch (\Throwable $e) {
                                $firstPhotoUrl = '';
                            }
                        @endphp
                        <div>
                            <p class="font-display font-semibold text-sm text-ink-900 mb-2">Photo du passage</p>
                            <img
                                src="{{ $firstPhotoUrl }}"
                                alt="Photo de l'intervention du {{ $lastPassage->visited_at->format('d/m/Y') }}"
                                loading="lazy"
                                class="w-full max-h-96 object-cover rounded-2xl ring-1 ring-sand-200"
                            >
                        </div>
                    @endif

                </div>
            </section>
        @endif

        {{-- Section "Historique" — chaque passage est dépliable (feedback Pierre : « rendre les historiques cliquables ») --}}
        <section>
            <h2 class="font-display font-semibold text-xl text-ink-950 mb-4">Historique</h2>

            @if ($passages->count() > 1)
                <ol class="relative border-l border-sand-200 ml-3 space-y-5">
                    @foreach ($passages->skip(1) as $p)
                        <li class="ml-6 relative" x-data="{ open: false }">
                            <span class="absolute -left-[33px] top-3 h-4 w-4 rounded-full ring-4 ring-sand-50 bg-navy-300"></span>
                            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 overflow-hidden">
                                {{-- En-tête cliquable --}}
                                <button
                                    type="button"
                                    @click="open = !open"
                                    :aria-expanded="open"
                                    aria-controls="passage-panel-{{ $p->id }}"
                                    class="w-full p-4 flex items-center justify-between gap-3 text-left hover:bg-sand-50/70 transition-colors">
                                    <div>
                                        <p class="font-display font-semibold text-ink-900">
                                            {{ $p->visited_at->locale('fr')->isoFormat('D MMM YYYY') }}
                                        </p>
                                        <p class="text-sm text-ink-500">
                                            @if ($p->ph_avant !== null)
                                                pH {{ number_format((float) $p->ph_avant, 1, ',', '') }}
                                            @endif
                                            @if ($p->ph_avant !== null && $p->chlore_libre !== null)
                                                ·
                                            @endif
                                            @if ($p->chlore_libre !== null)
                                                Cl {{ number_format((float) $p->chlore_libre, 1, ',', '') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        @if ($p->photos->isNotEmpty())
                                            <span class="text-xs text-ink-400 inline-flex items-center gap-1">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                                                </svg>
                                                {{ $p->photos->count() }}
                                            </span>
                                        @endif
                                        <svg class="h-5 w-5 text-ink-400 transition-transform duration-200" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                    </div>
                                </button>

                                {{-- Détail déplié --}}
                                <div x-show="open" x-cloak
                                     id="passage-panel-{{ $p->id }}"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="px-4 pb-4 border-t border-sand-100">
                                    <div class="grid {{ $showSel ? 'grid-cols-4' : 'grid-cols-3' }} gap-2 mt-3">
                                        <div class="rounded-xl bg-sand-50 ring-1 ring-sand-200 py-2 text-center">
                                            <p class="text-[11px] text-ink-400">pH</p>
                                            <p class="font-display font-bold text-base text-ink-950 tabular-nums">{{ $p->ph_avant !== null ? number_format((float) $p->ph_avant, 1, ',', '') : '·' }}</p>
                                        </div>
                                        <div class="rounded-xl bg-sand-50 ring-1 ring-sand-200 py-2 text-center">
                                            <p class="text-[11px] text-ink-400">Cl libre</p>
                                            <p class="font-display font-bold text-base text-ink-950 tabular-nums">{{ $p->chlore_libre !== null ? number_format((float) $p->chlore_libre, 1, ',', '') : '·' }}</p>
                                        </div>
                                        <div class="rounded-xl bg-sand-50 ring-1 ring-sand-200 py-2 text-center">
                                            <p class="text-[11px] text-ink-400">TAC</p>
                                            <p class="font-display font-bold text-base text-ink-950 tabular-nums">{{ $p->tac !== null ? number_format((float) $p->tac, 0, ',', '') : '·' }}</p>
                                        </div>
                                        @if ($showSel)
                                            <div class="rounded-xl bg-sand-50 ring-1 ring-sand-200 py-2 text-center">
                                                <p class="text-[11px] text-ink-400">Sel</p>
                                                <p class="font-display font-bold text-base text-ink-950 tabular-nums">{{ $p->sel_g_l !== null ? number_format((float) $p->sel_g_l, 1, ',', '') : '·' }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    @if (!empty($p->actions) && count((array) $p->actions) > 0)
                                        <div class="flex flex-wrap gap-1.5 mt-3">
                                            @foreach ((array) $p->actions as $action)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-azure-50 text-azure-700 text-xs font-medium px-2.5 py-1">
                                                    <svg class="h-3 w-3 text-azure-500" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                                        <path d="M3 8L6.5 11.5L13 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    {{ $action }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($p->notes)
                                        <p class="text-sm text-ink-700 leading-relaxed mt-3">{{ $p->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif

            {{-- Empty state --}}
            @if ($passages->isEmpty())
                <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 p-6 text-center">
                    <p class="text-ink-500">Aucun passage enregistré pour le moment.</p>
                </div>
            @endif
        </section>

        {{-- Mes documents — contrat & factures (feedback Pierre : « récupérer ici son contrat, ses factures »).
             Teaser : la facturation sera branchée plus tard (voir réponse admin). --}}
        <section>
            <h2 class="font-display font-semibold text-xl text-ink-950 mb-4">Mes documents</h2>
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 divide-y divide-sand-100 overflow-hidden">
                <div class="p-4 flex items-center gap-3">
                    <span class="h-10 w-10 rounded-xl bg-azure-50 text-azure-600 grid place-items-center shrink-0">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h4"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-display font-semibold text-ink-900">Contrat d'entretien</p>
                        <p class="text-sm text-ink-500">Disponible avec la mise en place de la facturation.</p>
                    </div>
                    <span class="text-xs font-semibold text-ink-400 bg-sand-100 rounded-full px-2.5 py-1 shrink-0">Bientôt</span>
                </div>
                <div class="p-4 flex items-center gap-3">
                    <span class="h-10 w-10 rounded-xl bg-lagon-500/12 text-lagon-600 grid place-items-center shrink-0">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 11v6M9.5 13.2c0-.8.7-1.2 1.6-1.2 1.6 0 1.8 2.1.4 2.4-1.6.3-1.6 2.4 0 2.4 1 0 1.6-.4 1.6-1.2"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-display font-semibold text-ink-900">Factures</p>
                        <p class="text-sm text-ink-500">Disponible avec la mise en place de la facturation.</p>
                    </div>
                    <span class="text-xs font-semibold text-ink-400 bg-sand-100 rounded-full px-2.5 py-1 shrink-0">Bientôt</span>
                </div>
            </div>
        </section>

        {{-- CTA WhatsApp --}}
        <section class="rounded-2xl bg-azure-50 ring-1 ring-azure-200/60 p-5">
            <p class="font-display font-semibold text-ink-950">Une question sur votre eau ?</p>
            <p class="text-sm text-ink-600 mt-1">On vous répond directement.</p>
            <a
                href="https://wa.me/596696940054"
                class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-[#25D366] text-white font-bold mt-3 shadow-sm hover:opacity-90 transition-opacity"
                target="_blank"
                rel="noopener noreferrer"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.462 3.49"/>
                </svg>
                Écrire
            </a>
        </section>

        {{-- Footer --}}
        <footer class="text-center py-6">
            <p class="text-xs text-ink-400">Dlo Azur Piscines</p>
            <p class="text-xs text-ink-400 mt-1">Connecté par lien sécurisé · aucun mot de passe</p>
        </footer>

    </main>
</div>
