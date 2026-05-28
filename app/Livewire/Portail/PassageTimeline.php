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

        return view('livewire.portail.passage-timeline', [
            'client'      => $client,
            'piscine'     => $piscine,
            'lastPassage' => $lastPassage,
            'passages'    => $passages,
        ]);
    }
}
