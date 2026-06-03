@extends('layouts.admin')

@section('title', 'Récap mensuel · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
<div class="px-5 sm:px-8 py-7 max-w-3xl space-y-6">

    {{-- En-tête + sélecteur de période --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-semibold text-xl text-ink-950">Récap mensuel</h1>
            <p class="text-sm text-ink-500 mt-0.5">
                {{ \Illuminate\Support\Carbon::create($annee, $mois, 1)->locale('fr')->isoFormat('MMMM YYYY') }}
            </p>
        </div>

        {{-- Sélecteur mois / année --}}
        <form method="GET" action="{{ route('admin.recap.index') }}"
              class="flex items-center gap-2">
            <select name="mois"
                    onchange="this.form.submit()"
                    class="h-10 rounded-xl bg-white ring-1 ring-sand-200 px-3 text-sm text-ink-900 focus:outline-none focus:ring-azure-400 cursor-pointer">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($m === $mois)>
                        {{ \Illuminate\Support\Carbon::create(null, $m)->locale('fr')->isoFormat('MMMM') }}
                    </option>
                @endforeach
            </select>

            <select name="annee"
                    onchange="this.form.submit()"
                    class="h-10 rounded-xl bg-white ring-1 ring-sand-200 px-3 text-sm text-ink-900 focus:outline-none focus:ring-azure-400 cursor-pointer">
                @foreach(range(now()->year, now()->year - 3, -1) as $y)
                    <option value="{{ $y }}" @selected($y === $annee)>{{ $y }}</option>
                @endforeach
            </select>

            <button type="submit"
                    class="h-10 px-4 rounded-xl bg-white ring-1 ring-sand-200 text-sm text-ink-700 font-semibold hover:bg-sand-50 transition-colors">
                Voir
            </button>
        </form>
    </div>

    {{-- État vide --}}
    @if($clients->isEmpty())
        <div class="rounded-2xl bg-white ring-1 ring-sand-200 shadow-xs p-10 text-center">
            <p class="text-ink-500 text-sm">Aucun passage sur cette période.</p>
        </div>
    @else

        {{-- Cards clients --}}
        @foreach($clients as $client)
            @php
                // Agréger la chimie consommée par produit sur tous les passages du mois
                $chimie = collect();
                foreach ($client->passages as $passage) {
                    foreach ($passage->produits as $produit) {
                        $key = $produit->id;
                        if ($chimie->has($key)) {
                            $entry = $chimie->get($key);
                            // Sommer les quantités quand renseignées
                            if ($produit->pivot->quantite !== null && $entry['quantite'] !== null) {
                                $entry['quantite'] += $produit->pivot->quantite;
                            } elseif ($produit->pivot->quantite !== null) {
                                $entry['quantite'] = $produit->pivot->quantite;
                            }
                            $chimie->put($key, $entry);
                        } else {
                            $chimie->put($key, [
                                'libelle'       => $produit->libelle,
                                'quantite'      => $produit->pivot->quantite,
                                'prix_snapshot' => $produit->pivot->prix_snapshot,
                                'unite'         => $produit->unite ?? '',
                            ]);
                        }
                    }
                }
            @endphp

            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6 space-y-5">

                {{-- Nom client + compteur passages --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display font-semibold text-base text-ink-900">
                            {{ $client->name }}
                        </h2>
                        <p class="text-sm text-ink-500 mt-0.5">
                            {{ $client->nb_passages }}
                            {{ $client->nb_passages > 1 ? 'passages' : 'passage' }}
                            ce mois
                        </p>
                    </div>

                    {{-- Bouton inerte — teaser Phase 3 facturation --}}
                    <span class="inline-flex items-center gap-2 h-10 px-4 rounded-xl
                                 bg-sand-100 text-ink-400 text-sm font-semibold
                                 cursor-not-allowed select-none"
                          aria-disabled="true"
                          title="Disponible lors de la Phase facturation">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        Générer la facture
                        <span class="text-xs font-normal opacity-70">(bientôt — Phase facturation)</span>
                    </span>
                </div>

                {{-- Chimie consommée --}}
                <div>
                    <h3 class="text-xs text-ink-500 uppercase tracking-wider font-semibold mb-3">
                        Chimie consommée
                    </h3>

                    @if($chimie->isEmpty())
                        <p class="text-sm text-ink-400 italic">Aucune chimie enregistrée ce mois.</p>
                    @else
                        <ul class="divide-y divide-sand-100">
                            @foreach($chimie as $item)
                                <li class="flex items-center justify-between py-2.5 text-sm">
                                    <span class="text-ink-900 font-medium">{{ $item['libelle'] }}</span>
                                    <span class="text-ink-500 tabular-nums">
                                        @if($item['quantite'] !== null)
                                            {{ number_format($item['quantite'], 2, ',', ' ') }}
                                            {{ $item['unite'] }}
                                        @else
                                            —
                                        @endif
                                        @if($item['prix_snapshot'] !== null)
                                            &nbsp;·&nbsp;
                                            {{ number_format($item['prix_snapshot'], 2, ',', ' ') }} €&nbsp;<span class="text-xs text-ink-400">HT</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

            </div>
        @endforeach

    @endif

</div>

<x-admin.mobile-bottom-nav />
@endsection
