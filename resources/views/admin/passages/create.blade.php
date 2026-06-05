@extends('layouts.admin')

@section('title', 'Nouveau passage · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    {{--
        Vue saisie passage offline-first (PASS-01, PASS-02, PASS-03, PASS-06).
        100% Alpine.data('passageForm') — PAS Livewire (CF-02 : Livewire exige le réseau).
        L'état est persisté en IndexedDB (store 'passages' + 'photos') via offline-queue.js.
        La synchronisation est déclenchée au submit, au retour online et au visibilitychange.
    --}}
    <div
        x-data="passageForm({ clientId: {{ $client?->id ?? 'null' }}, piscineId: {{ $piscine?->id ?? 'null' }}, clients: @js($clients ?? []), produits: @js($produits ?? []) })"
        x-init="init()"
        class="min-h-screen bg-sand-50">

        {{-- Sticky header --}}
        <header class="sticky top-0 z-20 bg-sand-50/95 backdrop-blur border-b border-sand-200 px-4 pt-9 pb-3 flex items-center gap-3">
            <a href="{{ url()->previous() }}"
               class="h-10 w-10 rounded-xl bg-white ring-1 ring-sand-200 text-navy-700 grid place-items-center active:scale-95 shrink-0"
               aria-label="Retour">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <div class="flex-1 min-w-0">
                <p class="font-display font-semibold text-lg text-ink-950 truncate">
                    {{ $client?->name ?? 'Nouveau passage' }}
                </p>
                <p class="text-sm text-ink-500 truncate">
                    {{ ($piscine?->volume_m3 ? $piscine->volume_m3 . ' m³ · ' : '') }}{{ now()->translatedFormat('l j F') }}
                </p>
            </div>
        </header>

        <main class="px-4 pb-32 space-y-6 pt-4">

            {{-- Sélecteur client — affiché uniquement si la saisie est ouverte sans client.
                 Intégrité : un passage doit appartenir à un client (sinon il devient orphelin,
                 invisible côté portail). La liste est rendue côté serveur donc disponible hors-ligne. --}}
            @unless($client)
                <section>
                    <label for="client-select" class="block text-sm font-semibold text-ink-900 mb-1.5">Client</label>
                    <select id="client-select" x-model="clientId" @change="onClientChange()"
                            class="w-full h-12 rounded-xl bg-white ring-1 ring-sand-200 px-3 text-ink-900 focus:ring-2 focus:ring-azure-500 outline-none">
                        <option value="">Choisissez un client…</option>
                        @foreach (($clients ?? []) as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                    <template x-if="selectedClientPiscines.length > 1">
                        <select x-model="piscineId"
                                class="w-full h-12 mt-2 rounded-xl bg-white ring-1 ring-sand-200 px-3 text-ink-900 focus:ring-2 focus:ring-azure-500 outline-none">
                            <option value="">Choisissez une piscine…</option>
                            <template x-for="p in selectedClientPiscines" :key="p.id">
                                <option :value="p.id" x-text="p.nom"></option>
                            </template>
                        </select>
                    </template>
                </section>
            @endunless

            {{-- Bandeau hors-ligne (Alpine) — UI-SPEC §Bandeau hors-ligne --}}
            <div x-show="!online"
                 x-transition.opacity.duration.150ms
                 role="status"
                 aria-live="polite"
                 class="flex items-start gap-3 rounded-2xl p-3.5"
                 style="background-color: var(--warn-bg); box-shadow: inset 0 0 0 1px oklch(0.85 0.09 82);">
                <span class="h-9 w-9 shrink-0 rounded-full grid place-items-center"
                      style="background-color: oklch(0.90 0.07 82); color: oklch(0.5 0.11 72);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="1" y1="1" x2="23" y2="23"/>
                        <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55M5 12.55a10.94 10.94 0 0 1 5.17-2.39M10.71 5.05A16 16 0 0 1 22.58 9M1.42 9a15.91 15.91 0 0 1 4.7-2.88M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01"/>
                    </svg>
                </span>
                <div>
                    <p class="font-semibold text-sm" style="color: oklch(0.42 0.10 70);">
                        Hors ligne · votre saisie est sauvegardée
                    </p>
                    <p class="text-xs leading-snug" style="color: oklch(0.5 0.08 72);">
                        Elle partira automatiquement au retour du réseau.
                    </p>
                </div>
            </div>

            {{-- Section Mesures de l'eau — UI-SPEC §Section Mesures, steppers h-12 ≥ 44px --}}
            <section>
                <h2 class="font-display font-semibold text-lg text-ink-950 mb-3">Mesures de l'eau</h2>
                <div class="grid grid-cols-2 gap-3">

                    @foreach ([
                        ['field' => 'ph_avant',     'label' => 'pH',           'step' => 0.1, 'precision' => 1, 'unit' => ''],
                        ['field' => 'ph_apres',     'label' => 'pH après',     'step' => 0.1, 'precision' => 1, 'unit' => ''],
                        ['field' => 'chlore_libre', 'label' => 'Chlore libre', 'step' => 0.1, 'precision' => 1, 'unit' => 'mg/L'],
                        ['field' => 'chlore_total', 'label' => 'Chlore total', 'step' => 0.1, 'precision' => 1, 'unit' => 'mg/L'],
                        ['field' => 'tac',          'label' => 'TAC',          'step' => 5,   'precision' => 0, 'unit' => 'mg/L'],
                        ['field' => 'sel_g_l',      'label' => 'Sel',          'step' => 0.1, 'precision' => 1, 'unit' => 'g/L'],
                        ['field' => 'th',           'label' => 'TH',           'step' => 1,   'precision' => 0, 'unit' => '°f'],
                    ] as $m)
                        @php($f = $m['field'])
                        <div class="rounded-2xl bg-white ring-1 ring-sand-200 p-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-semibold text-ink-900">{{ $m['label'] }}</span>
                                @if($m['unit'])
                                    <span class="text-xs text-ink-400">{{ $m['unit'] }}</span>
                                @endif
                            </div>
                            <div class="flex items-stretch gap-1.5 mt-2">
                                {{-- Stepper − (h-12 = 48px ≥ 44px touch target) --}}
                                <button
                                    type="button"
                                    @click="decr('{{ $f }}', {{ $m['step'] }}, {{ $m['precision'] }})"
                                    class="w-14 h-14 rounded-xl bg-sand-100 ring-1 ring-sand-200 text-ink-700 active:scale-95 grid place-items-center text-xl font-bold"
                                    aria-label="Diminuer {{ $m['label'] }}">−</button>
                                {{-- Valeur : saisie directe au clavier numérique (inputmode=decimal),
                                     les steppers ± servant au réglage fin. Virgule FR ou point acceptés. --}}
                                <input
                                    type="text"
                                    inputmode="decimal"
                                    x-model="{{ $f }}"
                                    placeholder="·"
                                    aria-label="{{ $m['label'] }}{{ $m['unit'] ? ' en '.$m['unit'] : '' }}"
                                    class="flex-1 w-full min-w-0 h-12 rounded-xl bg-sand-50 ring-1 ring-sand-100 text-center font-display font-bold text-xl text-ink-950 tabular-nums focus:ring-2 focus:ring-azure-500 outline-none">
                                {{-- Stepper + --}}
                                <button
                                    type="button"
                                    @click="incr('{{ $f }}', {{ $m['step'] }}, {{ $m['precision'] }})"
                                    class="w-14 h-14 rounded-xl bg-azure-500 text-white active:scale-95 grid place-items-center text-xl font-bold"
                                    aria-label="Augmenter {{ $m['label'] }}">+</button>
                            </div>
                        </div>
                    @endforeach

                </div>
            </section>

            {{-- Section Actions menées — UI-SPEC §Section Actions, h-12 touch target --}}
            <section>
                <h2 class="font-display font-semibold text-lg text-ink-950 mb-3">Actions menées</h2>
                <div class="space-y-2">
                    <template x-for="action in actionsAvailable" :key="action">
                        <label
                            class="flex items-center gap-3 h-12 px-3.5 rounded-xl cursor-pointer transition-colors"
                            :class="isActionSelected(action)
                                ? 'bg-azure-50 ring-1 ring-azure-200'
                                : 'bg-white ring-1 ring-sand-200'">
                            {{-- Checkbox custom --}}
                            <span
                                class="h-6 w-6 rounded-md shrink-0 grid place-items-center"
                                :class="isActionSelected(action)
                                    ? 'bg-azure-500 text-white'
                                    : 'bg-white ring-1 ring-sand-300'">
                                <template x-if="isActionSelected(action)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="3"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </template>
                            </span>
                            <input
                                type="checkbox"
                                class="sr-only"
                                :checked="isActionSelected(action)"
                                @change="toggleAction(action)"
                                :aria-label="action">
                            <span
                                class="font-medium"
                                :class="isActionSelected(action) ? 'text-ink-900' : 'text-ink-700'"
                                x-text="action"></span>
                        </label>
                    </template>
                </div>
            </section>

            {{-- Section Produits utilisés — chimie consommée (admin-5, offline-first Alpine+IndexedDB) --}}
            <section>
                <h2 class="font-display font-semibold text-lg text-ink-950 mb-3">Produits utilisés
                    <span class="text-sm font-normal text-ink-400">(optionnel)</span>
                </h2>
                <div class="space-y-2">
                    <template x-for="p in produitsDisponibles" :key="p.id">
                        <label
                            class="flex items-center gap-3 h-12 px-3.5 rounded-xl cursor-pointer transition-colors"
                            :class="isProduitSelected(p.id)
                                ? 'bg-azure-50 ring-1 ring-azure-200'
                                : 'bg-white ring-1 ring-sand-200'">
                            {{-- Checkbox custom --}}
                            <span
                                class="h-6 w-6 rounded-md shrink-0 grid place-items-center"
                                :class="isProduitSelected(p.id)
                                    ? 'bg-azure-500 text-white'
                                    : 'bg-white ring-1 ring-sand-300'">
                                <template x-if="isProduitSelected(p.id)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="3"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </template>
                            </span>
                            <input
                                type="checkbox"
                                class="sr-only"
                                :checked="isProduitSelected(p.id)"
                                @change="toggleProduit(p.id)"
                                :aria-label="p.libelle">
                            <span
                                class="font-medium flex-1"
                                :class="isProduitSelected(p.id) ? 'text-ink-900' : 'text-ink-700'"
                                x-text="p.libelle"></span>
                            {{-- Quantité — visible seulement quand le produit est coché --}}
                            <input
                                x-show="isProduitSelected(p.id)"
                                x-cloak
                                type="number"
                                inputmode="decimal"
                                min="0"
                                step="0.1"
                                @click.stop
                                x-model="produitQuantites[p.id]"
                                placeholder="Qté"
                                class="w-16 h-8 rounded-lg bg-sand-50 ring-1 ring-sand-200 text-center text-sm text-ink-900 focus:ring-2 focus:ring-azure-500 outline-none">
                        </label>
                    </template>
                    <p x-show="produitsDisponibles.length === 0" class="text-sm text-ink-400 px-1">
                        Aucun produit disponible.
                    </p>
                </div>
            </section>

            {{-- Section Photos — UI-SPEC §Section Photos, miniatures aspect-square, caméra arrière --}}
            <section>
                <h2 class="font-display font-semibold text-lg text-ink-950 mb-3">Photos</h2>
                <div class="grid grid-cols-3 gap-2.5">

                    <template x-for="photo in photos" :key="photo.clientUuid">
                        <div class="relative aspect-square rounded-xl overflow-hidden ring-1 ring-sand-200 bg-sand-100 min-h-20">
                            <img
                                :src="photo.previewUrl"
                                :alt="`Photo passage`"
                                class="w-full h-full object-cover"
                                loading="lazy">
                            {{-- Badge statut (Envoyée/En attente/Erreur) --}}
                            <span
                                class="absolute bottom-1 left-1 rounded-md text-[10px] font-bold px-1.5 py-0.5"
                                :class="{
                                    'bg-success text-white':                                        photo.status === 'synced',
                                    'bg-warn text-[oklch(0.32_0.09_70)]':                          photo.status === 'pending' || photo.status === 'uploading',
                                    'bg-danger text-white':                                         photo.status === 'error',
                                    'bg-sand-200 text-ink-500':                                     photo.status === 'processing',
                                }"
                                x-text="{
                                    pending:    'En attente',
                                    uploading:  'En cours',
                                    synced:     'Envoyée',
                                    error:      'Erreur',
                                    processing: 'Traitement…'
                                }[photo.status] ?? photo.status"></span>
                        </div>
                    </template>

                    {{-- Bouton Ajouter photo (caméra arrière — UI-SPEC §Interaction Contracts Photos) --}}
                    <label class="aspect-square min-h-20 rounded-xl border-2 border-dashed border-azure-300 bg-azure-50/50 grid place-items-center text-azure-600 active:scale-95 cursor-pointer">
                        <input
                            type="file"
                            accept="image/*"
                            capture="environment"
                            multiple
                            class="sr-only"
                            @change="onPhotoSelect($event)"
                            aria-label="Ajouter des photos du passage">
                        <div class="flex flex-col items-center gap-1">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            <span class="text-xs font-semibold">Ajouter</span>
                        </div>
                    </label>

                </div>
            </section>

            {{-- Section Notes — UI-SPEC §Section Notes --}}
            <section class="space-y-4">
                <div>
                    <label for="notes" class="block text-sm font-semibold text-ink-900 mb-1.5">
                        Mot pour le client
                    </label>
                    <textarea
                        id="notes"
                        x-model="notes"
                        rows="3"
                        placeholder="Eau parfaitement équilibrée, rien à signaler."
                        class="w-full rounded-xl bg-white ring-1 ring-sand-200 p-3 text-ink-900 placeholder:text-ink-400 focus:ring-2 focus:ring-azure-500 outline-none resize-none"></textarea>
                </div>
                <div>
                    <label for="notes-privees" class="block text-sm font-semibold text-ink-900 mb-1.5">
                        Note interne
                        <span class="text-ink-400 font-normal">(privée, non visible par le client)</span>
                    </label>
                    <textarea
                        id="notes-privees"
                        x-model="notesPrivees"
                        rows="3"
                        placeholder="Prévoir cartouche de filtration au prochain passage."
                        class="w-full rounded-xl bg-white ring-1 ring-sand-200 p-3 text-ink-900 placeholder:text-ink-400 focus:ring-2 focus:ring-azure-500 outline-none resize-none"></textarea>
                </div>
            </section>

        </main>

        {{-- Sticky save-bar — UI-SPEC §Sticky save-bar, h-13 = 52px --}}
        <div class="sticky bottom-0 bg-sand-50/95 backdrop-blur border-t border-sand-200 px-4 py-3 space-y-2"
             style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
            <div class="text-xs text-ink-500 flex items-center gap-1.5">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" class="text-lagon-600" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Brouillon sauvegardé automatiquement
            </div>
            <button
                type="button"
                @click="submit()"
                :disabled="saving"
                class="w-full h-13 rounded-xl bg-azure-500 text-white font-bold text-base shadow-md hover:bg-azure-600 active:scale-[0.99] transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!saving">Enregistrer le passage</span>
                <span x-show="saving" x-cloak>Enregistrement…</span>
            </button>
        </div>

        {{-- Toasts warnings mesures (soft validation D-63) — UI-SPEC §Toast warnings --}}
        <div class="fixed bottom-20 left-4 right-4 z-50 space-y-2 pointer-events-none" aria-live="polite">
            <template x-for="warning in warnings" :key="warning.id">
                <div class="rounded-2xl ring-1 px-4 py-3 flex items-center gap-2.5 shadow-md pointer-events-auto"
                     style="background-color: var(--warn-bg); box-shadow: inset 0 0 0 1px oklch(0.75 0.10 75);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" style="color: oklch(0.5 0.11 72);" class="shrink-0" aria-hidden="true">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <p class="text-sm font-semibold" style="color: oklch(0.42 0.10 70);" x-text="warning.msg"></p>
                </div>
            </template>
        </div>

        {{-- Toast erreur 409 (passage déjà clos — D-40) --}}
        <div x-show="conflictMsg" x-cloak class="fixed bottom-20 left-4 right-4 z-50">
            <div class="rounded-2xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3 flex items-start gap-2.5 shadow-md">
                <p class="text-sm font-semibold text-danger flex-1" x-text="conflictMsg"></p>
                <button
                    type="button"
                    @click="dismissConflict()"
                    class="text-danger h-6 w-6 grid place-items-center shrink-0"
                    aria-label="Fermer">✕</button>
            </div>
        </div>

        {{-- Toast refus serveur permanent (422 — valeurs hors limites). Affiché au lieu de
             l'écran « enregistré » : l'opérateur corrige la saisie et réenregistre. --}}
        <div x-show="uploadError" x-cloak class="fixed bottom-20 left-4 right-4 z-50" role="alert" aria-live="assertive">
            <div class="rounded-2xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3 flex items-start gap-2.5 shadow-md">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" class="text-danger shrink-0 mt-0.5" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p class="text-sm font-semibold text-danger flex-1" x-text="uploadError"></p>
                <button
                    type="button"
                    @click="dismissUploadError()"
                    class="text-danger h-6 w-6 grid place-items-center shrink-0"
                    aria-label="Fermer">✕</button>
            </div>
        </div>

        {{-- Écran de confirmation après enregistrement — le moment « c'est fait, tu peux ranger le téléphone ». --}}
        <div x-show="saved" x-cloak
             class="fixed inset-0 z-[60] bg-sand-50 flex flex-col items-center justify-center text-center px-6"
             style="padding-bottom: env(safe-area-inset-bottom);">
            <span class="h-16 w-16 rounded-full grid place-items-center mb-5"
                  :class="saveResult === 'synced' ? 'bg-success/15 text-success' : 'text-[oklch(0.5_0.11_72)]'"
                  :style="saveResult === 'synced' ? '' : 'background-color: var(--warn-bg);'">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </span>
            <p class="font-display font-bold text-2xl text-ink-950">Passage enregistré</p>
            <p class="text-ink-600 mt-2 max-w-xs" x-show="saveResult === 'synced'">
                Synchronisé. Ton client peut déjà le consulter.
            </p>
            <p class="text-ink-600 mt-2 max-w-xs" x-show="saveResult === 'queued'" x-cloak>
                Sauvegardé sur ce téléphone. Il partira tout seul au retour du réseau.
            </p>
            <div class="flex flex-col gap-3 mt-8 w-full max-w-xs">
                <a href="{{ route('admin.dashboard') }}"
                   class="h-13 rounded-xl bg-azure-500 text-white font-bold grid place-items-center active:scale-[0.99]">Terminé</a>
                <a href="{{ route('admin.passages.create') }}"
                   class="h-12 rounded-xl bg-white ring-1 ring-sand-200 text-ink-700 font-semibold grid place-items-center">Saisir un autre passage</a>
            </div>
        </div>

        {{-- Mobile bottom navigation --}}
        <x-admin.mobile-bottom-nav />

        {{-- Sync drawer is mounted once in layouts/admin.blade.php (P0 SC-1) --}}

        {{-- Toast mise à jour PWA --}}
        <x-admin.pwa-update-toast />

    </div>
@endsection
