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
     * State succès du formulaire lead (S7).
     */
    public bool $leadSent = false;

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
        return array_filter([
            'ph'          => $this->ph !== '' ? $this->ph : null,
            'chlore'      => $this->chlore !== '' ? $this->chlore : null,
            'chlore_total' => $this->chloreTotal !== '' ? $this->chloreTotal : null,
            'alcalinite'  => $this->alcalinite !== '' ? $this->alcalinite : null,
            'stabilisant' => $this->stabilisant !== '' ? $this->stabilisant : null,
            'sel'         => $this->sel,
            'selPpm'      => $this->selPpm !== '' ? $this->selPpm : null,
            'th'          => $this->th !== '' ? $this->th : null,
        ], fn ($v) => $v !== null);
    }

    /**
     * Résumé texte du diagnostic pour le pré-remplissage WhatsApp (DIAG-06 basique).
     * Construit côté serveur puis exposé à Alpine via @js — aucun coefficient dedans.
     */
    public function whatsappSummary(): string
    {
        $lines = [];
        $lines[] = 'Bonjour Pierre, j\'ai utilisé l\'outil diagnostic de Dlo Azur Piscines.';
        $lines[] = '';

        if ($this->mode === 'chemistry') {
            $lines[] = '📊 Mode : Analyse chimique';
        } elseif ($this->mode === 'symptom') {
            $lines[] = '🔍 Mode : Diagnostic symptôme';
        }

        $vol = $this->volumeEffectif();
        if ($vol > 0) {
            $lines[] = '🏊 Volume : ' . round($vol, 1) . ' m³';
        }
        if ($this->filtration) {
            $lines[] = '🔧 Filtre : ' . $this->filtration;
        }

        $mesures = $this->mesures();
        if (! empty($mesures)) {
            $lines[] = '';
            $lines[] = '📋 Mesures :';
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
            if (! empty($mesures['sel']) && isset($mesures['selPpm'])) {
                $lines[] = '  Sel : ' . $mesures['selPpm'] . ' ppm';
            }
        }

        if (! empty($this->triedActions)) {
            $lines[] = '';
            $lines[] = '⚠️ Déjà tenté (sans succès) : ' . implode(', ', $this->triedActions);
        }

        if (! empty($this->recommandations)) {
            $lines[] = '';
            $lines[] = '💡 Recommandations (' . count($this->recommandations) . ' correction(s) identifiée(s))';
        }

        $lines[] = '';
        $lines[] = 'Peux-tu m\'aider ou intervenir ?';

        return implode("\n", $lines);
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
     * Synchronise les actions déjà tentées depuis la sélection multiselect Alpine.
     */
    public function updateTriedActions(array $tried): void
    {
        $this->triedActions = $tried;
    }

    /**
     * Compute + persist : calcule les doses via DoseEngine et crée un Diagnostic.
     *
     * Guard chain (ContactForm pattern) :
     *   1. rateLimit
     *   2. protectAgainstSpam
     *   3. validate (mesures)
     *   4. DIAG-03 disclaimer server-guard
     *   5. DoseEngine::compute + Diagnostic::create
     *   6. success (savedDiagnosticId + session seed)
     */
    public function computeAndPersist(): void
    {
        // 1. Rate limit
        try {
            $this->rateLimit(5, 60);
        } catch (TooManyRequestsException) {
            $this->addError('throttle', "Trop d'envois d'affilée. Patiente une minute puis réessaie.");
            return;
        }

        // 2. Honeypot
        try {
            $this->protectAgainstSpam();
        } catch (\Throwable) {
            return;
        }

        // 3. Validate mesures
        $this->validateOnly([
            'volume', 'surface', 'profondeur', 'filtration',
            'ph', 'chlore', 'alcalinite', 'stabilisant', 'selPpm', 'chloreTotal', 'th',
        ]);

        // 4. DIAG-03 : guard serveur — disclaimer must be accepted
        if (! $this->disclaimerAccepted) {
            $this->addError('disclaimer', 'Accepte d\'abord les conditions pour voir les recommandations de dosage.');
            return;
        }

        // 5. DoseEngine + Diagnostic::create
        try {
            $vol  = $this->volumeEffectif();
            $recs = DoseEngine::compute($this->mesures(), $vol > 0 ? $vol : 0.0);

            $diagnostic = Diagnostic::create([
                'client_id'              => auth('clients')->id(), // null si anonyme (Req5)
                'piscine_id'             => $this->piscineId,
                'volume_m3'              => $vol > 0 ? $vol : null,
                'type_probleme'          => $this->mode,
                'mesures'                => $this->mesures(),
                'recommandations'        => $recs,
                'disclaimer_accepted_at' => now(), // jamais null sur une ligne dosée (D-04)
                'created_via'            => $this->mode === 'symptom' ? 'depannage' : 'wizard',
            ]);

            // 6. Succès — conserver l'ID pour le lien PDF (D-06)
            $this->savedDiagnosticId = $diagnostic->id;
            $this->recommandations   = $recs;

            // Seed de la session pour le gate PDF anonyme (D-06, Plan 05-05)
            session()->put(
                'diagnostic_ids',
                array_merge(session('diagnostic_ids', []), [$diagnostic->id])
            );
        } catch (\Throwable $e) {
            Log::error('DiagnosticWizard computeAndPersist failed', [
                'exception' => $e->getMessage(),
                'client_id' => auth('clients')->id(),
            ]);
            $this->addError('compute', "Le calcul a échoué de notre côté. Réessaie dans un instant.");
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
            $this->addError('throttle', "Trop d'envois d'affilée. Patiente une minute puis réessaie.");
            return;
        }

        // 2. Honeypot
        try {
            $this->protectAgainstSpam();
        } catch (\Throwable) {
            return;
        }

        // 3. Validate lead fields
        $this->validateOnly(['prenom', 'commune', 'email', 'siteWeb']);

        // 4. Persist + notify Pierre
        try {
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
            $this->addError('send', "L'envoi a échoué de notre côté. Réessaie, ou contacte Pierre directement sur WhatsApp.");
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
        ]);
    }
}
