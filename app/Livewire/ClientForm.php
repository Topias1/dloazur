<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ClientForm extends Component
{
    #[Validate('required|string|max:80')]
    public string $name = '';

    #[Validate('nullable|email|max:160')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('nullable|string|max:200')]
    public string $address = '';

    #[Validate('nullable|string|max:2000')]
    public string $notes = '';

    public ?int $clientId = null;

    public function mount(?int $clientId = null): void
    {
        if ($clientId) {
            $client = Client::findOrFail($clientId);
            $this->clientId  = $client->id;
            $this->name      = $client->name;
            $this->email     = (string) $client->email;
            $this->phone     = (string) $client->phone;
            $this->address   = (string) $client->address;
            $this->notes     = (string) $client->notes;
        }
    }

    public function submit(): void
    {
        $this->validate();

        try {
            if ($this->clientId) {
                // Update existing client
                Client::findOrFail($this->clientId)->update([
                    'name'    => $this->name,
                    'email'   => $this->email ?: null,
                    'phone'   => $this->phone ?: null,
                    'address' => $this->address ?: null,
                    'notes'   => $this->notes ?: null,
                ]);
            } else {
                // Create new client
                Client::create([
                    'uuid'    => (string) Str::uuid(),
                    'name'    => $this->name,
                    'email'   => $this->email ?: null,
                    'phone'   => $this->phone ?: null,
                    'address' => $this->address ?: null,
                    'notes'   => $this->notes ?: null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Client save failed', ['exception' => $e->getMessage()]);
            $this->addError('save', "L'enregistrement a échoué.");
            return;
        }

        $this->dispatch('client-saved');
        session()->flash('status', 'client-saved');
        $this->redirect(route('admin.clients.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.client-form');
    }
}
