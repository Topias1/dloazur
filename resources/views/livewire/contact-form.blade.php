<div>
    {{-- Honeypot: visually hidden, aria-hidden for a11y (UI-SPEC + T-4-01) --}}
    <div aria-hidden="true" tabindex="-1" style="display:none">
        <x-honeypot livewire-model="extraFields" />
    </div>

    @if ($sent)
        {{-- Success state (UI-SPEC §Copywriting Contract) --}}
        <div class="bg-sand-50 rounded-2xl ring-1 ring-sand-200 p-8 text-center">
            <div class="text-3xl mb-3">✓</div>
            <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">Message envoyé.</h3>
            <p class="text-ink-600 mb-4">Pierre vous répondra rapidement. En attendant, écrivez-lui directement sur WhatsApp.</p>
            <a
                href="https://wa.me/596696940054"
                rel="noopener noreferrer"
                target="_blank"
                class="inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-[#25D366] text-white font-semibold hover:bg-[#20c05a] transition-colors"
            >
                Ou directement sur WhatsApp
            </a>
        </div>
    @else
        <form wire:submit="submit" class="space-y-4" novalidate>

            {{-- Global throttle error --}}
            @error('throttle')
                <div class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                    <p class="text-sm text-danger">{{ $message }}</p>
                </div>
            @enderror

            {{-- Global send error --}}
            @error('send')
                <div class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                    <p class="text-sm text-danger">{{ $message }}</p>
                </div>
            @enderror

            {{-- Prénom + Nom séparés (feedback Pierre : éviter les quiproquos) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact-firstname" class="block text-sm font-semibold text-ink-700 mb-1.5">
                        Prénom <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="contact-firstname"
                        type="text"
                        wire:model.lazy="firstname"
                        autocomplete="given-name"
                        required
                        class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('firstname') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                    >
                    @error('firstname')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="contact-lastname" class="block text-sm font-semibold text-ink-700 mb-1.5">
                        Nom <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="contact-lastname"
                        type="text"
                        wire:model.lazy="lastname"
                        autocomplete="family-name"
                        required
                        class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('lastname') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                    >
                    @error('lastname')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label for="contact-email" class="block text-sm font-semibold text-ink-700 mb-1.5">
                    E-mail <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <input
                    id="contact-email"
                    type="email"
                    wire:model.lazy="email"
                    autocomplete="email"
                    required
                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('email') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                >
                @error('email')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Téléphone (obligatoire — feedback Pierre : pouvoir rappeler pour filtrer) --}}
            <div>
                <label for="contact-phone" class="block text-sm font-semibold text-ink-700 mb-1.5">
                    Téléphone <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <input
                    id="contact-phone"
                    type="tel"
                    wire:model.lazy="phone"
                    autocomplete="tel"
                    required
                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('phone') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                >
                @error('phone')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message --}}
            <div>
                <label for="contact-message" class="block text-sm font-semibold text-ink-700 mb-1.5">
                    Message <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <textarea
                    id="contact-message"
                    wire:model.lazy="message"
                    rows="4"
                    required
                    class="w-full px-4 py-3 rounded-xl bg-sand-50 ring-1 @error('message') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition resize-y min-h-[96px]"
                ></textarea>
                @error('message')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed"
                class="w-full h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 active:bg-azure-700 transition-colors flex items-center justify-center gap-2"
            >
                <span wire:loading.remove>Envoyer mon message</span>
                <span wire:loading>Envoi en cours…</span>
            </button>

        </form>

        {{-- WhatsApp fallback (D-16, UI-SPEC §Contact form) --}}
        <p class="mt-6 text-sm text-ink-500 text-center">
            Vous préférez le chat instantané ?
            <a
                href="https://wa.me/596696940054"
                rel="noopener noreferrer"
                target="_blank"
                class="inline-flex items-center gap-1 font-semibold text-white bg-[#25D366] px-2.5 py-1 rounded-lg hover:bg-[#20c05a] transition-colors"
            >
                Ou directement sur WhatsApp
            </a>
        </p>
    @endif
</div>
