@extends('layouts.app')

@section('content')
    <div class="pt-32 pb-20 mx-auto max-w-content px-5 sm:px-8">
        <div class="max-w-2xl mx-auto prose prose-ink">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Conditions générales de vente</h1>
            <p class="text-ink-500">Dernière mise à jour : mai 2025</p>

            {{-- Identité du prestataire --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Identité du prestataire</h2>
            <p class="text-ink-700">
                <strong>DLO AZUR EI</strong> (entreprise individuelle)<br>
                Pierre ADAM, pisciniste indépendant<br>
                29 montée du Clapotage, 97231 Le Robert, Martinique<br>
                SIRET : 934 053 281 000 10<br>
                Téléphone : <a href="tel:+596696940054" class="text-azure-600 hover:text-azure-700">0696 94 00 54</a><br>
                E-mail : <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>
            </p>
            {{-- Note TVA : Pierre ADAM est en franchise de base de TVA (art. 293 B CGI).
                 La mention "TVA non applicable — art. 293 B du CGI" doit figurer sur les factures.
                 Les prix sont exprimés HT. --}}

            {{-- Objet --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Objet</h2>
            <p class="text-ink-700">
                Les présentes conditions générales de vente (CGV) s'appliquent à toutes les prestations de services proposées par <strong>Dlo Azur Piscines</strong> (Pierre ADAM) en Martinique : entretien régulier de piscines, nettoyage et remise en état, dépannage, analyse de l'eau, montage de piscines hors-sol et jacuzzis.
            </p>

            {{-- Devis et commandes --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Devis et commandes</h2>
            <p class="text-ink-700">
                Toute prestation fait l'objet d'un devis gratuit transmis par WhatsApp ou e-mail. Le devis est valable 30 jours. La commande est ferme après acceptation écrite (WhatsApp ou e-mail) du devis par le client.
            </p>

            {{-- Tarifs --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Tarifs</h2>
            <p class="text-ink-700">
                Les prix sont exprimés en euros (€). En application de l'article 293 B du Code général des impôts, Pierre ADAM bénéficie de la franchise en base de TVA : <strong>TVA non applicable, art. 293 B du CGI</strong>. Les prix indiqués sur les devis sont les prix définitifs, sans TVA à ajouter. Ils peuvent être révisés sans préavis pour les nouvelles commandes.
            </p>

            {{-- Paiement --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Paiement</h2>
            <p class="text-ink-700">
                Le paiement est exigible à la fin de chaque prestation, sauf accord contraire stipulé au devis. Les modes de paiement acceptés : virement bancaire, espèces. Tout retard de paiement entraîne des pénalités de retard conformément aux dispositions légales applicables.
            </p>

            {{-- Exécution des prestations --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Exécution des prestations</h2>
            <p class="text-ink-700">
                Les prestations sont réalisées personnellement par Pierre ADAM, sans sous-traitance. Les dates d'intervention sont convenues d'un commun accord par WhatsApp ou e-mail. En cas d'empêchement (conditions météorologiques exceptionnelles, urgence personnelle), le prestataire s'engage à informer le client dans les meilleurs délais et à proposer un report.
            </p>

            {{-- Responsabilité --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Responsabilité</h2>
            <p class="text-ink-700">
                Dlo Azur Piscines s'engage à réaliser les prestations avec soin et professionnalisme. La responsabilité du prestataire est limitée aux dommages directs causés par une faute avérée dans l'exécution des travaux. Elle ne saurait être engagée pour des dommages indirects ou résultant d'une utilisation inappropriée de la piscine par le client, d'une défaillance des équipements non signalée, ou de conditions climatiques exceptionnelles.
            </p>

            {{-- Résiliation --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Résiliation</h2>
            <p class="text-ink-700">
                Chaque partie peut résilier un contrat d'entretien régulier avec un préavis de 30 jours par écrit (WhatsApp ou e-mail). Pour les prestations ponctuelles, toute annulation moins de 48h avant l'intervention convenue pourra faire l'objet d'une facturation du déplacement.
            </p>

            {{-- Propriété intellectuelle --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Propriété intellectuelle</h2>
            <p class="text-ink-700">
                L'ensemble des contenus présents sur le site dloazurpiscines.com (textes, photos, logos, rapports de passage) est la propriété exclusive de DLO AZUR / Pierre ADAM. Toute reproduction, modification ou distribution sans autorisation écrite est interdite.
            </p>

            {{-- Données personnelles --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Données personnelles</h2>
            <p class="text-ink-700">
                Les données collectées (nom, e-mail, téléphone, adresse de la piscine) sont utilisées exclusivement pour la gestion des interventions et la relation commerciale. Aucune donnée n'est transmise à des tiers sans consentement explicite. Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression : <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>.
            </p>

            {{-- Médiation --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Médiation et règlement des litiges</h2>
            <p class="text-ink-700">
                En cas de litige, le client est invité à contacter Dlo Azur Piscines par e-mail ou WhatsApp pour trouver une solution amiable. À défaut de résolution amiable, le client peut faire appel à un médiateur de la consommation conformément aux articles L.611-1 et suivants du Code de la consommation.
            </p>

            {{-- Droit applicable --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Droit applicable</h2>
            <p class="text-ink-700">
                Les présentes CGV sont soumises au droit français. En cas de litige non résolu par voie amiable, les tribunaux de Fort-de-France (Martinique) sont seuls compétents.
            </p>

            {{-- Contact --}}
            <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Contact</h2>
            <p class="text-ink-700">
                Pour toute question relative aux présentes CGV :<br>
                <a href="mailto:contact@dloazurpiscines.com" class="text-azure-600 hover:text-azure-700">contact@dloazurpiscines.com</a>
                ou <a href="https://wa.me/596696940054" class="text-azure-600 hover:text-azure-700">WhatsApp : 0696 94 00 54</a>
            </p>
        </div>
    </div>
@endsection
