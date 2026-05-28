<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Passage;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class PassageIndex extends Component
{
    use WithPagination;

    #[Validate('nullable|string')]
    public string $clientId = '';

    #[Validate('nullable|date')]
    public string $dateFrom = '';

    #[Validate('nullable|date')]
    public string $dateTo = '';

    public function updatedClientId(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $passages = Passage::query()
            ->with(['client:id,name', 'piscine:id,nom,volume_m3'])
            ->withCount('photos')
            ->when($this->clientId !== '', fn ($q) => $q->where('client_id', (int) $this->clientId))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('visited_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('visited_at', '<=', $this->dateTo))
            ->orderBy('visited_at', 'desc')
            ->paginate(25);

        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('livewire.passage-index', compact('passages', 'clients'));
    }
}
