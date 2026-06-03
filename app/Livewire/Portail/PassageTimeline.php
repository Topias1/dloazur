<?php

namespace App\Livewire\Portail;

use App\Models\Passage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class PassageTimeline extends Component
{
    /**
     * Rendu de la timeline des passages du client connecté.
     *
     * SÉCURITÉ CRITIQUE (RESEARCH §1110) : le filtre where('client_id', ...) est OBLIGATOIRE.
     * Ne jamais retirer ce filtre — Test P3 valide que Client A ne voit pas les passages de Client B.
     */
    public function render(): View
    {
        $client = Auth::guard('clients')->user();

        // Filtre strict client_id — isolation inter-clients (threat T-2-07G)
        $passages = Passage::query()
            ->where('client_id', $client->id)
            ->with(['photos', 'piscine'])
            ->orderBy('visited_at', 'desc')
            ->get();

        $lastPassage = $passages->first();
        $piscine = $lastPassage?->piscine ?? $client->piscines()->first();

        // Case « Sel » : pertinente seulement pour un traitement au sel / électrolyse,
        // OU si un passage a réellement enregistré une mesure de sel. Une piscine au
        // chlore ne doit pas afficher de case Sel (feedback Pierre).
        $traitement = mb_strtolower((string) ($piscine->traitement ?? ''));
        $showSel = in_array($traitement, ['sel', 'électrolyse', 'electrolyse', 'sel/électrolyse'], true)
            || $passages->contains(fn ($p) => $p->sel_g_l !== null);

        // Photo d'en-tête de la carte piscine : la plus récente photo de passage si
        // disponible, sinon une photo générique (le rendu reste « sexy » sans S3).
        $heroPhotoUrl = null;
        $heroPhoto = $passages->firstWhere(fn ($p) => $p->photos->isNotEmpty())?->photos->first();
        if ($heroPhoto) {
            try {
                $heroPhotoUrl = \Illuminate\Support\Facades\Storage::disk($heroPhoto->disk ?? 'r2')
                    ->temporaryUrl($heroPhoto->path, now()->addHour());
            } catch (\Throwable) {
                $heroPhotoUrl = null;
            }
        }

        return view('livewire.portail.passage-timeline', [
            'client'       => $client,
            'piscine'      => $piscine,
            'lastPassage'  => $lastPassage,
            'passages'     => $passages,
            'showSel'      => $showSel,
            'heroPhotoUrl' => $heroPhotoUrl,
        ]);
    }
}
