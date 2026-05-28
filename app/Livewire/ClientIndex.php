<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $driver   = DB::connection()->getDriverName();
        $likeOp   = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        $clients = Client::query()
            ->when($this->search, fn ($q) =>
                $q->where(
                    DB::raw("name || ' ' || COALESCE(email,'') || ' ' || COALESCE(phone,'') || ' ' || COALESCE(address,'')"),
                    $likeOp,
                    '%' . $this->search . '%'
                )
            )
            ->withCount('passages')
            ->with(['piscines:id,client_id,nom,volume_m3,type'])
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        return view('livewire.client-index', compact('clients'));
    }
}
