<?php

namespace App\Livewire;

use App\Mail\DiagnosticLead;
use App\Models\Diagnostic;
use App\Services\Diagnostic\DoseEngine;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

/**
 * Diagnostic piscine — composant Livewire pleine page (DIAG-01, D-01, Plans 05-01/05-03)
 *
 * Architecture (D-01) :
 *   - Livewire : état serveur (disclaimerAccepted, champs mesures, champs lead, piscineId, mode)
 *   - Alpine   : navigation entre les étapes (step, nodeId, mode) — JAMAIS lié à Livewire
 *
 * DIAG-02 : aucune formule arithmétique dans ce composant ni dans la vue Blade.
 *           Seul DoseEngine (Plan 05-02) calcule les doses côté serveur.
 *
 * DIAG-03 : disclaimerAccepted doit être true avant toute sortie de dosage.
 *           Le guard server-side re-vérifie avant tout calcul/persistance (D-04).
 *
 * D-03 / additive : les colonnes lead (prenom/commune/email/site_web) sont sur la table
 *                   diagnostics (migration 05-03) — pas de modèle Lead séparé.
 */
final class DiagnosticWizard extends Component
{
    use WithRateLimiting, UsesSpamProtection;

    // ── État persistant côté serveur ─────────────────────────────────────────

    /**
     * Disclaimer explicitement accepté (DIAG-03, D-04).
     * Guard serveur : computeAndPersist() vérifie ce flag avant toute opération.
     */
    public bool $disclaimerAccepted = false;

    /**
     * Actions déjà tentées par l'utilisateur (BLUEPRINT §6, DIAG-01 action-aware).
     * Synchronisé via wire:model ou updateTriedActions().
     */
    public array $triedActions = [];

    /**
     * Mode de diagnostic : 'symptom' | 'chemistry' | null.
     * Utilisé côté serveur pour created_via.
     */
    public ?string $mode = null;

    /**
     * Identifiant de piscine pré-rempli si client connecté.
     */
    public ?int $piscineId = null;

    /**
     * Hint type de filtre pré-rempli depuis la fiche piscine (D-08 gap).
     * Valeur libre-texte — non-autoritaire ; l'utilisateur peut l'override.
     */
    public ?string $filtrationHint = null;

    /**
     * ID du Diagnostic persisté — utilisé pour le lien PDF (D-06).
     */
    public ?int $savedDiagnosticId = null;

    /**
     * Recommandations calculées par DoseEngine (serveur uniquement, DIAG-02).
     */
    public array $recommandations = [];

    /**
     * Un calcul de doses a été effectué (computeDoses).
     * Découple l'affichage des résultats de la persistance (savedDiagnosticId).
     * true = doses calculées et visibles ; la persistance reste explicite (keepDiagnostic).
     */
    public bool $hasComputed = false;

    /**
     * State succès du formulaire lead (S7).
     */
    public bool $leadSent = false;

    /**
     * Nœud/feuille atteint dans l'arbre de décision symptôme.
     * Défini quand l'utilisateur navigue via le parcours symptôme.
     * Utilisé par l'escalade préemptive basée sur les flags de l'arbre.
     */
    public ?string $symptomResultId = null;

    /**
     * Niveau d'escalade calculé : 'preemptif' | 'reactif' | 'aucun'.
     * Calculé dans computeEscalade() ou après computeAndPersist().
     */
    public string $escaladeNiveau = 'aucun';

    /**
     * Raison de l'escalade (pour l'affichage et le test).
     * Ex: 'acide-chlorhydrique', '230V', 'electro-usee', 'echec-retest'.
     */
    public ?string $escaladeRaison = null;

    /**
     * Hook réactif : Plan 06 peut mettre ce flag à true après un re-test
     * infructueux pour déclencher l'escalade réactive.
     */
    public bool $retestFailed = false;

    /**
     * Indice de confiance calculé : 'eleve' | 'moyen' | 'indicatif'.
     * Calculé dans computeConfidence().
     */
    public string $confidenceIndex = 'indicatif';

    /**
     * Coordonnées optionnelles fournies par l'utilisateur (commune).
     * Intégré dans le payload WhatsApp riche.
     */
    public ?string $coordonnees = null;

    // ── Champs du wizard chimie — Step 1 : infos piscine ────────────────────

    /**
     * Mode de saisie du volume : 'volume' (direct) ou 'surface' (surface × profondeur).
     * Géré en Alpine côté client — uniquement pour le calcul côté serveur.
     */
    public string $sizeMode = 'volume';

    /**
     * Volume direct en m³ (sizeMode = 'volume').
     */
    #[Validate('nullable|numeric|min:1|max:1000')]
    public string $volume = '';

    /**
     * Surface en m² (sizeMode = 'surface').
     */
    #[Validate('nullable|numeric|min:1|max:5000')]
    public string $surface = '';

    /**
     * Profondeur en m (sizeMode = 'surface').
     */
    #[Validate('nullable|numeric|min:0.5|max:5')]
    public string $profondeur = '';

    /**
     * Type de filtre — constrained select (FLOCULANT-BRANCH-SPEC §2).
     * Autoritaire : sable / verre / cartouche / diatomees.
     * Pré-rempli depuis piscine.filtration comme hint non-autoritaire (D-08).
     */
    #[Validate('nullable|in:sable,verre,cartouche,diatomees')]
    public string $filtration = '';

    /**
     * Piscine au sel ? (oui = true, non = false).
     */
    public bool $sel = false;

    // ── Champs du wizard chimie — Step 2 : mesures ──────────────────────────

    #[Validate('nullable|numeric')]
    public string $ph = '';

    #[Validate('nullable|numeric')]
    public string $chlore = '';

    #[Validate('nullable|numeric')]
    public string $alcalinite = '';

    #[Validate('nullable|numeric')]
    public string $stabilisant = '';

    #[Validate('nullable|numeric')]
    public string $selPpm = '';

    /**
     * Champs optionnels V0-light (audit P1 intake).
     */
    #[Validate('nullable|numeric')]
    public string $chloreTotal = '';

    #[Validate('nullable|numeric')]
    public string $th = '';

    // ── Champs lead capture — S7 ─────────────────────────────────────────────

    #[Validate('required|string|max:80')]
    public string $prenom = '';

    #[Validate('required|string|max:80')]
    public string $commune = '';

    #[Validate('nullable|email|max:160')]
    public string $email = '';

    #[Validate('nullable|url|max:255')]
    public string $siteWeb = '';

    // Honeypot (spatie/laravel-honeypot)
    public HoneypotData $extraFields;

    // ──────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();

        // Pré-remplir depuis la session client connecté (D-08 gap : valeur non-autoritaire)
        if (auth('clients')->check()) {
            $client  = auth('clients')->user();
            $piscine = $client->piscines()->latest()->first();
            if ($piscine) {
                $this->piscineId      = $piscine->id;
                $this->filtrationHint = $piscine->filtration ?? null;

                // Normaliser la valeur libre-texte en valeur canonique (hint only)
                $hint = strtolower((string) ($piscine->filtration ?? ''));
                $map  = [
                    'sable'       => 'sable',
                    'verre'       => 'verre',
                    'cartouche'   => 'cartouche',
                    'diatomee'    => 'diatomees',
                    'diatomée'    => 'diatomees',
                    'diatomees'   => 'diatomees',
                    'diatomées'   => 'diatomees',
                ];
                $this->filtration = $map[$hint] ?? '';

                // Volume pré-rempli depuis la fiche piscine (hint only)
                if ($piscine->volume_m3) {
                    $this->volume = (string) $piscine->volume_m3;
                }
            }
        }
    }

    // ── Escalade + Confiance ──────────────────────────────────────────────────

    /**
     * Classifie le niveau d'escalade : 'preemptif' | 'reactif' | 'aucun'.
     *
     * Règles (BLUEPRINT §5, audit §6, CDC guard-rail 2) :
     *   1. Préemptif : la feuille d'arbre porte un flag escalade.niveau = 'preemptif'
     *      (acide chlorhydrique, 230V, cellule usée — hors-DIY particulier).
     *   2. Réactif  : le flag retestFailed a été mis à true (hook Plan 06).
     *   3. Aucun    : cas DIY facile — guard anti sur-escalade (CDC guard-rail 2).
     *
     * DIAG-02 invariant : classification pure, aucune formule de dose.
     */
    public function computeEscalade(): void
    {
        // Hook réactif (Plan 06 peut activer ce flag après un re-test raté)
        if ($this->retestFailed) {
            $this->escaladeNiveau = 'reactif';
            $this->escaladeRaison = 'echec-retest';
            return;
        }

        // Escalade préemptive basée sur le flag de la feuille de l'arbre
        if ($this->symptomResultId) {
            $results = config('diagnostic-tree.results', []);
            $feuille = $results[$this->symptomResultId] ?? null;
            if ($feuille && isset($feuille['escalade']['niveau']) && $feuille['escalade']['niveau'] === 'preemptif') {
                $this->escaladeNiveau = 'preemptif';
                $this->escaladeRaison = $feuille['escalade']['raison'] ?? null;
                return;
            }
        }

        // Aucune escalade — cas DIY standard (guard anti sur-escalade CDC guard-rail 2)
        $this->escaladeNiveau = 'aucun';
        $this->escaladeRaison = null;
    }

    /**
     * Retourne le niveau d'escalade courant en relançant le calcul.
     * Utilisé pour exposer l'état à la vue.
     */
    public function getEscalade(): string
    {
        return $this->escaladeNiveau;
    }

    /**
     * Calcule l'indice de confiance (CDC §5.5).
     *
     * Niveaux :
     *   élevé      = mesures complètes (pH + chlore + TAC fournies)
     *   moyen      = mesures partielles (pH ou chlore présent, mais manque stabilisant/TAC)
     *   indicatif  = aucune mesure chiffrée (diagnostic purement visuel/symptôme)
     *
     * DIAG-02 : classification sémantique, aucune arithmétique de dose.
     */
    public function computeConfidence(): void
    {
        $hasPh        = $this->ph !== '';
        $hasChlorine  = $this->chlore !== '';
        $hasTac       = $this->alcalinite !== '';
        $hasStab      = $this->stabilisant !== '';

        if ($hasPh && $hasChlorine && $hasTac) {
            // Élevé : mesures chimiques clés complètes
            $this->confidenceIndex = 'eleve';
        } elseif ($hasPh || $hasChlorine) {
            // Moyen : mesures partielles — affine en mesurant le stabilisant/TAC
            $this->confidenceIndex = 'moyen';
        } else {
            // Indicatif : parcours purement visuel/symptôme sans mesure
            $this->confidenceIndex = 'indicatif';
        }
    }

    /**
     * Synchronise le résultat d'arbre atteint côté Alpine pour l'escalade préemptive.
     * Appelé par Alpine quand l'utilisateur atteint une feuille de l'arbre symptôme.
     */
    public function setSymptomResult(string $resultId): void
    {
        $this->symptomResultId = $resultId;
        $this->computeEscalade();
    }

    /**
     * Active l'escalade réactive (hook Plan 06 — re-test infructueux).
     */
    public function triggerRetestFailed(): void
    {
        $this->retestFailed = true;
        $this->computeEscalade();
    }

    /**
     * Assemble le payload de contexte riche pour le lien WhatsApp (DIAG-06).
     *
     * Inclut (BLUEPRINT §5) : symptôme, mesures + fiabilité, filtre + volume,
     * actions tentées + résultats, diagnostic, confiance, coordonnées si fournies.
     *
     * Retourne un tableau — la vue Blade assemble la chaîne via implode("\n", ...).
     * DIAG-02 invariant : aucun coefficient de dosage dans ce payload.
     */
    public function richContextPayload(): array
    {
        $lines = [];
        $lines[] = "Bonjour Pierre, j'ai utilisé l'outil diagnostic de Dlo Azur Piscines.";
        $lines[] = '';

        // Symptôme / mode
        if ($this->mode === 'chemistry') {
            $lines[] = 'Parcours : Analyse chimique';
        } elseif ($this->mode === 'symptom') {
            $lines[] = 'Parcours : Diagnostic par symptôme';
        }

        // Diagnostic atteint (arbre symptôme)
        if ($this->symptomResultId) {
            $results = config('diagnostic-tree.results', []);
            $feuille = $results[$this->symptomResultId] ?? null;
            if ($feuille && !empty($feuille['diagnostic'])) {
                $lines[] = 'Diagnostic : ' . $feuille['diagnostic'];
            }
        }

        // Volume + filtre
        $vol = $this->volumeEffectif();
        if ($vol > 0) {
            $lines[] = 'Volume : ' . round($vol, 1) . ' m³';
        }
        if ($this->filtration) {
            $lines[] = 'Filtre : ' . $this->filtration;
        }

        // Mesures + fiabilité
        $mesures = $this->mesures();
        if (!empty($mesures)) {
            $lines[] = '';
            $lines[] = 'Mesures :';
            if (isset($mesures['ph'])) {
                $lines[] = '  pH : ' . $mesures['ph'];
            }
            if (isset($mesures['chlore'])) {
                $lines[] = '  Chlore libre : ' . $mesures['chlore'] . ' mg/L';
            }
            if (isset($mesures['alcalinite'])) {
                $lines[] = '  TAC : ' . $mesures['alcalinite'] . ' mg/L';
            }
            if (isset($mesures['stabilisant'])) {
                $lines[] = '  Stabilisant : ' . $mesures['stabilisant'] . ' mg/L';
            }
            if (!empty($mesures['sel']) && isset($mesures['selPpm'])) {
                $lines[] = '  Sel : ' . $mesures['selPpm'] . ' ppm';
            }
            // Note de fiabilité basée sur la confiance
            $fiabilite = match ($this->confidenceIndex) {
                'eleve'     => '(mesures complètes — fiabilité élevée)',
                'moyen'     => '(mesures partielles — fiabilité moyenne, affiner avec stabilisant/TAC)',
                default     => '(diagnostic visuel — mesures non fournies)',
            };
            $lines[] = '  ' . $fiabilite;
        } else {
            $lines[] = 'Mesures : aucune fournie (diagnostic visuel)';
        }

        // Actions tentées + résultats
        if (!empty($this->triedActions)) {
            $lines[] = '';
            $lines[] = 'Déjà tenté (sans succès) : ' . implode(', ', $this->triedActions);
        }

        // Recommandations (plan chimique)
        if (!empty($this->recommandations)) {
            $lines[] = '';
            $lines[] = 'Plan calculé : ' . count($this->recommandations) . ' correction(s) identifiée(s)';
        }

        // Confiance
        $confLabel = match ($this->confidenceIndex) {
            'eleve'     => 'Confiance élevée',
            'moyen'     => 'Confiance moyenne',
            default     => 'Indicatif',
        };
        $lines[] = '';
        $lines[] = 'Indice de confiance : ' . $confLabel;

        // Coordonnées si fournies
        if ($this->coordonnees) {
            $lines[] = 'Localisation : ' . $this->coordonnees;
        } elseif ($this->commune !== '') {
            $lines[] = 'Commune : ' . $this->commune;
        }

        $lines[] = '';
        $lines[] = "Pouvez-vous m'aider ou intervenir ?";

        return $lines;
    }

    /**
     * Résumé riche du diagnostic pour WhatsApp (DIAG-06 enrichi).
     * Remplace whatsappSummary() basique (Plan 03) — utilise richContextPayload().
     * Construit côté serveur ; aucun coefficient de dose dans ce résumé.
     */
    public function whatsappSummary(): string
    {
        return implode("\n", $this->richContextPayload());
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Volume effectif en m³ :
     *   - sizeMode = 'volume' : champ $volume direct
     *   - sizeMode = 'surface' : $surface × $profondeur (calcul serveur pur, pas en JS)
     *
     * DIAG-02 : le calcul surface×profondeur est géométrique (pas une formule chimique)
     * et s'effectue côté serveur. Alpine peut l'afficher via le readout mais ne le recalcule pas.
     */
    public function volumeEffectif(): float
    {
        if ($this->sizeMode === 'surface') {
            $s = (float) str_replace(',', '.', $this->surface);
            $p = (float) str_replace(',', '.', $this->profondeur);

            return $s * $p;
        }

        return (float) str_replace(',', '.', $this->volume);
    }

    /**
     * Assemble le tableau de mesures pour DoseEngine::compute().
     */
    public function mesures(): array
    {
        $base = array_filter([
            'ph'          => $this->ph !== '' ? $this->ph : null,
            'chlore'      => $this->chlore !== '' ? $this->chlore : null,
            'chlore_total' => $this->chloreTotal !== '' ? $this->chloreTotal : null,
            'alcalinite'  => $this->alcalinite !== '' ? $this->alcalinite : null,
            'stabilisant' => $this->stabilisant !== '' ? $this->stabilisant : null,
            'selPpm'      => $this->selPpm !== '' ? $this->selPpm : null,
            'th'          => $this->th !== '' ? $this->th : null,
        ], fn ($v) => $v !== null);
        $base['sel'] = $this->sel; // always present and explicit (IN-01)
        return $base;
    }

    // ── Actions serveur ───────────────────────────────────────────────────────

    /**
     * Accepte le disclaimer légal (DIAG-03).
     * Déclenché par wire:click depuis la tuile disclaimer.
     */
    public function acceptDisclaimer(): void
    {
        $this->disclaimerAccepted = true;
    }

    /**
     * Définit le mode de parcours (Phase 10 — diag-1/diag-2, T-10-01).
     *
     * Appelé par Alpine via $wire.call('setMode', value) au clic sur le lien chimie
     * ou à l'entrée dans l'arbre symptôme. Seules les valeurs de la liste blanche
     * ['symptom', 'chemistry'] sont acceptées ; toute autre valeur est ignorée
     * silencieusement (pas d'assignation, pas d'exception) pour empêcher
     * l'injection d'un created_via arbitraire (T-10-01 : Tampering).
     */
    public function setMode(string $mode): void
    {
        if (in_array($mode, ['symptom', 'chemistry'], strict: true)) {
            $this->mode = $mode;
        }
    }

    private const ALLOWED_TRIED_ACTIONS = [
        'Chlore choc', 'Brossage des parois', 'Anti-algues',
        'Ajusté le pH', 'Backwash filtre', 'Rien encore',
    ];

    /**
     * Synchronise les actions déjà tentées depuis la sélection multiselect Alpine.
     * Allowlist + cap pour empêcher l'injection de valeurs arbitraires (CR-02).
     */
    public function updateTriedActions(array $tried): void
    {
        $this->triedActions = array_values(
            array_filter(
                array_slice($tried, 0, 10),
                fn ($v) => is_string($v) && in_array($v, self::ALLOWED_TRIED_ACTIONS, true)
            )
        );
    }

    /**
     * Calcul pur des doses via DoseEngine — AUCUNE persistance, AUCUN effet de bord DB.
     *
     * C'est l'action déclenchée par « Voir mes doses ». Elle découple le calcul
     * (math serveur, DIAG-02) de la persistance (keepDiagnostic) : un visiteur peut
     * voir ses doses sans qu'une ligne Diagnostic soit créée. Un Diagnostic n'est
     * écrit que sur un geste explicite (garder / PDF / envoyer à Pierre).
     *
     * Guard chain :
     *   1. rateLimit
     *   2. protectAgainstSpam
     *   3. validate (mesures)
     *   4. DIAG-03 disclaimer server-guard (accepter avant de voir les doses)
     *   5. DoseEngine::compute (pur) + classification confiance/escalade
     */
    public function computeDoses(): void
    {
        // 1. Rate limit
        try {
            $this->rateLimit(5, 60);
        } catch (TooManyRequestsException) {
            $this->addError('throttle', "Trop d'essais d'affilée. Patientez une minute puis réessayez.");
            return;
        }

        // 2. Honeypot
        try {
            $this->protectAgainstSpam();
        } catch (\Throwable) {
            return;
        }

        // 3. Validate mesures (champs chimie uniquement — pas les champs lead)
        $this->validate([
            'volume'      => 'nullable|numeric|min:1|max:1000',
            'surface'     => 'nullable|numeric|min:1|max:5000',
            'profondeur'  => 'nullable|numeric|min:0.5|max:5',
            'filtration'  => 'nullable|in:sable,verre,cartouche,diatomees',
            'ph'          => 'nullable|numeric',
            'chlore'      => 'nullable|numeric',
            'alcalinite'  => 'nullable|numeric',
            'stabilisant' => 'nullable|numeric',
            'selPpm'      => 'nullable|numeric',
            'chloreTotal' => 'nullable|numeric',
            'th'          => 'nullable|numeric',
        ]);

        // 4. DIAG-03 : guard serveur — disclaimer must be accepted
        if (! $this->disclaimerAccepted) {
            $this->addError('disclaimer', 'Acceptez d\'abord les conditions pour voir les recommandations de dosage.');
            return;
        }

        // 5. DoseEngine — calcul pur, AUCUNE persistance (DIAG-02 : serveur uniquement)
        try {
            $vol = $this->volumeEffectif();
            $this->recommandations = DoseEngine::compute($this->mesures(), $vol > 0 ? $vol : 0.0);

            // Classification (pas dose — DIAG-02)
            $this->computeConfidence();
            $this->computeEscalade();

            // Un re-calcul invalide une éventuelle persistance précédente (WR-03).
            // On ne reset savedDiagnosticId que si un calcul existait déjà —
            // évite d'invalider keepDiagnostic() qui peut appeler computeDoses() en interne.
            if ($this->hasComputed) {
                $this->savedDiagnosticId = null;
            }
            $this->hasComputed = true;
        } catch (\Throwable $e) {
            Log::error('DiagnosticWizard computeDoses failed', [
                'exception' => $e->getMessage(),
                'client_id' => auth('clients')->id(),
            ]);
            $this->addError('compute', "Le calcul a échoué de notre côté. Réessayez dans un instant.");
        }
    }

    /**
     * Persiste le diagnostic courant — geste EXPLICITE (garder / PDF / envoyer à Pierre).
     *
     * Crée la ligne Diagnostic et seed la session pour le gate PDF (D-06).
     * `disclaimer_accepted_at` n'est jamais null sur une ligne dosée (D-04) car
     * keepDiagnostic exige un calcul préalable, lui-même gardé par le disclaimer.
     * Idempotent : si déjà persisté (savedDiagnosticId non null), no-op.
     */
    public function keepDiagnostic(): void
    {
        // Idempotence : déjà enregistré pour cet état de calcul
        if ($this->savedDiagnosticId !== null) {
            return;
        }

        // Il faut un calcul (qui a lui-même franchi le guard disclaimer DIAG-03/D-04)
        if (! $this->hasComputed) {
            $this->computeDoses();
            if (! $this->hasComputed) {
                return; // compute a échoué ou été bloqué — erreurs déjà posées
            }
        }

        try {
            $vol = $this->volumeEffectif();

            $diagnostic = Diagnostic::create([
                'client_id'              => auth('clients')->id(), // null si anonyme (Req5)
                'piscine_id'             => $this->piscineId,
                'volume_m3'              => $vol > 0 ? $vol : null,
                'type_probleme'          => $this->mode,
                'mesures'                => $this->mesures(),
                'recommandations'        => $this->recommandations,
                'disclaimer_accepted_at' => now(), // jamais null sur une ligne dosée (D-04)
                'created_via'            => $this->mode === 'symptom' ? 'depannage' : 'wizard',
            ]);

            // Conserver l'ID pour le lien PDF (D-06)
            $this->savedDiagnosticId = $diagnostic->id;

            // Seed de la session pour le gate PDF anonyme (D-06, Plan 05-05)
            session()->put(
                'diagnostic_ids',
                array_merge(session('diagnostic_ids', []), [$diagnostic->id])
            );
        } catch (\Throwable $e) {
            Log::error('DiagnosticWizard keepDiagnostic failed', [
                'exception' => $e->getMessage(),
                'client_id' => auth('clients')->id(),
            ]);
            $this->addError('compute', "L'enregistrement a échoué de notre côté. Réessayez dans un instant.");
        }
    }

    /**
     * Persiste puis redirige vers le PDF (D-06).
     * Le téléchargement PDF est l'un des gestes explicites qui matérialisent un Diagnostic.
     */
    public function downloadPdf(): mixed
    {
        $this->keepDiagnostic();

        if ($this->savedDiagnosticId === null) {
            return null; // erreurs déjà posées
        }

        return redirect()->route('diagnostic.pdf', $this->savedDiagnosticId);
    }

    /**
     * Back-compat : calcul + persistance en un seul appel (ancien comportement couplé).
     * Conservé pour les tests d'invariants existants ; l'UI utilise désormais
     * computeDoses() (affichage) puis keepDiagnostic()/downloadPdf() (persistance explicite).
     */
    public function computeAndPersist(): void
    {
        $this->computeDoses();

        if ($this->hasComputed && $this->disclaimerAccepted) {
            $this->keepDiagnostic();
        }
    }

    /**
     * Enregistre les coordonnées du visiteur (lead capture — S7, Req6, D-03).
     *
     * Guard chain identique à ContactForm 1:1 :
     *   1. rateLimit
     *   2. protectAgainstSpam
     *   3. validate (champs lead)
     *   4. persist sur le Diagnostic existant + mail Pierre
     *   5. success
     */
    public function submitLead(): void
    {
        // 1. Rate limit
        try {
            $this->rateLimit(5, 60);
        } catch (TooManyRequestsException) {
            $this->addError('throttle', "Trop d'envois d'affilée. Patientez une minute puis réessayez.");
            return;
        }

        // 2. Honeypot
        try {
            $this->protectAgainstSpam();
        } catch (\Throwable) {
            return;
        }

        // 3. Validate lead fields uniquement
        $this->validate([
            'prenom'  => 'required|string|max:80',
            'commune' => 'required|string|max:80',
            'email'   => 'nullable|email|max:160',
            'siteWeb' => 'nullable|url|max:255',
        ]);

        // 4. Persist + notify Pierre
        try {
            // Envoyer ses coordonnées EST un geste explicite « contacter Pierre » :
            // matérialise le Diagnostic s'il ne l'est pas encore (decouple compute/persist).
            if ($this->savedDiagnosticId === null && $this->hasComputed) {
                $this->keepDiagnostic();
            }

            // Mise à jour de la ligne Diagnostic existante (additive — D-03)
            if ($this->savedDiagnosticId) {
                $diagnostic = Diagnostic::find($this->savedDiagnosticId);
                if ($diagnostic) {
                    $diagnostic->update([
                        'prenom'   => $this->prenom,
                        'commune'  => $this->commune,
                        'email'    => $this->email !== '' ? $this->email : null,
                        'site_web' => $this->siteWeb !== '' ? $this->siteWeb : null,
                    ]);
                }
            }

            // Notifier Pierre (Pattern 7, ContactForm pattern)
            Mail::to(config('contact.recipient', 'contact@dloazurpiscines.com'))
                ->send(new DiagnosticLead(
                    prenom:    $this->prenom,
                    commune:   $this->commune,
                    email:     $this->email !== '' ? $this->email : null,
                    siteWeb:   $this->siteWeb !== '' ? $this->siteWeb : null,
                    summary:   $this->whatsappSummary(),
                    mesures:   $this->mesures(),
                    triedActions: $this->triedActions,
                    diagId:    $this->savedDiagnosticId,
                ));
        } catch (\Throwable $e) {
            Log::error('DiagnosticWizard submitLead failed', [
                'exception' => $e->getMessage(),
                'prenom'    => $this->prenom,
            ]);
            $this->addError('send', "L'envoi a échoué de notre côté. Réessayez, ou contactez Pierre directement sur WhatsApp.");
            return;
        }

        // 5. Succès
        $this->leadSent = true;
        $this->reset(['prenom', 'commune', 'email', 'siteWeb']);
    }

    /**
     * Render — fournit le config de l'arbre à la vue pour navigation Alpine.
     *
     * DIAG-02 invariant : seul le texte statique (label, plan, safety_block) est fourni.
     * Aucune formule arithmétique, aucun coefficient n'est injecté dans le JS.
     */
    public function render(): View
    {
        return view('livewire.diagnostic-wizard', [
            'tree'             => config('diagnostic-tree', []),
            'filtrationHint'   => $this->filtrationHint,
            'whatsappSummary'  => $this->whatsappSummary(),
            'escaladeNiveau'   => $this->escaladeNiveau,
            'escaladeRaison'   => $this->escaladeRaison,
            'confidenceIndex'  => $this->confidenceIndex,
        ]);
    }
}
