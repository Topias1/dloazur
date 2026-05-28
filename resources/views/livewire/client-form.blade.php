<form wire:submit.prevent="submit" class="space-y-5">

    {{-- Nom --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">
            Nom <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input
            wire:model="name"
            type="text"
            maxlength="80"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
            autocomplete="name"
            placeholder="Prénom Nom">
        @error('name')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- E-mail --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">E-mail</label>
        <input
            wire:model="email"
            type="email"
            inputmode="email"
            maxlength="160"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
            autocomplete="email"
            placeholder="client@example.com">
        @error('email')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Téléphone --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Téléphone</label>
        <input
            wire:model="phone"
            type="tel"
            inputmode="tel"
            maxlength="30"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
            autocomplete="tel"
            placeholder="0696 000 000">
        @error('phone')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Adresse --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Adresse</label>
        <input
            wire:model="address"
            type="text"
            maxlength="200"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
            autocomplete="street-address"
            placeholder="12 rue de la Plage, Schoelcher">
        @error('address')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Notes</label>
        <textarea
            wire:model="notes"
            rows="4"
            maxlength="2000"
            class="w-full rounded-xl bg-sand-50 ring-1 ring-sand-200 p-3 focus:ring-2 focus:ring-azure-500 outline-none resize-none"
            placeholder="Informations complémentaires…"></textarea>
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
            <span wire:loading.remove>Enregistrer</span>
            <span wire:loading>Enregistrement…</span>
        </button>
    </div>

</form>
