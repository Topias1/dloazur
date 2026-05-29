{{-- Services détaillés — contenu récupéré de Zyro (Plan 01-06 content recovery) --}}
{{-- 3 services avec checklists + astuces + bloc "Pourquoi nous faire confiance" --}}
<section class="bg-white border-t border-navy-900/8">
    <div class="mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-28">

        {{-- Intro --}}
        <div class="max-w-2xl mb-16">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Nos prestations en détail</h2>
            <p class="mt-4 text-lg leading-relaxed text-ink-700">
                Expertise locale adaptée au climat antillais : humidité, brume de sable, ensoleillement toute l'année. Solutions sur mesure pour chaque besoin, ponctuel ou régulier.
            </p>
        </div>

        {{-- 3 services --}}
        <div class="space-y-16">

            {{-- ── Service 1 : Nettoyage & remise en état ── --}}
            <article class="grid lg:grid-cols-[1fr_1.6fr] gap-10 items-start" id="service-nettoyage">
                <div>
                    <span class="inline-grid h-12 w-12 place-items-center rounded-xl bg-azure-50 text-azure-600 mb-5">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-6 6a2 2 0 1 0 3 3l6-6a4 4 0 0 0 5.4-5.4l-2.6 2.6-2-2 2.6-2.6Z"/></svg>
                    </span>
                    <h3 class="font-display font-bold text-2xl sm:text-3xl text-ink-950">Nettoyage &amp; remise en état</h3>
                    <p class="mt-4 leading-relaxed text-ink-700">
                        Le climat tropical de la Martinique (fort taux d'humidité, pluies intenses, brume de sable et ensoleillement permanent) peut rapidement détériorer l'état d'une piscine. Feuilles, algues, eau trouble : Dlo Azur transforme même les bassins les plus oubliés en plans d'eau limpides et accueillants.
                    </p>
                    <p class="mt-3 leading-relaxed text-ink-700">
                        Les remises en état se déroulent souvent en plusieurs passages : un premier pour gérer la chimie de l'eau et brosser les parois, les suivants pour aspirer le fond et nettoyer les équipements (skimmer, refoulement, éclairages, pompe, filtration).
                    </p>
                    <p class="mt-3 leading-relaxed text-ink-700">
                        Idéal pour : piscines laissées sans entretien plusieurs semaines, bassins affectés par les pluies tropicales ou une brume de sable, remise en état avant la saison de baignade ou un événement, ainsi que toute réparation de filtration (électrolyseur, filtre, pompe).
                    </p>
                </div>
                <div class="space-y-6">
                    {{-- Checklist Nettoyage intensif --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Nettoyage intensif
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Élimination des feuilles, insectes et débris flottants</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Brossage des parois et du fond pour enlever dépôts et algues</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Aspiration des saletés incrustées avec équipements professionnels</li>
                        </ul>
                    </div>
                    {{-- Checklist Traitement eau --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Traitement de l'eau
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Analyse et ajustement des paramètres chimiques : pH, chlore ou sel, alcalinité, stabilisant</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Chloration choc et traitement anti-algues si nécessaire</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Prévention des eaux troubles ou verdâtres, courantes sous notre climat</li>
                        </ul>
                    </div>
                    {{-- Checklist Révision équipements --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Révision complète des équipements
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Vérification et entretien de la pompe et du filtre</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Contrôle des skimmers et buses de refoulement pour une circulation optimale</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Rinçage et nettoyage du filtre pour éviter l'accumulation de saletés</li>
                        </ul>
                    </div>
                    {{-- Astuce --}}
                    <div class="rounded-2xl bg-azure-50 border border-azure-200/60 p-5">
                        <p class="text-azure-800 text-sm leading-relaxed">
                            <span class="font-semibold">💡 Astuce Dlo Azur :</span> Après un nettoyage intensif, un entretien régulier prolonge la propreté et la clarté de l'eau sans effort supplémentaire.
                        </p>
                    </div>
                </div>
            </article>

            <hr class="border-navy-900/8">

            {{-- ── Service 2 : Entretien hebdomadaire ── --}}
            <article class="grid lg:grid-cols-[1fr_1.6fr] gap-10 items-start" id="service-entretien">
                <div>
                    <span class="inline-grid h-12 w-12 place-items-center rounded-xl bg-lagon-500/12 text-lagon-600 mb-5">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12c2-3 4-3 6 0s4 3 6 0 4-3 6 0"/><path d="M3 18c2-3 4-3 6 0s4 3 6 0 4-3 6 0"/></svg>
                    </span>
                    <h3 class="font-display font-bold text-2xl sm:text-3xl text-ink-950">Entretien hebdomadaire</h3>
                    <p class="mt-4 leading-relaxed text-ink-700">
                        Le secret d'une piscine impeccable sous le soleil martiniquais : un suivi régulier pour éviter l'accumulation d'algues, la détérioration du système de filtration et les déséquilibres chimiques. L'eau chaude, l'évaporation rapide et les fortes précipitations exigent une expertise locale.
                    </p>
                    <p class="mt-3 leading-relaxed text-ink-700">
                        Dlo Azur propose des forfaits adaptés à votre usage : interventions <strong>hebdomadaires, bimensuelles ou à la demande</strong>. Chaque traitement est ajusté aux conditions météo et aux spécificités de votre piscine.
                    </p>
                </div>
                <div class="space-y-6">
                    {{-- Checklist Nettoyage et entretien --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Nettoyage et entretien
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Enlèvement des impuretés : feuilles, insectes, sable</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Nettoyage des parois et de la ligne d'eau</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Aspiration du fond de la piscine</li>
                        </ul>
                    </div>
                    {{-- Checklist Analyse eau --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Analyse et ajustement de l'eau
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Vérification du pH, du chlore ou sel, de l'alcalinité et du stabilisant</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Ajustement des produits pour une eau saine et confortable</li>
                        </ul>
                    </div>
                    {{-- Checklist Contrôle équipements --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Contrôle des équipements
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Vérification et nettoyage du préfiltre de la pompe</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Backwash (rétrolavage) du filtre à sable pour une filtration efficace</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Contrôle des buses de refoulement et des skimmers</li>
                        </ul>
                    </div>
                    {{-- Astuce --}}
                    <div class="rounded-2xl bg-azure-50 border border-azure-200/60 p-5">
                        <p class="text-azure-800 text-sm leading-relaxed">
                            <span class="font-semibold">💡 Astuce Dlo Azur :</span> Optez pour un contrat annuel et bénéficiez d'un bassin parfaitement entretenu toute l'année, sans y penser.
                        </p>
                    </div>
                </div>
            </article>

            <hr class="border-navy-900/8">

            {{-- ── Service 3 : Montage hors sol & jacuzzi ── --}}
            <article class="grid lg:grid-cols-[1fr_1.6fr] gap-10 items-start" id="service-montage">
                <div>
                    <span class="inline-grid h-12 w-12 place-items-center rounded-xl bg-azure-50 text-azure-600 mb-5">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 20h20M4 20V9l8-5 8 5v11M9 20v-6h6v6"/></svg>
                    </span>
                    <h3 class="font-display font-bold text-2xl sm:text-3xl text-ink-950">Montage hors sol &amp; jacuzzi</h3>
                    <p class="mt-4 leading-relaxed text-ink-700">
                        Vous souhaitez installer une piscine hors sol pour vous rafraîchir rapidement sans engager de gros travaux ? Qu'il s'agisse d'une piscine <strong>en acier, en bois ou autoportante</strong>, Dlo Azur vous accompagne de A à Z pour une mise en place rapide et efficace.
                    </p>
                    <p class="mt-3 leading-relaxed text-ink-700">
                        Installation rapide et professionnelle, sécurisation, conseils d'entretien adaptés au climat tropical. Service clé en main : vous n'avez qu'à plonger et profiter.
                    </p>
                </div>
                <div class="space-y-6">
                    {{-- Checklist Conseil --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Conseil et sélection du modèle
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Aide au choix selon votre terrain, votre budget et votre utilisation</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Explication des avantages des différents types (bois, acier, autoportante)</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Conseil sur le type de filtration adapté au climat tropical</li>
                        </ul>
                    </div>
                    {{-- Checklist Préparation --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Préparation du terrain
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Vérification du nivellement du sol pour éviter les affaissements</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Mise en place d'un revêtement adapté (dalle, tapis de sol)</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Vérification du système de drainage pour éviter les stagnations en saison humide</li>
                        </ul>
                    </div>
                    {{-- Checklist Montage --}}
                    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-6">
                        <h4 class="font-display font-semibold text-base text-ink-950 mb-3 flex items-center gap-2">
                            <span aria-hidden="true">✅</span> Montage et installation
                        </h4>
                        <ul class="space-y-2 text-ink-700">
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Assemblage sécurisé de la structure</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Installation et raccordement de la pompe, du filtre et des accessoires</li>
                            <li class="flex gap-2"><span class="text-azure-500 shrink-0 mt-0.5"><x-icon.check :size="16" /></span>Test complet pour garantir un fonctionnement optimal dès le premier bain</li>
                        </ul>
                    </div>
                </div>
            </article>

        </div>{{-- /space-y-16 --}}

        {{-- ── Pourquoi nous faire confiance ── --}}
        <div class="mt-20 pt-16 border-t border-navy-900/8">
            <h2 class="font-display font-bold text-2xl sm:text-3xl text-ink-950 text-center mb-10">Pourquoi nous faire confiance ?</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 text-center hover:shadow-md hover:-translate-y-0.5 transition duration-300">
                    <span class="text-3xl block mb-3" aria-hidden="true">🏆</span>
                    <h3 class="font-display font-semibold text-lg text-ink-950 mb-2">Expertise locale Martinique</h3>
                    <p class="text-ink-700 leading-relaxed text-sm">Nous connaissons les défis spécifiques des piscines en Martinique et apportons des solutions efficaces adaptées au climat tropical.</p>
                </div>
                <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 text-center hover:shadow-md hover:-translate-y-0.5 transition duration-300">
                    <span class="text-3xl block mb-3" aria-hidden="true">🤝</span>
                    <h3 class="font-display font-semibold text-lg text-ink-950 mb-2">Service client réactif &amp; amical</h3>
                    <p class="text-ink-700 leading-relaxed text-sm">À l'écoute de vos besoins, disponible par WhatsApp 7j/7, sans standard téléphonique ni rotation d'interlocuteurs.</p>
                </div>
                <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 text-center hover:shadow-md hover:-translate-y-0.5 transition duration-300">
                    <span class="text-3xl block mb-3" aria-hidden="true">🎯</span>
                    <h3 class="font-display font-semibold text-lg text-ink-950 mb-2">Prestations sur-mesure</h3>
                    <p class="text-ink-700 leading-relaxed text-sm">Nettoyage ponctuel, entretien régulier ou installation complète : nous avons la solution adaptée à chaque piscine et chaque budget.</p>
                </div>
            </div>
        </div>

    </div>
</section>
