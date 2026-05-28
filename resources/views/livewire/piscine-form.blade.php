<form wire:submit.prevent="submit" class="space-y-5">

    {{-- Nom --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Nom de la piscine</label>
        <input
            wire:model="nom"
            type="text"
            maxlength="60"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
            placeholder="Lagon, Piscine principale…">
        @error('nom')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Volume --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Volume (m³)</label>
        <input
            wire:model="volume_m3"
            type="number"
            inputmode="decimal"
            step="0.5"
            min="1"
            max="1000"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
            placeholder="25">
        @error('volume_m3')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Type --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Type</label>
        <select
            wire:model="type"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none">
            <option value="">— Sélectionner —</option>
            <option value="enterrée">Enterrée</option>
            <option value="hors-sol">Hors-sol</option>
            <option value="semi-enterrée">Semi-enterrée</option>
            <option value="spa">Spa</option>
        </select>
        @error('type')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Filtration --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Filtration</label>
        <select
            wire:model="filtration"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none">
            <option value="">— Sélectionner —</option>
            <option value="sable">Sable</option>
            <option value="cartouche">Cartouche</option>
            <option value="diatomée">Diatomée</option>
        </select>
        @error('filtration')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Traitement --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Traitement</label>
        <select
            wire:model="traitement"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none">
            <option value="">— Sélectionner —</option>
            <option value="chlore">Chlore</option>
            <option value="sel">Électrolyse sel</option>
            <option value="UV">UV</option>
            <option value="oxygène-actif">Oxygène actif</option>
        </select>
        @error('traitement')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Équipements --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-2">Équipements</label>
        <div class="flex flex-wrap gap-2">
            @foreach (['Volet', 'Robot', 'PAC', 'Bâche', 'Échelle'] as $opt)
                <label class="inline-flex items-center gap-2 h-11 px-3.5 rounded-xl bg-white ring-1 ring-sand-200 cursor-pointer has-[:checked]:bg-azure-50 has-[:checked]:ring-azure-200">
                    <input type="checkbox" wire:model="equipements" value="{{ $opt }}" class="sr-only">
                    <span>{{ $opt }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Notes</label>
        <textarea
            wire:model="notes"
            rows="3"
            maxlength="1000"
            class="w-full rounded-xl bg-sand-50 ring-1 ring-sand-200 p-3 focus:ring-2 focus:ring-azure-500 outline-none resize-none"
            placeholder="Particularités, historique…"></textarea>
        @error('notes')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Save error --}}
    @error('save')
        <p class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3 text-sm text-danger">{{ $message }}</p>
    @enderror

    {{-- Submit --}}
    <div class="pt-2">
        <button
            type="submit"
            class="h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 transition-colors"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-60">
            <span wire:loading.remove>Enregistrer la piscine</span>
            <span wire:loading>Enregistrement…</span>
        </button>
    </div>

</form>
