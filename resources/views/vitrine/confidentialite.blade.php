@extends('layouts.app')

@section('content')
    <div class="pt-32 pb-20 mx-auto max-w-content px-5 sm:px-8">
        <div class="max-w-2xl mx-auto prose prose-ink">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Politique de confidentialité</h1>
            <p class="text-ink-500">Dernière mise à jour : {{ date('d/m/Y') }}</p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Responsable du traitement</h2>
            <p class="text-ink-700">
                <strong>Dlo Azur Piscines</strong> — Pierre ADAM<br>
                Martinique (972)<br>
                E-mail : <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Données collectées</h2>
            <p class="text-ink-700">
                <strong>Phase 1 (actuelle) :</strong> Le formulaire de contact collecte nom, prénom, adresse e-mail, téléphone (facultatif) et message. Ces données sont utilisées uniquement pour répondre à votre demande et ne sont pas conservées de façon permanente (D-13).
            </p>
            <p class="text-ink-700">
                <strong>Phase 2 (à venir) :</strong> Les données de suivi d'interventions (mesures eau, photos, historique de passages) seront associées à votre compte client sécurisé.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Hébergement et sécurité</h2>
            <p class="text-ink-700">
                Toutes les données sont hébergées dans l'Union Européenne :
            </p>
            <ul class="text-ink-700">
                <li><strong>Application et base de données :</strong> Laravel Cloud / Neon — région EU/Francfort, Allemagne</li>
                <li><strong>Photos et médias :</strong> Scaleway Object Storage — région Paris, France</li>
            </ul>
            <p class="text-ink-700">
                La communication est chiffrée par TLS (HTTPS). Aucune donnée n'est transmise à des tiers à des fins commerciales.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Cookies</h2>
            <p class="text-ink-700">
                Ce site n'utilise pas de cookies tiers. Le seul cookie présent est le cookie de session Laravel (sécurisé, HttpOnly, SameSite=Lax), nécessaire au fonctionnement de l'application.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Vos droits (RGPD)</h2>
            <p class="text-ink-700">
                Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez des droits d'accès, rectification, effacement, portabilité et opposition. Pour exercer ces droits, contactez-nous à : <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Conservation des données</h2>
            <p class="text-ink-700">
                Les données du formulaire de contact ne sont pas stockées de façon permanente (Phase 1). Les données de compte client (Phase 2) sont conservées pendant la durée de la relation commerciale + 3 ans conformément aux obligations légales.
            </p>
        </div>
    </div>
@endsection
