@extends('layouts.app')

@section('content')
    <div class="pt-32 pb-20 mx-auto max-w-content px-5 sm:px-8">
        <div class="max-w-2xl mx-auto prose prose-ink">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Mentions légales</h1>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Éditeur du site</h2>
            <p class="text-ink-700">
                <strong>Dlo Azur Piscines</strong><br>
                Pierre ADAM — Pisciniste indépendant<br>
                Martinique (972)<br>
                Téléphone : <a href="tel:+596696940054" class="text-azure-600 hover:text-azure-700">0696 94 00 54</a><br>
                E-mail : <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>
            </p>
            <p class="text-ink-700">
                {{-- TODO: Pierre à compléter avant cutover --}}
                SIRET : <em>[À compléter par Pierre ADAM avant lancement]</em><br>
                RCS : <em>[À compléter par Pierre ADAM avant lancement]</em>
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Hébergement</h2>
            <p class="text-ink-700">
                Ce site est hébergé par <strong>Laravel Cloud</strong>, une infrastructure opérée par Laravel LLC.<br>
                Région : <strong>EU — Francfort, Allemagne</strong> (conformité RGPD assurée — données stockées dans l'Union Européenne).<br>
                Base de données managée : Neon Inc. (PostgreSQL) — région EU/Francfort.<br>
                Médias : Scaleway Object Storage — région Paris, France.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Responsable de la publication</h2>
            <p class="text-ink-700">Pierre ADAM — contact@dloazurpiscines.com</p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Délégué à la protection des données (DPO)</h2>
            <p class="text-ink-700">
                Pour toute question relative à vos données personnelles :<br>
                <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Propriété intellectuelle</h2>
            <p class="text-ink-700">
                L'ensemble du contenu de ce site (textes, photos, logos) est la propriété exclusive de Dlo Azur Piscines / Pierre ADAM, sauf mention contraire. Toute reproduction ou représentation sans autorisation est interdite.
            </p>
        </div>
    </div>
@endsection
