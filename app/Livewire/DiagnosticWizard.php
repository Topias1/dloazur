<?php

namespace App\Livewire;

use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

/**
 * Diagnostic piscine — composant Livewire pleine page (DIAG-01, D-01, Plan 05-01)
 *
 * Architecture (D-01) :
 *   - Livewire : état serveur (disclaimerAccepted, tried-actions, piscineId, mode)
 *   - Alpine   : navigation entre les étapes (step, nodeId, mode) — JAMAIS lié à Livewire
 *
 * DIAG-02 : aucune formule arithmétique dans ce composant ni dans la vue Blade.
 *           Seul DoseEngine (Plan 05-03) calcule les doses côté serveur.
 *
 * DIAG-03 : disclaimerAccepted doit être true avant toute sortie de dosage.
 *           Le guard server-side (acceptDisclaimer / computeAndPersist) est défini ici
 *           pour que Plan 05-03 puisse le valider.
 */
final class DiagnosticWizard extends Component
{
    use WithRateLimiting, UsesSpamProtection;

    // ── État persistant côté serveur (synchronisé avec Livewire) ────────────

    /**
     * Disclaimer explicitement accepté (DIAG-03, D-04).
     * Guard serveur : computeAndPersist() vérifie ce flag avant toute opération
     * de calcul/persistance. L'UI Alpine masque la suite si false.
     */
    public bool $disclaimerAccepted = false;

    /**
     * Actions déjà tentées par l'utilisateur (BLUEPRINT §6, DIAG-01 action-aware).
     * Multiselect côté client → synchronisé via wire:model sur le nœud "tried".
     * Chaque valeur = une action déjà essayée (ex: 'chlore-choc', 'brossage', 'anti-algues').
     */
    public array $triedActions = [];

    /**
     * Mode de diagnostic : 'symptom' | 'chemistry' | null (pas encore choisi).
     * Utilisé côté serveur pour marquer le Diagnostic.created_via correctement.
     */
    public ?string $mode = null;

    /**
     * Identifiant de piscine pré-rempli si client connecté.
     * Nullable — le diagnostic est disponible en mode anonyme (SPEC Req 5).
     */
    public ?int $piscineId = null;

    /**
     * Hint type de filtre pré-rempli depuis la fiche piscine (D-08 gap).
     * Valeur libre-texte — considérée comme non-autoritaire ; l'utilisateur peut l'override.
     */
    public ?string $filtrationHint = null;

    /**
     * ID du Diagnostic persisted (Plan 05-03) — utilisé pour le lien PDF (D-06).
     */
    public ?int $savedDiagnosticId = null;

    // Honeypot (spatie/laravel-honeypot)
    public HoneypotData $extraFields;

    // ──────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();

        // Pré-remplir depuis la session client connecté (Plan 05-03 étendra ceci)
        if (auth('clients')->check()) {
            $client = auth('clients')->user();
            // Pré-remplir la piscine principale du client si disponible
            $piscine = $client->piscines()->latest()->first();
            if ($piscine) {
                $this->piscineId     = $piscine->id;
                $this->filtrationHint = $piscine->filtration ?? null;
            }
        }
    }

    // ── Actions serveur ───────────────────────────────────────────────────────

    /**
     * Accepte le disclaimer légal (DIAG-03).
     *
     * Déclenché par wire:click depuis la tuile disclaimer (Alpine avance le step
     * côté client en parallèle — @click.prevent + wire:click).
     * Le flag est positionné serveur pour que Plan 05-03 puisse vérifier avant de persister.
     */
    public function acceptDisclaimer(): void
    {
        $this->disclaimerAccepted = true;
    }

    /**
     * Guard serveur : rejecter tout calcul/persistance si disclaimer non accepté (DIAG-03).
     *
     * Plan 05-03 implémente la logique complète (DoseEngine + Diagnostic::create).
     * Ce stub garantit que le guard shape existe dès Plan 05-01 pour les tests.
     */
    public function computeAndPersist(): void
    {
        // Guard DIAG-03 (D-04) : disclaimerAccepted doit être vrai
        if (! $this->disclaimerAccepted) {
            $this->addError('disclaimer', 'Accepte d\'abord les conditions pour voir les recommandations de dosage.');

            return;
        }

        // Plan 05-03 implémentera le calcul DoseEngine + persistance Diagnostic.
        // Pour l'instant le wizard ne traite que le parcours symptôme (arbre de décision statique).
    }

    /**
     * Synchronise les actions déjà tentées depuis la sélection multiselect Alpine.
     * Plan 05-03 utilisera cette liste pour l'action-aware diagnosis (BLUEPRINT §6).
     */
    public function updateTriedActions(array $tried): void
    {
        $this->triedActions = $tried;
    }

    /**
     * Render — fournit le config de l'arbre à la vue pour navigation Alpine.
     *
     * DIAG-02 invariant : seul le texte statique (label, plan, safety_block) est fourni.
     * Aucune formule arithmétique, aucun coefficient n'est injecté dans le JS.
     * Le config est rendu côté serveur (Blade @js) ; Alpine le traverse en lecture.
     */
    public function render(): View
    {
        return view('livewire.diagnostic-wizard', [
            'tree'         => config('diagnostic-tree', []),
            'filtrationHint' => $this->filtrationHint,
        ]);
    }
}
