<?php

namespace App\Livewire;

use App\Models\Piscine;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PiscineForm extends Component
{
    #[Validate('required|integer|exists:clients,id')]
    public int $clientId;

    #[Validate('nullable|string|max:60')]
    public string $nom = '';

    #[Validate('nullable|numeric|min:1|max:1000')]
    public string $volume_m3 = '';

    #[Validate('nullable|string|max:30')]
    public string $type = '';

    #[Validate('nullable|string|max:30')]
    public string $filtration = '';

    #[Validate('nullable|string|max:30')]
    public string $traitement = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    public array $equipements = [];

    public ?int $piscineId = null;

    public function mount(int $clientId, ?int $piscineId = null): void
    {
        $this->clientId  = $clientId;
        $this->piscineId = $piscineId;

        if ($piscineId) {
            $piscine = Piscine::where('client_id', $clientId)->findOrFail($piscineId);
            $this->nom        = (string) $piscine->nom;
            $this->volume_m3  = (string) $piscine->volume_m3;
            $this->type       = (string) $piscine->type;
            $this->filtration = (string) $piscine->filtration;
            $this->traitement = (string) $piscine->traitement;
            $this->notes      = (string) $piscine->notes;
            $this->equipements = (array) ($piscine->equipements ?? []);
        }
    }

    public function submit(): void
    {
        $this->validate();

        try {
            if ($this->piscineId) {
                Piscine::where('client_id', $this->clientId)
                    ->findOrFail($this->piscineId)
                    ->update([
                        'nom'         => $this->nom ?: null,
                        'volume_m3'   => $this->volume_m3 ?: null,
                        'type'        => $this->type ?: null,
                        'filtration'  => $this->filtration ?: null,
                        'traitement'  => $this->traitement ?: null,
                        'equipements' => $this->equipements ?: null,
                        'notes'       => $this->notes ?: null,
                    ]);
            } else {
                Piscine::create([
                    'client_id'   => $this->clientId,
                    'nom'         => $this->nom ?: null,
                    'volume_m3'   => $this->volume_m3 ?: null,
                    'type'        => $this->type ?: null,
                    'filtration'  => $this->filtration ?: null,
                    'traitement'  => $this->traitement ?: null,
                    'equipements' => $this->equipements ?: null,
                    'notes'       => $this->notes ?: null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Piscine save failed', ['exception' => $e->getMessage()]);
            $this->addError('save', "L'enregistrement a échoué.");
            return;
        }

        $this->dispatch('piscine-saved');
        $this->redirect(route('admin.clients.show', $this->clientId), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.piscine-form');
    }
}
