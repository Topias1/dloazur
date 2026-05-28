@extends('layouts.app')

@section('content')
    <div class="pt-32 pb-20 mx-auto max-w-content px-5 sm:px-8">
        <div class="max-w-2xl mx-auto prose prose-ink">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Conditions générales de vente</h1>
            <p class="text-ink-500">Dernière mise à jour : {{ date('d/m/Y') }}</p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Objet</h2>
            <p class="text-ink-700">
                Les présentes conditions générales de vente (CGV) s'appliquent à toutes les prestations de services proposées par <strong>Dlo Azur Piscines</strong> (Pierre ADAM) en Martinique : entretien régulier de piscines, dépannage, analyse de l'eau, montage hors-sol.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Devis et commandes</h2>
            <p class="text-ink-700">
                Toute prestation fait l'objet d'un devis gratuit transmis par WhatsApp ou e-mail. Le devis est valable 30 jours. La commande est ferme après acceptation écrite (WhatsApp ou e-mail) du devis par le client.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Tarifs</h2>
            <p class="text-ink-700">
                Les prix sont exprimés en euros (€) toutes taxes comprises (TTC), applicables en Martinique (TVA 8,5 % — taux DOM). Ils peuvent être révisés sans préavis pour les nouvelles commandes.
                {{-- TODO: confirmer taux TVA avec comptable local avant premières factures --}}
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Paiement</h2>
            <p class="text-ink-700">
                Le paiement est exigible à la fin de chaque prestation, sauf accord contraire stipulé au devis. Les modes de paiement acceptés : virement bancaire, espèces, et (à terme) paiement en ligne. Tout retard de paiement entraîne des pénalités légales.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Responsabilité</h2>
            <p class="text-ink-700">
                Dlo Azur Piscines s'engage à réaliser les prestations avec soin et professionnalisme. La responsabilité du prestataire est limitée aux dommages directs causés par une faute avérée. Elle ne saurait être engagée pour des dommages indirects ou résultant d'une utilisation inappropriée de la piscine par le client.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Résiliation</h2>
            <p class="text-ink-700">
                Chaque partie peut résilier un contrat d'entretien avec un préavis de 30 jours par écrit (WhatsApp ou e-mail).
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Droit applicable</h2>
            <p class="text-ink-700">
                Les présentes CGV sont soumises au droit français. En cas de litige, les tribunaux de Fort-de-France (Martinique) sont seuls compétents.
            </p>

            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Contact</h2>
            <p class="text-ink-700">
                Pour toute question : <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>
                ou <a href="https://wa.me/596696940054" class="text-azure-600 hover:text-azure-700">WhatsApp : 0696 94 00 54</a>
            </p>
        </div>
    </div>
@endsection
